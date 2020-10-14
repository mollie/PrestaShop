<?php

namespace MolliePrefix;

class MultiDependencyTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    public function testOne()
    {
        return 'foo';
    }
    public function testTwo()
    {
        return 'bar';
    }
    /**
     * @depends testOne
     * @depends testTwo
     */
    public function testThree($a, $b)
    {
        $this->assertEquals('foo', $a);
        $this->assertEquals('bar', $b);
    }
}
\class_alias('MolliePrefix\\MultiDependencyTest', 'MultiDependencyTest', \false);
