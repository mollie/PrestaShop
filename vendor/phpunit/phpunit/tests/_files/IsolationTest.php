<?php

namespace MolliePrefix;

class IsolationTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    public function testIsInIsolationReturnsFalse()
    {
        $this->assertFalse($this->isInIsolation());
    }
    public function testIsInIsolationReturnsTrue()
    {
        $this->assertTrue($this->isInIsolation());
    }
}
\class_alias('MolliePrefix\\IsolationTest', 'IsolationTest', \false);
