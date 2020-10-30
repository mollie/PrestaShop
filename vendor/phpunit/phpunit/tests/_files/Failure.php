<?php

namespace MolliePrefix;

class Failure extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    protected function runTest()
    {
        $this->fail();
    }
}
\class_alias('MolliePrefix\\Failure', 'MolliePrefix\\Failure', \false);
