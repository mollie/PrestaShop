<?php

namespace MolliePrefix;

class TestIncomplete extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    protected function runTest()
    {
        $this->markTestIncomplete('Incomplete test');
    }
}
\class_alias('MolliePrefix\\TestIncomplete', 'MolliePrefix\\TestIncomplete', \false);
