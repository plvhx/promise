<?php

namespace Gandung\Promise\Tests;

use Gandung\Promise\Promise;
use Gandung\Promise\FulfilledPromise;
use Gandung\Promise\RejectedPromise;
use Gandung\Promise\Tests\Fixtures\Exception\CatchableException;

class PromiseTest extends \PHPUnit_Framework_TestCase
{
    public function testCanGetInstance()
    {
        $promise = new Promise();
        $this->assertInstanceOf(Promise::class, $promise);
    }

    public function testCanResolveUnchainedPromiseWithoutFulfilledCallback()
    {
        $promise = new Promise();
        $this->assertInstanceOf(Promise::class, $promise);
        $promise->resolve('this is a text.');
    }

    public function testCanRejectUnchainedPromiseWithoutRejectionCallback()
    {
        $promise = new Promise();
        $this->assertInstanceOf(Promise::class, $promise);
        $promise->reject('shit!.');
    }

    public function testCanResolveUnchainedPromise()
    {
        $promise = new Promise();
        $this->assertInstanceOf(Promise::class, $promise);
        $promise->then(
            function ($d) {
                echo sprintf("%s" . PHP_EOL, $d);
            },
            function ($e) {
                throw new \Exception($e);
            }
        );
        $promise->resolve('this is a text');
    }

    public function testCanRejectUnchainedPromise()
    {
        $promise = new Promise();
        $this->assertInstanceOf(Promise::class, $promise);
        $promise
            ->then(
                function ($d) {
                    echo sprintf("%s" . PHP_EOL, $d);
                },
                function ($e) {
                    throw new \Exception($e);
                }
            );
        $promise->reject('shit!.');
    }

    public function testCanResolveChainedPromise()
    {
        $promise = new Promise();
        $this->assertInstanceOf(Promise::class, $promise);
        $promise
            ->then(function ($d) {
                return $d;
            }, null)
            ->then(function ($d) {
                echo sprintf("%s" . PHP_EOL, $d);
            }, null);
        $promise->resolve('this is a text.');
    }

    public function testCanRejectChainedPromise()
    {
        $promise = new Promise();
        $this->assertInstanceOf(Promise::class, $promise);
        $promise
            ->then(
                function ($d) {
                    return $d;
                },
                function ($e) {
                    throw new \Exception($e);
                }
            )
            ->then(
                function ($d) {
                    echo sprintf("%s" . PHP_EOL, $d);
                },
                function ($e) {
                    echo sprintf("%s" . PHP_EOL, $e->getMessage());
                }
            );
        $promise->reject('encountered an error.');
    }

    public function testCanSynchronouslyWaitWithSuppliedCallback()
    {
        $promise = new Promise();
        $this->assertInstanceOf(Promise::class, $promise);
        $promise->setWaitCallback(
            function ($d) {
                echo sprintf("%s" . PHP_EOL, $d);
            }
        );
        $promise->then(
            function ($d) {
                echo sprintf("%s" . PHP_EOL, $d);
            },
            function ($e) {
                throw new \Exception($e);
            }
        );
        $promise->resolve('this is a text.');
        $result = $promise->wait();
        $this->assertInternalType('string', $result);
        $this->assertEquals('this is a text.', $result);
    }

    public function testCanSynchronouslyWaitOnChainedPromiseWithSuppliedCallback()
    {
        $promise = new Promise();
        $this->assertInstanceOf(Promise::class, $promise);
        $promise->setWaitCallback(
            function () {
                echo "All tasks has been synchronously waited." . PHP_EOL;
            }
        );
        $promise
            ->then(
                function ($d) {
                    return $d;
                },
                function ($e) {
                    throw new \Exception($e);
                }
            )
            ->then(
                function ($d) {
                    echo sprintf("On chained promise(resolved): %s" . PHP_EOL, $d);
                },
                function ($e) {
                    echo sprintf("On chained promise(rejected): %s" . PHP_EOL, $e->getMessage());
                }
            );
        $promise->resolve('this is a text.');
        $result = $promise->wait();
        $this->assertInternalType('string', $result);
        $this->assertEquals('this is a text.', $result);
    }

    public function testCanDoCancellationWithSuppliedCallback()
    {
        $promise = new Promise();
        $this->assertInstanceOf(Promise::class, $promise);
        $promise->setCancelCallback(
            function () {
                echo "All tasks has been cancelled." . PHP_EOL;
            }
        );
        $promise
            ->then(
                function ($d) {
                    return $d;
                },
                function ($e) {
                    throw new \Exception($e);
                }
            );
        $result = $promise->cancel();
        $this->assertNotNull($result);
        $this->assertInternalType('string', $result);
    }

    public function testCanDoCancellationOnChainedPromiseWithSuppliedCallback()
    {
        $promise = new Promise();
        $this->assertInstanceOf(Promise::class, $promise);
        $promise->setCancelCallback(
            function () {
                echo "All tasks has been cancelled." . PHP_EOL;
            }
        );
        $promise
            ->then(
                function ($d) {
                    return $d;
                },
                function ($e) {
                    throw new \Exception($e);
                }
            )
            ->then(
                function ($d) {
                    echo sprintf("%s" . PHP_EOL, $d);
                },
                function ($e) {
                    echo sprintf("%s" . PHP_EOL, $e->getMessage());
                }
            );
        $result = $promise->cancel();
        $this->assertNotNull($result);
        $this->assertInternalType('string', $result);
    }

