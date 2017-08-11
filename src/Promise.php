<?php

namespace Gandung\Promise;

use TaskQueue\TaskQueue;

class Promise implements PromiseInterface
{
    /**
     * @var integer
     */
    private $state = self::STATE_PENDING;

    /**
     * @var mixed
     */
    private $current;

    /**
     * @var array
     */
    private $context = [];

    /**
     * @var \Closure
     */
    private $waitCallback;

    /**
     * @var \Closure
     */
    private $cancelCallback;

    /**
     * @var boolean
     */
    private $isStateTransformed;

    public function then($onFulfilled = null, $onRejected = null)
    {
        if ($this->state === self::STATE_PENDING) {
            $q = new Promise();
            $this->context[] = [$q, $onFulfilled, $onRejected];

            return $q;
        }

        if ($this->state === self::STATE_FULFILLED) {
            return $onFulfilled
                ? (new FulfilledPromise($this->current))->then($onFulfilled, null)
                : new FulfilledPromise($this->current);
        }

        return $onRejected
            ? (new RejectedPromise($this->current))->then(null, $onRejected)
            : new RejectedPromise($this->current);
    }

    public function setWaitCallback(\Closure $callback = null)
    {
        if (!($callback instanceof \Closure)) {
            throw new \InvalidArgumentException(
                sprintf("Parameter 1 of %s must be a valid callback.", __METHOD__)
            );
        }

        $this->waitCallback = $callback;
    }

    public function setCancelCallback(\Closure $callback = null)
    {
        if (!($callback instanceof \Closure)) {
            throw new \InvalidArgumentException(
                sprintf("Parameter 1 of %s must be a valid callback.", __METHOD__)
            );
        }

        $this->cancelCallback = $callback;
    }

    private function validateState($value, $state)
    {
        if ($this->state !== self::STATE_PENDING) {
            if ($value === $this->current && $state === $this->state) {
                return false;
            }

            $prevStatus = $this->state;
            $currentStatus = $state;

            throw $this->state === $state
                ? new \LogicException(
                    sprintf("State of the promise is already %s", $prevStatus == 4 ? 'fulfilled' : 'rejected')
                )
                : new \LogicException(
                    sprintf(
                        "Unable to change %s promise to %s",
                        $prevStatus == 4 ? 'fulfilled' : 'rejected',
                        $currentStatus == 4 ? 'fulfilled' : 'rejected'
                    )
                );

            return false;
        }

        if ($value === $this) {
            throw new \LogicException(
                "Unable to fulfill or reject a promise with itself."
            );

            return false;
        }

        return true;
    }

    public function resolve($value)
    {
        $this->isStateTransformed = $this->validateState($value, self::STATE_FULFILLED);

        if ($this->isStateTransformed) {
            $this->setState(self::STATE_FULFILLED);
            $this->trigger($value);
        }
    }

    public function reject($reason)
    {
        $this->isStateTransformed = $this->validateState($reason, self::STATE_REJECTED);

        if ($this->isStateTransformed) {
            $this->setState(self::STATE_REJECTED);
            $this->trigger($reason);
        }
    }

    public function wait($callback = null)
    {
        if ($this->waitCallback === null) {
            if (!($callback instanceof \Closure)) {
                throw new \LogicException(
                    sprintf(
                        "Default waiting callback resolver is not set. " .
                        "Synchronous wait mechanism is impossible to invoked because %s is called " .
                        "without callback resolver.", __METHOD__
                    )
                );
            }

            $this->waitCallback = $callback;
        }

        if ($this->currentState() !== self::STATE_PENDING) {
            return;
        }

        try {
            $waitCallback = $this->waitCallback;
            $this->waitCallback = null;
            $waitCallback();
        } catch (\Exception $e) {
            if ($this->currentState() === self::STATE_PENDING) {
                $this->reject($e);
            } else {
                throw $e;
            }
        }

        Context\ContextStack::getQueueHandler()->run();

        if ($this->currentState() === self::STATE_PENDING) {
            $this->reject(
                'Invoking the synchronous wait callback resolver didn\'t resolve the current promise'
            );
        }

        $q = $this->current instanceof PromiseInterface
            ? $this->current->wait()
            : $this->current;

        if ($this->current instanceof PromiseInterface || $this->currentState() === self::STATE_FULFILLED) {
            return $q;
        }
    }

    public function cancel($callback = null)
    {
        if ($this->cancelCallback === null) {
            if (!($callback instanceof \Closure)) {
                throw new \LogicException(
                    sprintf(
                        "Default cancellation callback resolver is not set. " .
                        "Cancellation mechanism is impossible to invoked because %s is called " .
                        "without callback resolver.", __METHOD__
                    )
                );
            }

            $this->cancelCallback = $callback;
        }

        if ($this->currentState() !== self::STATE_PENDING) {
            return;
        }

        if ($this->cancelCallback) {
            $cancelCallback = $this->cancelCallback;
            $this->cancelCallback = null;

            try {
                $cancelCallback();
            } catch (\Exception $e) {
                $this->reject($e);
            }
        }

        if ($this->currentState() === self::STATE_PENDING) {
            $this->reject(new \Exception('Promise has been cancelled.'));
        }

        $e = $this->current instanceof PromiseInterface
            ? $this->current->cancel()
            : $this->current;

        if ($this->current instanceof PromiseInterface || $this->currentState() === self::STATE_REJECTED) {
            return $e;
        }
    }

    public function currentState()
    {
        return $this->state;
    }

    private function trigger($value)
    {
        if ($this->state === self::STATE_PENDING) {
            throw new \LogicException(
                "Cannot resolving or rejecting promise when in pending state."
            );
        }

        $context = $this->context;
        $this->context = null;
        $this->current = $value;
        
        if (!method_exists($value, 'then')) {
            $index = $this->state === self::STATE_FULFILLED ? 1 : 2;

            Context\ContextStack::create(new TaskQueue)->store(
            static function () use ($index, $context, $value) {
                foreach ($context as $c) {
                    self::invokeContext($c, $index, $value);
                }
            });

            Context\ContextStack::getQueueHandler()->run();
        } elseif ($value instanceof Promise) {
            $value->context = array_merge($value->context, $context);
        } else {
            $value->then(
            static function ($value) use ($context) {
                foreach ($context as $c) {
                    self::invokeContext($c, 1, $value);
                }
            },
            static function ($reason) use ($context) {
                foreach ($context as $c) {
                    self::invokeContext($c, 2, $reason);
                }
            }
            );
        }
    }

    private static function invokeContext($context, $index, $value)
    {
        $promise = $context[0];

        if ($promise->currentState() !== self::STATE_PENDING) {
            return;
        }

        try {
            if (isset($context[$index])) {
                $promise->resolve($context[$index]($value));
            } elseif ($index === 1) {
                $promise->resolve($value);
            } else {
                $promise->reject($value);
            }
        } catch (\Exception $e) {
            $promise->reject($e);
        }
    }

    private function setState($state)
    {
        if ($state !== self::STATE_PENDING &&
            $state !== self::STATE_FULFILLED &&
            $state !== self::STATE_REJECTED) {
            throw new \InvalidArgumentException(
                sprintf("Parameter 1 of %s must be a valid promise state.", __METHOD__)
            );
        }

        $this->state = $state;
    }
}
