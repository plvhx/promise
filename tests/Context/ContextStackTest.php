<?php

namespace Gandung\Promise\Tests\Context;

use Gandung\Promise\Context\ContextStack;

class ContextStackTest extends \PHPUnit_Framework_TestCase
{
    public function testCanGetInstance()
    {
        $contextStack = ContextStack::create();
        $this->assertInstanceOf(ContextStack::class, $contextStack);
    }

    public function testCanStoreClosureIntoStack()
    {
        ContextStack::create()->store(function () {
            echo "i'm dead actually." . PHP_EOL;
        });
    }

    public function testCanStoreMethodIntoStack()
    {
        ContextStack::create()->store(['instance' => new \SplFixedArray, 'method' => 'getSize']);
    }
}
