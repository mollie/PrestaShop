<?php

namespace MolliePrefix\PhpParser\ErrorHandler;

use MolliePrefix\PhpParser\Error;
class CollectingTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    public function testHandleError()
    {
        $errorHandler = new \MolliePrefix\PhpParser\ErrorHandler\Collecting();
        $this->assertFalse($errorHandler->hasErrors());
        $this->assertEmpty($errorHandler->getErrors());
        $errorHandler->handleError($e1 = new \MolliePrefix\PhpParser\Error('Test 1'));
        $errorHandler->handleError($e2 = new \MolliePrefix\PhpParser\Error('Test 2'));
        $this->assertTrue($errorHandler->hasErrors());
        $this->assertSame([$e1, $e2], $errorHandler->getErrors());
        $errorHandler->clearErrors();
        $this->assertFalse($errorHandler->hasErrors());
        $this->assertEmpty($errorHandler->getErrors());
    }
}
