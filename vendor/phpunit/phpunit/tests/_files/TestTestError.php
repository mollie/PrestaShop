<?php

namespace MolliePrefix;

class TestError extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    protected function runTest()
    {
        throw new \Exception();
    }
}
\class_alias('MolliePrefix\\TestError', 'MolliePrefix\\TestError', \false);
