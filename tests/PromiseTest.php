<?php

namespace Gandung\Promise\Tests;

use Gandung\Promise\Promise;
use Gandung\Promise\FulfilledPromise;
use Gandung\Promise\RejectedPromise;

class PromiseTest extends \PHPUnit_Framework_TestCase
{
	public function callNonPublicMethod(&$object, $method, $fcall = false, $parameters)
	{
		$reflector = new \ReflectionClass($object);
		$q = $reflector->getMethod($method);
		$q->setAccessible(true);

		$context = ($fcall === true ? null : $object);

		return $q->invokeArgs($context, (is_array($parameters) ? $parameters
			: array_slice(func_get_args(), 3)));
	}

	public function testIfPromiseCanBeFulfilled()
	{
		$promise = new Promise();
		$promise->resolve('This is a text.');
	}

	public function testIfPromiseCanBeFulfilledWithSameData()
	{
		$promise = new Promise();
		$promise->resolve('This is a text.');
		$promise->resolve('This is a text.');
	}

	/**
	 * @expectedException \LogicException
	 */
	public function testCannotResolveOrRejectCurrentPromiseWhenPending()
	{
		$promise = new Promise();
		$this->callNonPublicMethod($promise, 'setState', false, 1);
		$this->callNonPublicMethod($promise, 'trigger', false, 'foo');
	}

	public function testIfPromiseCanBeFulfilledWithResolvingCallback()
	{
		$promise = new Promise();

		$promise
			->then(function($d) {
				echo sprintf("%s" . PHP_EOL, $d);
			});

		$promise->resolve('this is a text.');
	}

	public function testIfPromiseCanBeFulfilledWithResolvingPromise()
	{
		$promise = new Promise();

		$promise
			->then(function($d) {
				echo sprintf("%s" . PHP_EOL, $d);
			});

		$promise->resolve(new FulfilledPromise('this is a text.'));
	}

	public function testIfPromiseChainCanBeFulfilledWithResolvingCallback()
	{
		$promise = new Promise();

		$promise
			->then(function($d) {
				return $d;
			})
			->then(function($d) {
				echo sprintf("%s" . PHP_EOL, $d);
			});

		$promise->resolve('This is a text.');
	}

	public function testIfPromiseCanBeRejected()
	{
		$promise = new Promise();
		$promise->reject('shit!.');
	}

	public function testIfPromiseCanBeRejectedWithSameData()
	{
		$promise = new Promise();
		$promise->reject('shit!.');
		$promise->reject('shit!.');
	}

	public function testIfPromiseCanBeRejectedWithRejectingCallback()
	{
		$promise = new Promise();
		$promise
			->then(null, function($e) {
				echo sprintf("%s" . PHP_EOL, $e);
			});
		$promise->reject('shit!.');
	}

	public function testIfPromiseCanBeRejectedWithRejectingPromise()
	{
		$promise = new Promise();
		$promise
			->then(null, function($e) {
				echo sprintf("%s" . PHP_EOL, $e);
			});
		$promise->reject(new RejectedPromise('this is a text.'));
	}

	public function testIfPromiseChainCanBeRejectedWithRejectingCallback()
	{
		$promise = new Promise();
		$promise
			->then(null, function($e) {
				return new RejectedPromise($e);
			})
			->then(null, function($e) {
				echo sprintf("%s" . PHP_EOL, $e);
			});
		$promise->reject('this is a text.');
	}

	public function testCanInvokeContextWithNullContextAsParameterWithIndex1()
	{
		$promise = new Promise();
		$this->callNonPublicMethod($promise, 'invokeContext', true, [[$promise], 1, 'this is a text.']);
	}

	public function testCanInvokeContextWithNullContextAsParameterWithIndex2()
	{
		$promise = new Promise();
		$this->callNonPublicMethod($promise, 'invokeContext', true, [[$promise], 2, 'this is a text.']);
	}

	public function testCanInvokeContextWithNullContextAsParameterWhileThrowingException()
	{
		$promise = new Promise();
		$fcall = function($e) {
			throw new \Exception($e);
		};

		$this->callNonPublicMethod($promise, 'invokeContext', true, [[$promise, $fcall], 1, 'this is a text.']);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testIfSetStateCanThrowException()
	{
		$promise = new Promise();
		$this->callNonPublicMethod($promise, 'setState', false, 31337);
	}

	public function testIfValueIsAnContext()
	{
		$promise = new Promise();
		$promise->resolve(new Promise);
	}

	public function testInvokeContextInPendingState()
	{
		$promise = new Promise();
		$this->callNonPublicMethod($promise, 'setState', false, 4);
		$this->callNonPublicMethod($promise, 'invokeContext', true, [[$promise], 1, 'this is a text']);
	}
}