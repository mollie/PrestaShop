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
 * An empty Listener that can be extended to implement TestListener
 * with just a few lines of code.
 *
 * @see PHPUnit_Framework_TestListener for documentation on the API methods.
 * @since Class available since Release 4.0.0
 */
abstract class PHPUnit_Framework_BaseTestListener implements \MolliePrefix\PHPUnit_Framework_TestListener
{
    public function addError(\MolliePrefix\PHPUnit_Framework_Test $test, \Exception $e, $time)
    {
    }
    public function addWarning(\MolliePrefix\PHPUnit_Framework_Test $test, \MolliePrefix\PHPUnit_Framework_Warning $e, $time)
    {
    }
    public function addFailure(\MolliePrefix\PHPUnit_Framework_Test $test, \MolliePrefix\PHPUnit_Framework_AssertionFailedError $e, $time)
    {
    }
    public function addIncompleteTest(\MolliePrefix\PHPUnit_Framework_Test $test, \Exception $e, $time)
    {
    }
    public function addRiskyTest(\MolliePrefix\PHPUnit_Framework_Test $test, \Exception $e, $time)
    {
    }
    public function addSkippedTest(\MolliePrefix\PHPUnit_Framework_Test $test, \Exception $e, $time)
    {
    }
    public function startTestSuite(\MolliePrefix\PHPUnit_Framework_TestSuite $suite)
    {
    }
    public function endTestSuite(\MolliePrefix\PHPUnit_Framework_TestSuite $suite)
    {
    }
    public function startTest(\MolliePrefix\PHPUnit_Framework_Test $test)
    {
    }
    public function endTest(\MolliePrefix\PHPUnit_Framework_Test $test, $time)
    {
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
 * An empty Listener that can be extended to implement TestListener
 * with just a few lines of code.
 *
 * @see PHPUnit_Framework_TestListener for documentation on the API methods.
 * @since Class available since Release 4.0.0
 */
\class_alias('MolliePrefix\\PHPUnit_Framework_BaseTestListener', 'PHPUnit_Framework_BaseTestListener', \false);
