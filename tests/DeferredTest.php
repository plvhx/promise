<?php

namespace Gandung\Promise\Tests;

use Gandung\Promise\Deferred;
use Gandung\Promise\Promise;

class DeferredTest extends \PHPUnit_Framework_TestCase
{
	public function testCanReturnAnInstance()
	{
		$deferred = new Deferred();
		$this->assertInstanceOf(Deferred::class, $deferred);
	}

	public function testCanReturnImmutablePromiseObject()
	{
		$deferred = new Deferred();
		$this->assertInstanceOf(Promise::class, $deferred->promise());
	}

	public function testCanResolveDeferredPromise()
	{
		$deferred = new Deferred();
		$deferred->resolve('success.');
		$deferred->promise()->then(
			function($d) {
				echo sprintf("%s" . PHP_EOL, $d);
			},
			function($e) {
				echo sprintf("%s" . PHP_EOL, $e);
			}
		);
	}

	public function testCanRejectDeferredPromise()
	{
		$deferred = new Deferred();
		$deferred->reject('fail.');
		$deferred->promise()->then(
			function($d) {
				echo sprintf("%s" . PHP_EOL, $d);
			},
			function($e) {
				echo sprintf("%s" . PHP_EOL, $e);
			}
		);
	}
}