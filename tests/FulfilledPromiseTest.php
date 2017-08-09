<?php

namespace Gandung\Promise\Tests;

use Gandung\Promise\Promise;
use Gandung\Promise\FulfilledPromise;

class FulfilledPromiseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCanThrowExceptionWhenGetAnInstance()
    {
        $promise = new FulfilledPromise(new Promise);
    }

    public function testCanReturnSelfAfterThen()
    {
        $promise = new FulfilledPromise(31337);
        $inst = $promise->then(null, null);

        $this->assertInstanceOf(FulfilledPromise::class, $inst);
    }

    public function testCanHandleExceptionWhenCallThenMethod()
    {
        $promise = new FulfilledPromise('For your own good.');
        $promise->then(function ($d) {
            throw new \Exception($d);
        });
        $promise->resolve('For your own good.');
    }

    /**
     * @expectedException \LogicException
     */
    public function testCanThrowExceptionWhileResolvingWithDifferentValue()
    {
        $promise = new FulfilledPromise('this is a text.');
        $promise->then(function ($d) {
            echo sprintf("%s" . PHP_EOL, $d);
        });
        $promise->resolve('this is a shit.');
    }

    /**
     * @expectedException \LogicException
     */
    public function testCanThrowExceptionWhileRejectingFulfilledPromise()
    {
        $promise = new FulfilledPromise('this is a text.');
        $promise->then(function ($d) {
            echo sprintf("%s" . PHP_EOL, $d);
        });
        $promise->reject('shit.');
    }

    public function testCanGetPromiseState()
    {
        $promise = new FulfilledPromise('this is a shit.');
        $this->assertInternalType('integer', $promise->currentState());
        $this->assertEquals(4, $promise->currentState());
    }
}
