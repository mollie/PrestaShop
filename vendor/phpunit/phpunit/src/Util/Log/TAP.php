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
/**
 * A TestListener that generates a logfile of the
 * test execution using the Test Anything Protocol (TAP).
 */
class PHPUnit_Util_Log_TAP extends \MolliePrefix\PHPUnit_Util_Printer implements \MolliePrefix\PHPUnit_Framework_TestListener
{
    /**
     * @var int
     */
    protected $testNumber = 0;
    /**
     * @var int
     */
    protected $testSuiteLevel = 0;
    /**
     * @var bool
     */
    protected $testSuccessful = \true;
    /**
     * Constructor.
     *
     * @param mixed $out
     *
     * @throws PHPUnit_Framework_Exception
     */
    public function __construct($out = null)
    {
        parent::__construct($out);
        $this->write("TAP version 13\n");
    }
    /**
     * An error occurred.
     *
     * @param PHPUnit_Framework_Test $test
     * @param Exception              $e
     * @param float                  $time
     */
    public function addError(\MolliePrefix\PHPUnit_Framework_Test $test, \Exception $e, $time)
    {
        $this->writeNotOk($test, 'Error');
    }
    /**
     * A warning occurred.
     *
     * @param PHPUnit_Framework_Test    $test
     * @param PHPUnit_Framework_Warning $e
     * @param float                     $time
     */
    public function addWarning(\MolliePrefix\PHPUnit_Framework_Test $test, \MolliePrefix\PHPUnit_Framework_Warning $e, $time)
    {
        $this->writeNotOk($test, 'Warning');
    }
    /**
     * A failure occurred.
     *
     * @param PHPUnit_Framework_Test                 $test
     * @param PHPUnit_Framework_AssertionFailedError $e
     * @param float                                  $time
     */
    public function addFailure(\MolliePrefix\PHPUnit_Framework_Test $test, \MolliePrefix\PHPUnit_Framework_AssertionFailedError $e, $time)
    {
        $this->writeNotOk($test, 'Failure');
        $message = \explode("\n", \MolliePrefix\PHPUnit_Framework_TestFailure::exceptionToString($e));
        $diagnostic = ['message' => $message[0], 'severity' => 'fail'];
        if ($e instanceof \MolliePrefix\PHPUnit_Framework_ExpectationFailedException) {
            $cf = $e->getComparisonFailure();
            if ($cf !== null) {
                $diagnostic['data'] = ['got' => $cf->getActual(), 'expected' => $cf->getExpected()];
            }
        }
        $yaml = new \MolliePrefix\Symfony\Component\Yaml\Dumper();
        $this->write(\sprintf("  ---\n%s  ...\n", $yaml->dump($diagnostic, 2, 2)));
    }
    /**
     * Incomplete test.
     *
     * @param PHPUnit_Framework_Test $test
     * @param Exception              $e
     * @param float                  $time
     */
    public function addIncompleteTest(\MolliePrefix\PHPUnit_Framework_Test $test, \Exception $e, $time)
    {
        $this->writeNotOk($test, '', 'TODO Incomplete Test');
    }
    /**
     * Risky test.
     *
     * @param PHPUnit_Framework_Test $test
     * @param Exception              $e
     * @param float                  $time
     */
    public function addRiskyTest(\MolliePrefix\PHPUnit_Framework_Test $test, \Exception $e, $time)
    {
        $this->write(\sprintf("ok %d - # RISKY%s\n", $this->testNumber, $e->getMessage() != '' ? ' ' . $e->getMessage() : ''));
        $this->testSuccessful = \false;
    }
    /**
     * Skipped test.
     *
     * @param PHPUnit_Framework_Test $test
     * @param Exception              $e
     * @param float                  $time
     */
    public function addSkippedTest(\MolliePrefix\PHPUnit_Framework_Test $test, \Exception $e, $time)
    {
        $this->write(\sprintf("ok %d - # SKIP%s\n", $this->testNumber, $e->getMessage() != '' ? ' ' . $e->getMessage() : ''));
        $this->testSuccessful = \false;
    }
    /**
     * A testsuite started.
     *
     * @param PHPUnit_Framework_TestSuite $suite
     */
    public function startTestSuite(\MolliePrefix\PHPUnit_Framework_TestSuite $suite)
    {
        $this->testSuiteLevel++;
    }
    /**
     * A testsuite ended.
     *
     * @param PHPUnit_Framework_TestSuite $suite
     */
    public function endTestSuite(\MolliePrefix\PHPUnit_Framework_TestSuite $suite)
    {
        $this->testSuiteLevel--;
        if ($this->testSuiteLevel == 0) {
            $this->write(\sprintf("1..%d\n", $this->testNumber));
        }
    }
    /**
     * A test started.
     *
     * @param PHPUnit_Framework_Test $test
     */
    public function startTest(\MolliePrefix\PHPUnit_Framework_Test $test)
    {
        $this->testNumber++;
        $this->testSuccessful = \true;
    }
    /**
     * A test ended.
     *
     * @param PHPUnit_Framework_Test $test
     * @param float                  $time
     */
    public function endTest(\MolliePrefix\PHPUnit_Framework_Test $test, $time)
    {
        if ($this->testSuccessful === \true) {
            $this->write(\sprintf("ok %d - %s\n", $this->testNumber, \MolliePrefix\PHPUnit_Util_Test::describe($test)));
        }
        $this->writeDiagnostics($test);
    }
    /**
     * @param PHPUnit_Framework_Test $test
     * @param string                 $prefix
     * @param string                 $directive
     */
    protected function writeNotOk(\MolliePrefix\PHPUnit_Framework_Test $test, $prefix = '', $directive = '')
    {
        $this->write(\sprintf("not ok %d - %s%s%s\n", $this->testNumber, $prefix != '' ? $prefix . ': ' : '', \MolliePrefix\PHPUnit_Util_Test::describe($test), $directive != '' ? ' # ' . $directive : ''));
        $this->testSuccessful = \false;
    }
    /**
     * @param PHPUnit_Framework_Test $test
     */
    private function writeDiagnostics(\MolliePrefix\PHPUnit_Framework_Test $test)
    {
        if (!$test instanceof \MolliePrefix\PHPUnit_Framework_TestCase) {
            return;
        }
        if (!$test->hasOutput()) {
            return;
        }
        foreach (\explode("\n", \trim($test->getActualOutput())) as $line) {
            $this->write(\sprintf("# %s\n", $line));
        }
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
/**
 * A TestListener that generates a logfile of the
 * test execution using the Test Anything Protocol (TAP).
 */
\class_alias('MolliePrefix\\PHPUnit_Util_Log_TAP', 'MolliePrefix\\PHPUnit_Util_Log_TAP', \false);
