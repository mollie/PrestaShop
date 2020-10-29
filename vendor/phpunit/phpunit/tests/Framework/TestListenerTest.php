<?php

namespace MolliePrefix;

/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Framework_TestListenerTest extends \MolliePrefix\PHPUnit_Framework_TestCase implements \MolliePrefix\PHPUnit_Framework_TestListener
{
    protected $endCount;
    protected $errorCount;
    protected $failureCount;
    protected $warningCount;
    protected $notImplementedCount;
    protected $riskyCount;
    protected $skippedCount;
    protected $result;
    protected $startCount;
    public function addError(\MolliePrefix\PHPUnit_Framework_Test $test, \Exception $e, $time)
    {
        $this->errorCount++;
    }
    public function addWarning(\MolliePrefix\PHPUnit_Framework_Test $test, \MolliePrefix\PHPUnit_Framework_Warning $e, $time)
    {
        $this->warningCount++;
    }
    public function addFailure(\MolliePrefix\PHPUnit_Framework_Test $test, \MolliePrefix\PHPUnit_Framework_AssertionFailedError $e, $time)
    {
        $this->failureCount++;
    }
    public function addIncompleteTest(\MolliePrefix\PHPUnit_Framework_Test $test, \Exception $e, $time)
    {
        $this->notImplementedCount++;
    }
    public function addRiskyTest(\MolliePrefix\PHPUnit_Framework_Test $test, \Exception $e, $time)
    {
        $this->riskyCount++;
    }
    public function addSkippedTest(\MolliePrefix\PHPUnit_Framework_Test $test, \Exception $e, $time)
    {
        $this->skippedCount++;
    }
    public function startTestSuite(\MolliePrefix\PHPUnit_Framework_TestSuite $suite)
    {
    }
    public function endTestSuite(\MolliePrefix\PHPUnit_Framework_TestSuite $suite)
    {
    }
    public function startTest(\MolliePrefix\PHPUnit_Framework_Test $test)
    {
        $this->startCount++;
    }
    public function endTest(\MolliePrefix\PHPUnit_Framework_Test $test, $time)
    {
        $this->endCount++;
    }
    protected function setUp()
    {
        $this->result = new \MolliePrefix\PHPUnit_Framework_TestResult();
        $this->result->addListener($this);
        $this->endCount = 0;
        $this->failureCount = 0;
        $this->notImplementedCount = 0;
        $this->riskyCount = 0;
        $this->skippedCount = 0;
        $this->startCount = 0;
    }
    public function testError()
    {
        $test = new \MolliePrefix\TestError();
        $test->run($this->result);
        $this->assertEquals(1, $this->errorCount);
        $this->assertEquals(1, $this->endCount);
    }
    public function testFailure()
    {
        $test = new \MolliePrefix\Failure();
        $test->run($this->result);
        $this->assertEquals(1, $this->failureCount);
        $this->assertEquals(1, $this->endCount);
    }
    public function testStartStop()
    {
        $test = new \MolliePrefix\Success();
        $test->run($this->result);
        $this->assertEquals(1, $this->startCount);
        $this->assertEquals(1, $this->endCount);
    }
}
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
\class_alias('MolliePrefix\\Framework_TestListenerTest', 'MolliePrefix\\Framework_TestListenerTest', \false);
