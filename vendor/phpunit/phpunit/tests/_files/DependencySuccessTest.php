<?php

namespace MolliePrefix;

class DependencySuccessTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    public function testOne()
    {
    }
    /**
     * @depends testOne
     */
    public function testTwo()
    {
    }
    /**
     * @depends DependencySuccessTest::testTwo
     */
    public function testThree()
    {
    }
}
\class_alias('MolliePrefix\\DependencySuccessTest', 'MolliePrefix\\DependencySuccessTest', \false);
