<?php

namespace MolliePrefix;

class ExceptionStackTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    public function testPrintingChildException()
    {
        try {
            $this->assertEquals([1], [2], 'message');
        } catch (\MolliePrefix\PHPUnit_Framework_ExpectationFailedException $e) {
            $message = $e->getMessage() . $e->getComparisonFailure()->getDiff();
            throw new \MolliePrefix\PHPUnit_Framework_Exception("Child exception\n{$message}", 101, $e);
        }
    }
    public function testNestedExceptions()
    {
        $exceptionThree = new \Exception('Three');
        $exceptionTwo = new \InvalidArgumentException('Two', 0, $exceptionThree);
        $exceptionOne = new \Exception('One', 0, $exceptionTwo);
        throw $exceptionOne;
    }
}
\class_alias('MolliePrefix\\ExceptionStackTest', 'ExceptionStackTest', \false);
