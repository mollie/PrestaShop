<?php

namespace MolliePrefix;

class WasRun extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    public $wasRun = \false;
    protected function runTest()
    {
        $this->wasRun = \true;
    }
}
\class_alias('MolliePrefix\\WasRun', 'MolliePrefix\\WasRun', \false);
