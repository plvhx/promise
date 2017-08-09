<?php

namespace Gandung\Promise\Tests;

use Gandung\Promise\Promise;
use Gandung\Promise\RejectedPromise;

class RejectedPromiseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCanThrowExceptionWhileGetInstance()
    {
        $promise = new RejectedPromise(new Promise);
    }

    public function testCanReturnSelf()
    {
        $promise = new RejectedPromise('shit!');
        $promise->then(null, null);
        $this->assertInstanceOf(RejectedPromise::class, $promise);
    }

    public function testCanHandleExceptionWhileCallThen()
    {
        $promise = new RejectedPromise('shit!');
        $promise->then(null, function ($e) {
            throw new \Exception($e);
        });
        $promise->reject('shit!');
    }

    /**
     * @expectedException \LogicException
     */
    public function testCanThrowExceptionWhileTryingToResolvePromise()
    {
        $promise = new RejectedPromise('shit!');
        $promise->then(null, function ($e) {
            echo sprintf("%s" . PHP_EOL, $e);
        });
        $promise->resolve('good boy!');
    }

    /**
     * @expectedException \LogicException
     */
    public function testCanThrowExceptionWhileTryingToRejectPromiseWithDifferentValue()
    {
        $promise = new RejectedPromise('shit!');
        $promise->then(null, function ($e) {
            echo sprintf("%s" . PHP_EOL, $e);
        });
        $promise->reject('fvck.');
    }

    public function testCanGetState()
    {
        $promise = new RejectedPromise('shit!');
        $this->assertInternalType('integer', $promise->currentState());
        $this->assertEquals(8, $promise->currentState());
    }
}
