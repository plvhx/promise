<?php

namespace Gandung\Promise;

interface PromisorInterface
{
    /**
     * Return the promise object.
     *
     * @return PromiseInterface
     */
    public function promise();
}
