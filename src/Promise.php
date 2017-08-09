<?php

namespace Gandung\Promise;

use TaskQueue\TaskQueue;

class Promise implements PromiseInterface
{
	/**
	 * @var integer
	 */
	private $state = self::STATE_PENDING;

	/**
	 * @var mixed
	 */
	private $current;

	/**
	 * @var array
	 */
	private $context = [];

	/**
	 * @var integer
	 */
	private $prevState;

	public function then($onFulfilled = null, $onRejected = null)
	{
		if ($this->state === self::STATE_PENDING) {
			$q = new Promise();
			$this->context[] = [$q, $onFulfilled, $onRejected];

			return $q;
		}

		if ($this->state === self::STATE_FULFILLED) {
			return $onFulfilled
				? (new FulfilledPromise($this->current))->then($onFulfilled, null)
				: new FulfilledPromise($this->current);
		}

		return $onRejected
			? (new RejectedPromise($this->current))->then(null, $onRejected)
			: new RejectedPromise($this->current);
	}

	public function resolve($value)
	{
		$this->setState(self::STATE_FULFILLED);
		$this->trigger($value);
	}

	public function reject($reason)
	{
		$this->setState(self::STATE_REJECTED);
		$this->trigger($reason);
	}

	public function currentState()
	{
		return $this->state;
	}

	private function trigger($value)
	{
		if ($this->state === self::STATE_PENDING) {
			throw new \LogicException(
				"Cannot resolving or rejecting promise when in pending state."
			);
		}

		if ($value === $this->current && $this->prevState === $this->state) {
			return;
		}

		$this->prevState = $this->state;

		$context = $this->context;
		$this->context = null;
		$this->current = $value;
		
		if (!method_exists($value, 'then')) {
			$index = $this->state === self::STATE_FULFILLED ? 1 : 2;

			Context\ContextStack::create(new TaskQueue)->store(
				static function() use ($index, $context, $value) {
					foreach ($context as $c) {
						self::invokeContext($c, $index, $value);
					}
				});

			Context\ContextStack::getQueueHandler()->run();
		} else if ($value instanceof Promise) {
			$value->context = array_merge($value->context, $context);
		} else {
			$value->then(
				static function ($value) use ($context) {
					foreach ($context as $c) {
						self::invokeContext($c, 1, $value);
					}
				},
				static function ($reason) use ($context) {
					foreach ($context as $c) {
						self::invokeContext($c, 2, $reason);
					}
				}
			);
		}
	}

	private static function invokeContext($context, $index, $value)
	{
		$promise = $context[0];

		if ($promise->currentState() !== self::STATE_PENDING) {
			return;
		}

		try {
			if (isset($context[$index])) {
				$promise->resolve($context[$index]($value));
			} else if ($index === 1) {
				$promise->resolve($value);
			} else {
				$promise->reject($value);
			}
		} catch (\Exception $e) {
			$promise->reject($e);
		}
	}

	private function setState($state)
	{
		if ($state !== self::STATE_PENDING &&
			$state !== self::STATE_FULFILLED &&
			$state !== self::STATE_REJECTED) {
			throw new \InvalidArgumentException(
				sprintf("Parameter 1 of %s must be a valid promise state.", __METHOD__)
			);
		}

		$this->state = $state;
	}
}