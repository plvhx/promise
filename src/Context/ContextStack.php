<?php

namespace Gandung\Promise\Context;

use TaskQueue\TaskQueue;
use TaskQueue\Invoker\FunctionInvoker;
use TaskQueue\Invoker\MethodInvoker;
use DependencyInjection\Container;

class ContextStack
{
	/**
	 * @var array
	 */
	private static $queueHandler;
	
	public static function create(TaskQueue $queue)
	{
		if (null === static::$queueHandler) {
			static::$queueHandler = $queue;
		}

		return new static;
	}

	public function store($handler, $value = [])
	{
		if ($handler instanceof \Closure) {
			self::$queueHandler->add(new FunctionInvoker($handler), $value);
		}
		else {
			self::$queueHandler->add(new MethodInvoker(new Container, $handler), $value);
		}
	}

	public static function getQueueHandler()
	{
		return self::$queueHandler;
	}
}