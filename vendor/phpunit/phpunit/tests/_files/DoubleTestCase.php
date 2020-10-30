<?php

namespace MolliePrefix;

class DoubleTestCase implements \MolliePrefix\PHPUnit_Framework_Test
{
    protected $testCase;
    public function __construct(\MolliePrefix\PHPUnit_Framework_TestCase $testCase)
    {
        $this->testCase = $testCase;
    }
    public function count()
    {
        return 2;
    }
    public function run(\MolliePrefix\PHPUnit_Framework_TestResult $result = null)
    {
        $result->startTest($this);
        $this->testCase->runBare();
        $this->testCase->runBare();
        $result->endTest($this, 0);
    }
}
\class_alias('MolliePrefix\\DoubleTestCase', 'MolliePrefix\\DoubleTestCase', \false);
