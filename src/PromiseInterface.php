<?php

namespace Gandung\Promise;

interface PromiseInterface
{
    /**
     * Indicates the state of current promise is pending.
     */
    const STATE_PENDING = 1;

    /**
     * Indicates the state of current promise is fulfilled.
     */
    const STATE_FULFILLED = 4;

    /**
     * Indicates the state of current promise is rejected.
     */
    const STATE_REJECTED = 8;

    /**
     * Appends fulfillment and rejection handlers to the promise, and returns an immutable
     * new promise resolving to the return value of the handler.
     *
     * @param \Closure $onFulfilled Callback to be called when promise state is fulfilled.
     * @param \Closure $onRejected Callback to be called when promise state is rejected.
     * @return Promise
     */
    public function then($onFulfilled = null, $onRejected = null);

    /**
     * Resolving the promise with given value.
     *
     * @param mixed $value
     * @return void
     */
    public function resolve($value);

    /**
     * Rejected the promise with given reason.
     *
     * @param mixed $reason
     * @return void
     */
    public function reject($reason);

    /**
     * Getting current promise state.
     *
     * @return integer
     */
    public function currentState();
}
