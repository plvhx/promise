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
    
    /**
     * Statically create an instance of this class.
     *
     * @param TaskQueue
     */
    public static function create()
    {
        if (null === static::$queueHandler) {
            static::$queueHandler = new TaskQueue;
        }

        return new static;
    }

    /**
     * Store a value into task queueing stack.
     *
     * @param \Closure|array $handler
     * @param mixed $value
     */
    public function store($handler, $value = [])
    {
        $value = is_array($value)
            ? $value
            : array_slice(func_get_args(), 1);

        if ($handler instanceof \Closure) {
            self::$queueHandler->add(new FunctionInvoker($handler), $value);
        } else {
            self::$queueHandler->add(new MethodInvoker($handler), $value);
        }
    }

    /**
     * Get task queue handler.
     *
     * @return TaskQueue
     */
    public static function getQueueHandler()
    {
        return self::$queueHandler;
    }
}
