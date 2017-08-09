<?php

namespace Gandung\Promise\Tests\Context;

use Gandung\Promise\Context\ContextStack;
use TaskQueue\TaskQueue;

class ContextStackTest extends \PHPUnit_Framework_TestCase
{
    public function testCanGetInstance()
    {
        $contextStack = ContextStack::create(new TaskQueue);
        $this->assertInstanceOf(ContextStack::class, $contextStack);
    }

    public function testCanStoreClosureIntoStack()
    {
        ContextStack::create(new TaskQueue)->store(function () {
            echo "i'm dead actually." . PHP_EOL;
        });
    }

    public function testCanStoreMethodIntoStack()
    {
        ContextStack::create(new TaskQueue)->store(['instance' => new \SplFixedArray, 'method' => 'getSize']);
    }
}
