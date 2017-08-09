<?php

namespace Gandung\Promise;

use TaskQueue\TaskQueue;

class RejectedPromise implements PromiseInterface
{
    /**
     * @var mixed
     */
    private $reason;

    public function __construct($reason)
    {
        if (method_exists($reason, 'then')) {
            throw new \InvalidArgumentException(
                sprintf("Unable to get instance of %s with promise as constructor parameter.", __CLASS__)
            );
        }

        $this->reason = $reason;
    }

    public function then($onFulfilled = null, $onRejected = null)
    {
        if (!$onRejected) {
            return $this;
        }

        $q = new Promise();
        $reason = $this->reason;

        Context\ContextStack::create(new TaskQueue)->store(static function () use ($q, $reason, $onRejected) {
            if ($q->currentState() === self::STATE_PENDING) {
                try {
                    $q->resolve($onRejected($reason));
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
        throw new \LogicException(
            sprintf(
                "Cannot resolving a promise from context '%s'", __CLASS__
            )
        );
    }

    public function reject($reason)
    {
        if ($reason !== $this->reason) {
            throw new \LogicException(
                sprintf(
                    "Supplied value does not strictly equal to current value." .
                    "Cannot reject promise from context '%s'", __CLASS__
                )
            );
        }
    }

    public function currentState()
    {
        return self::STATE_REJECTED;
    }
}
