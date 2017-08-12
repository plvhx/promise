<?php

namespace Gandung\Promise;

class Deferred implements PromisorInterface
{
    /**
     * @var PromiseInterface
     */
    private $promise;

    /**
     * {@inheritdoc}
     */
    public function promise()
    {
        if (null === $this->promise) {
            $this->promise = new Promise;
        }

        return $this->promise;
    }

    /**
     * Resolving promise with given value.
     *
     * @param mixed $value
     */
    public function resolve($value)
    {
        $this->promise()->resolve($value);
    }

    /**
     * Rejecting promise with given reason.
     *
     * @param mixed $reason
     */
    public function reject($reason)
    {
        $this->promise()->reject($reason);
    }
}
