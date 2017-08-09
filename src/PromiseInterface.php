<?php

namespace Gandung\Promise;

interface PromiseInterface
{
    const STATE_PENDING = 1;

    const STATE_FULFILLED = 4;

    const STATE_REJECTED = 8;

    public function then($onFulfilled = null, $onRejected = null);

    public function resolve($value);

    public function reject($reason);

    public function currentState();
}
