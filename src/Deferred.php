<?php

namespace Gandung\Promise;

class Deferred implements PromisorInterface
{
    /**
     * @var PromiseInterface
     */
    private $promise;

    public function promise()
    {
        if (null === $this->promise) {
            $this->promise = new Promise;
        }

        return $this->promise;
    }

    public function resolve($value)
    {
        $this->promise()->resolve($value);
    }

    public function reject($reason)
    {
        $this->promise()->reject($reason);
    }
}
