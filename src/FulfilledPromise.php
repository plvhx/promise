<?php

namespace Gandung\Promise;

/**
 * @author Paulus Gandung Prakosa <rvn.plvhx@gmail.com>
 */
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

    /**
     * {@inheritdoc}
     */
    public function then($onFulfilled = null, $onRejected = null)
    {
        if (!$onFulfilled) {
            return $this;
        }

        $q = new Promise();
        $value = $this->value;

        Context\ContextStack::create()->store(static function () use ($q, $value, $onFulfilled) {
            if ($q->currentState() === self::STATE_PENDING) {
                try {
                    $q->resolve($onFulfilled($value));
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
     * Resolving the promise if given value are equal to current value. Otherwise,
     * throws an exception.
     *
     * @param integer $value
     * @throws \Exception
     */
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

    /**
     * Rejecting this promise will throw an exception because the state of current
     * promise is fulfilled.
     *
     * @param mixed $reason
     * @throws \Exception
     */
    public function reject($reason)
    {
        throw new \LogicException(
            sprintf(
                "Cannot rejected a promise from context '%s'", __CLASS__
            )
        );
    }

    /**
     * Return the state of current promise.
     *
     * @return integer
     */
    public function currentState()
    {
        return self::STATE_FULFILLED;
    }
}
