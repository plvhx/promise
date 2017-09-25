<?php

namespace Gandung\Promise;

/**
 * @author Paulus Gandung Prakosa <rvn.plvhx@gmail.com>
 */
class RejectedPromise implements PromiseInterface
{
    /**
     * @var mixed
     */
    private $reason;

    /**
     * {@inheritdoc}
     */
    public function __construct($reason)
    {
        if (method_exists($reason, 'then')) {
            throw new \InvalidArgumentException(
                sprintf("Unable to get instance of %s with promise as constructor parameter.", __CLASS__)
            );
        }

        $this->reason = $reason;
    }

    /**
     * {@inheritdoc}
     */
    public function then($onFulfilled = null, $onRejected = null)
    {
        if (!$onRejected) {
            return $this;
        }

        $q = new Promise();
        $reason = $this->reason;

        Context\ContextStack::create()->store(static function () use ($q, $reason, $onRejected) {
            if ($q->currentState() === self::STATE_PENDING) {
                try {
                    $q->resolve($onRejected($reason));
                } catch (\Throwable $e) {
                    $q->reject($e);
                } catch (\Exception $e) {
                    $q->reject($e);
                }
            }
        });

        Context\ContextStack::getQueueHandler()->run();

        return $q;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve($value)
    {
        throw new \LogicException(
            sprintf(
                "Cannot resolving a promise from context '%s'", __CLASS__
            )
        );
    }

    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
    public function currentState()
    {
        return self::STATE_REJECTED;
    }
}
