<?php

namespace MolliePrefix\PhpParser\ErrorHandler;

use MolliePrefix\PhpParser\Error;
class ThrowingTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \PhpParser\Error
     * @expectedExceptionMessage Test
     */
    public function testHandleError()
    {
        $errorHandler = new \MolliePrefix\PhpParser\ErrorHandler\Throwing();
        $errorHandler->handleError(new \MolliePrefix\PhpParser\Error('Test'));
    }
}
