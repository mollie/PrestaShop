<?php

namespace MolliePrefix;

class DependencyFailureTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    public function testOne()
    {
        $this->fail();
    }
    /**
     * @depends testOne
     */
    public function testTwo()
    {
    }
    /**
     * @depends !clone testTwo
     */
    public function testThree()
    {
    }
    /**
     * @depends clone testOne
     */
    public function testFour()
    {
    }
}
\class_alias('MolliePrefix\\DependencyFailureTest', 'DependencyFailureTest', \false);
