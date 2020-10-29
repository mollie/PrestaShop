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
 * A Decorator for Tests.
 *
 * Use TestDecorator as the base class for defining new
 * test decorators. Test decorator subclasses can be introduced
 * to add behaviour before or after a test is run.
 */
class PHPUnit_Extensions_TestDecorator extends \MolliePrefix\PHPUnit_Framework_Assert implements \MolliePrefix\PHPUnit_Framework_Test, \MolliePrefix\PHPUnit_Framework_SelfDescribing
{
    /**
     * The Test to be decorated.
     *
     * @var object
     */
    protected $test = null;
    /**
     * Constructor.
     *
     * @param PHPUnit_Framework_Test $test
     */
    public function __construct(\MolliePrefix\PHPUnit_Framework_Test $test)
    {
        $this->test = $test;
    }
    /**
     * Returns a string representation of the test.
     *
     * @return string
     */
    public function toString()
    {
        return $this->test->toString();
    }
    /**
     * Runs the test and collects the
     * result in a TestResult.
     *
     * @param PHPUnit_Framework_TestResult $result
     */
    public function basicRun(\MolliePrefix\PHPUnit_Framework_TestResult $result)
    {
        $this->test->run($result);
    }
    /**
     * Counts the number of test cases that
     * will be run by this test.
     *
     * @return int
     */
    public function count()
    {
        return \count($this->test);
    }
    /**
     * Creates a default TestResult object.
     *
     * @return PHPUnit_Framework_TestResult
     */
    protected function createResult()
    {
        return new \MolliePrefix\PHPUnit_Framework_TestResult();
    }
    /**
     * Returns the test to be run.
     *
     * @return PHPUnit_Framework_Test
     */
    public function getTest()
    {
        return $this->test;
    }
    /**
     * Runs the decorated test and collects the
     * result in a TestResult.
     *
     * @param PHPUnit_Framework_TestResult $result
     *
     * @return PHPUnit_Framework_TestResult
     */
    public function run(\MolliePrefix\PHPUnit_Framework_TestResult $result = null)
    {
        if ($result === null) {
            $result = $this->createResult();
        }
        $this->basicRun($result);
        return $result;
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
 * A Decorator for Tests.
 *
 * Use TestDecorator as the base class for defining new
 * test decorators. Test decorator subclasses can be introduced
 * to add behaviour before or after a test is run.
 */
\class_alias('MolliePrefix\\PHPUnit_Extensions_TestDecorator', 'MolliePrefix\\PHPUnit_Extensions_TestDecorator', \false);
