# Promises/A+ Implementation In PHP

<a href="https://promisesaplus.com/">
    <img src="https://promisesaplus.com/assets/logo-small.png" alt="Promises/A+ logo"
         title="Promises/A+ 1.0 compliant" align="right" />
</a>

This is a [Promises/A+](https://promisesaplus.com) implementation in PHP. This handles promise chaining immutably until all context handler is all gone from execution context.

## Features

- Promises/A+ implementation.
- Promise resolution and chaining is handled iteratively.

# API

# Promise

- `then(callable $onFulfilled, callable $onRejected)`

## Description

Appends fulfillment and rejection handlers to the promise, and returns an immutable new promise resolving to the return value of the called handler.

## Return Value

An immutable new promise.

- `resolve($value)`

## Description

Fulfills the promise with the given '$value'

## Return Value

None.

- `reject($reason)`

## Description

Rejects the promise with the given '$reason'

## Return Value

None.

- `currentState()`

## Description

Returns the state of the promise. List of promise state can be found in 'PromiseInterface' class.

## Return Value

Either STATE_PENDING, STATE_FULFILLED, OR STATE_REJECTED