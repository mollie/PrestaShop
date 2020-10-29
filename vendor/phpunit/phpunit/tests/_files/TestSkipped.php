<?php

namespace MolliePrefix;

class TestSkipped extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    protected function runTest()
    {
        $this->markTestSkipped('Skipped test');
    }
}
\class_alias('MolliePrefix\\TestSkipped', 'MolliePrefix\\TestSkipped', \false);
