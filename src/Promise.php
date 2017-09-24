<?php

namespace Gandung\Promise;

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

    /**
     * Appends fulfillment and rejection handlers to the promise, and returns an immutable
     * new promise resolving to the return value of the handler.
     *
     * @param \Closure $onFulfilled Callback to be called when promise state is fulfilled.
     * @param \Closure $onRejected Callback to be called when promise state is rejected.
     * @return Promise
     */
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

    /**
     * Set waiting callback.
     *
     * @param \Closure $callback Callback to be set.
     * @return void
     */
    public function setWaitCallback(\Closure $callback = null)
    {
        if (!($callback instanceof \Closure)) {
            throw new \InvalidArgumentException(
                sprintf("Parameter 1 of %s must be a valid callback.", __METHOD__)
            );
        }

        $this->waitCallback = $callback;
    }

    /**
     * Set cancellation callback.
     *
     * @param \Closure $callback Callback to be set.
     * @return void
     */
    public function setCancelCallback(\Closure $callback = null)
    {
        if (!($callback instanceof \Closure)) {
            throw new \InvalidArgumentException(
                sprintf("Parameter 1 of %s must be a valid callback.", __METHOD__)
            );
        }

        $this->cancelCallback = $callback;
    }

    /**
     * Comparing current promise state to new state and current promise value
     * to new value.
     *
     * @param mixed $value Value to be compare.
     * @param integer $state New promise state to be compare.
     * @return null|boolean
     */
    private function validateState($value, $state)
    {
        if ($this->state !== self::STATE_PENDING) {
            if ($value === $this->current && $state === $this->state) {
                return null;
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
        }

        if ($value === $this) {
            throw new \LogicException(
                "Unable to fulfill or reject a promise with itself."
            );
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve($value)
    {
        $this->isStateTransformed = $this->validateState($value, self::STATE_FULFILLED);

        if ($this->isStateTransformed) {
            $this->setState(self::STATE_FULFILLED);
            $this->trigger($value);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function reject($reason)
    {
        $this->isStateTransformed = $this->validateState($reason, self::STATE_REJECTED);

        if ($this->isStateTransformed) {
            $this->setState(self::STATE_REJECTED);
            $this->trigger($reason);
        }
    }

    /**
     * Synchronously forces promise to complete using wait method.
     *
     * @return mixed
     */
    public function wait()
    {
        $this->waitInPendingState();

        $q = $this->current instanceof PromiseInterface
            ? $this->current->wait()
            : $this->current;

        if ($this->current instanceof PromiseInterface || $this->currentState() === self::STATE_FULFILLED) {
            return $q;
        } else {
            throw $q instanceof \Exception
                ? $q
                : new \Exception($q);
        }
    }

    /**
     * Cancel a promise that has not yet been fulfilled.
     *
     * @return mixed
     */
    public function cancel()
    {
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
            $this->reject('Promise has been cancelled.');
        }

        $e = $this->current instanceof PromiseInterface
            ? $this->current->cancel()
            : $this->current;

        if ($this->current instanceof PromiseInterface || $this->currentState() === self::STATE_REJECTED) {
            return $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function currentState()
    {
        return $this->state;
    }

    /**
     * Invoking promise handler based on current state and given value.
     *
     * @param mixed $value
     * @return void
     */
    private function trigger($value)
    {
        $context = $this->context;
        $this->context = null;
        $this->current = $value;
        $this->waitCallback = null;
        $this->cancelCallback = null;

        if (!method_exists($value, 'then')) {
            $index = $this->state === self::STATE_FULFILLED ? 1 : 2;

            Context\ContextStack::create()->store(
            static function () use ($index, $context, $value) {
                foreach ($context as $c) {
                    self::invokeContext($c, $index, $value);
                }
            });

            Context\ContextStack::getQueueHandler()->run();
        } elseif ($value instanceof Promise && $value->currentState() === self::STATE_PENDING) {
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

    /**
     * Invoking handler based on given context, index, and value.
     *
     * @param array $context
     * @param integer $index
     * @param mixed $value
     * @return void
     */
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
        } catch (\Throwable $e) {
            $promise->reject($e);
        } catch (\Exception $e) {
            $promise->reject($e);
        }
    }

    /**
     * Set promise state to given state.
     *
     * @param integer $state
     * @return void
     */
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

    /**
     * Synchronously forces promise to wait when current promise state is pending.
     *
     * @return void
     */
    private function waitInPendingState()
    {
        if ($this->currentState() !== self::STATE_PENDING) {
            return;
        } elseif ($this->waitCallback) {
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
        }

        Context\ContextStack::getQueueHandler()->run();

        if ($this->currentState() === self::STATE_PENDING) {
            $this->reject(
                'Invoking the synchronous wait callback resolver didn\'t resolve the current promise'
            );
        }
    }
}