    /**
     * @expectedException \Exception
     */
    public function testCanThrowExceptionWhenTryingToResolvePromiseWhichHasBeenCancelled()
    {
        $promise = new Promise();
        $this->assertInstanceOf(Promise::class, $promise);
        $promise->cancel();
        $promise->resolve('this is a text.');
    }

    /**
     * @expectedException \Exception
     */
    public function testCannotContinouslyCallWaitWhenPreviousHasBeenResolved()
    {
        $promise = new Promise();
        $this->assertInstanceOf(Promise::class, $promise);
        $promise
            ->then(
                function ($d) {
                    echo sprintf("%s" . PHP_EOL, $d);
                },
                function ($e) {
                    echo sprintf("%s" . PHP_EOL, $e);
                }
            );
        $promise->wait();
        $promise->wait();
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCanThrowExceptionWhenSetInvalidWaitCallback()
    {
        $promise = new Promise();
        $this->assertInstanceOf(Promise::class, $promise);
        $promise->setWaitCallback(null);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCanThrowExceptionWhenSetCancellationCallback()
    {
        $promise = new Promise();
        $this->assertInstanceOf(Promise::class, $promise);
        $promise->setCancelCallback(null);
    }

    /**
     * @expectedException \LogicException
     */
    public function testCanThrowExceptionWhenResolveSelf()
    {
        $promise = new Promise();
        $this->assertInstanceOf(Promise::class, $promise);
        $promise->resolve($promise);
    }

    /**
     * @expectedException \LogicException
     */
    public function testCanThrowExceptionWhenRejectingSelf()
    {
        $promise = new Promise();
        $this->assertInstanceOf(Promise::class, $promise);
        $promise->reject($promise);
    }

    public function testCanCatchExceptionWhenDoTaskCancellation()
    {
        $promise = new Promise();
        $this->assertInstanceOf(Promise::class, $promise);
        $promise->setCancelCallback(
            function () {
                throw new CatchableException('catch me if you can.');
            }
        );
        $promise->cancel();
    }

    /**
     * @expectedException \LogicException
     */
    public function testCanThrowExceptionWhenResolvingPromiseUsingDifferentValue()
    {
        $promise = new Promise();
        $this->assertInstanceOf(Promise::class, $promise);
        $promise->resolve('a');
        $promise->resolve('b');
    }

    public function testCanDoNothingWhenResolvingPromiseUsingSameValue()
    {
        $promise = new Promise();
        $this->assertInstanceOf(Promise::class, $promise);
        $promise->resolve('shit');
        $promise->resolve('shit');
    }

    public function testCanDoNothingWhenDoPromiseCancellationInNonPendingState()
    {
        $promise = new Promise();
        $this->assertInstanceOf(Promise::class, $promise);
        $promise->resolve('this is a text.');
        $promise->cancel();
    }

    public function testCanDoNothingWhenDoPromiseCancellation()
    {
        $promise = new Promise();
        $this->assertInstanceOf(Promise::class, $promise);
        $promise->cancel();
    }

    public function testCanMergeExecutionContextWhenResolvingNewImmutablePromise()
    {
        $promise = new Promise();
        $this->assertInstanceOf(Promise::class, $promise);
        $promise->resolve(new Promise());
    }

    public function testCanResolveExecutionContextWhenValueIsThennable()
    {
        $promise = new Promise();
        $q = new Promise();
        $this->assertInstanceOf(Promise::class, $promise);
        $promise
            ->then(
                function ($d) use ($q) {
                    echo sprintf("%s" . PHP_EOL, $d);

                    return $q;
                },
                null
            )
            ->then(
                function ($d) {
                    echo sprintf("%s" . PHP_EOL, $d);
                },
                null
            );
        $promise->resolve($q);
        $q->resolve('this is a shit.');
    }

    /**
     * @expectedException \LogicException
     */
    public function testCanThrowExceptionWhenRejectingPromiseUsingDifferentValue()
    {
        $promise = new Promise();
        $this->assertInstanceOf(Promise::class, $promise);
        $promise->reject('a');
        $promise->reject('b');
    }

    public function testCanDoNothingWhenRejectingPromiseUsingSameValue()
    {
        $promise = new Promise();
        $this->assertInstanceOf(Promise::class, $promise);
        $promise->reject('a');
        $promise->reject('a');
    }

    public function testCanRejectExecutionContextWhenValueIsThennable()
    {
        $promise = new Promise();
        $q = new Promise();
        $this->assertInstanceOf(Promise::class, $promise);
        $this->assertInstanceOf(Promise::class, $q);
        $promise
            ->then(
                null,
                function ($e) use ($q) {
                    echo sprintf("%s" . PHP_EOL, $e);

                    return $q;
                }
            )
            ->then(
                null,
                function ($e) {
                    echo sprintf("%s" . PHP_EOL, $e);
                }
            );
        $promise->reject($q);
        $q->reject('a');
    }
}
