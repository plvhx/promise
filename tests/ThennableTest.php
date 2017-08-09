<?php

namespace Gandung\Promise\Tests;

use Gandung\Promise\Promise;

class Thennable extends \PHPUnit_Framework_TestCase
{
    public function callNonPublicMethod(&$object, $method, $parameters)
    {
        $reflector = new \ReflectionClass($object);
        $q = $reflector->getMethod($method);
        $q->setAccessible(true);

        return $q->invokeArgs($object, is_array($parameters) ? $parameters
            : array_slice(func_get_args(), 2));
    }

    public function testCanThennableInPendingState()
    {
        $promise = new Promise();
        $this->callNonPublicMethod($promise, 'setState', 1);
        $promise->then(function ($q) {
            echo sprintf("%s" . PHP_EOL, $q);
        });
    }

    public function testCanThennableInFulfilledState()
    {
        $promise = new Promise();
        $this->callNonPublicMethod($promise, 'setState', 4);
        $promise->then(function ($q) {
            echo sprintf("%s" . PHP_EOL, $q);
        });
    }

    public function testCanThennableInRejectedState()
    {
        $promise = new Promise();
        $this->callNonPublicMethod($promise, 'setState', 8);
        $promise->then(function ($q) {
            echo sprintf("%s" . PHP_EOL, $q);
        });
    }
}
