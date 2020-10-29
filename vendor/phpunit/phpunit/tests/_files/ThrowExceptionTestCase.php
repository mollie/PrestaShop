<?php

namespace MolliePrefix;

class ThrowExceptionTestCase extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    public function test()
    {
        throw new \RuntimeException('A runtime error occurred');
    }
}
\class_alias('MolliePrefix\\ThrowExceptionTestCase', 'MolliePrefix\\ThrowExceptionTestCase', \false);
