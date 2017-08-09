# Promises/A+ Implementation In PHP

[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%205.6-8892BF.svg?style=flat-square)](https://php.net/)
[![Build status](https://ci.appveyor.com/api/projects/status/8mx5j820l2fsqp41?svg=true)](https://ci.appveyor.com/project/plvhx/promise)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/14e50be8-c441-4d03-b65d-cb07e33c0672/mini.png)](https://insight.sensiolabs.com/projects/14e50be8-c441-4d03-b65d-cb07e33c0672)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/plvhx/promise/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/plvhx/promise/?branch=master)

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

## Quick Start

```php
use Gandung\Promise\Promise;

$promise = new Promise();

$promise->then(
	function($d) {
		echo sprintf("%s" . PHP_EOL, $d);
	},
	function($e) {
		throw new \Exception($e);
	}
);
```

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

# FulfilledPromise same as Promise

## Quick Start

```php
use Gandung\Promise\FulfilledPromise;

$promise = new FulfilledPromise('the quick dirty brown fox.');

$promise->then(function($d) {
	echo sprintf("%s" . PHP_EOL, $d);
});
```

# RejectedPromise same as Promise

## Quick Start

```php
use Gandung\Promise\RejectedPromise;

$promise = new RejectedPromise('the quick dirty brown fox.');

$promise->then(null, function($e) {
	throw new \Exception($e);
});
```

# Contributors

- [Paulus Gandung Prakosa](https://github.com/plvhx)

# License

BSD 3-Clause License

Copyright (c) 2017, Paulus Gandung Prakosa
All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are met:

* Redistributions of source code must retain the above copyright notice, this
  list of conditions and the following disclaimer.

* Redistributions in binary form must reproduce the above copyright notice,
  this list of conditions and the following disclaimer in the documentation
  and/or other materials provided with the distribution.

* Neither the name of the copyright holder nor the names of its
  contributors may be used to endorse or promote products derived from
  this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
