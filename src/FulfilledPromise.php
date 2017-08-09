<?php

namespace Gandung\Promise;

use TaskQueue\TaskQueue;

class FulfilledPromise implements PromiseInterface
{
    /**
     * @var mixed
     */
    private $value;

    public function __construct($value)
    {
        if (method_exists($value, 'then')) {
            throw new \InvalidArgumentException(
                sprintf("Unable to get instance of %s with promise as constructor parameter.", __CLASS__)
            );
        }

        $this->value = $value;
    }

    public function then($onFulfilled = null, $onRejected = null)
    {
        if (!$onFulfilled) {
            return $this;
        }

        $q = new Promise();
        $value = $this->value;

        Context\ContextStack::create(new TaskQueue)->store(static function () use ($q, $value, $onFulfilled) {
            if ($q->currentState() === self::STATE_PENDING) {
                try {
                    $q->resolve($onFulfilled($value));
                } catch (\Exception $e) {
                    $q->reject($e);
                }
            }
        });

        Context\ContextStack::getQueueHandler()->run();
        
        return $q;
    }

    public function resolve($value)
    {
        if ($value !== $this->value) {
            throw new \LogicException(
                sprintf(
                    "Supplied value does not strictly equal to current value." .
                    "Cannot resolve promise from context '%s'", __CLASS__
                )
            );
        }
    }

    public function reject($value)
    {
        throw new \LogicException(
            sprintf(
                "Cannot rejected a promise from context '%s'", __CLASS__
            )
        );
    }

    public function currentState()
    {
        return self::STATE_FULFILLED;
    }
}
