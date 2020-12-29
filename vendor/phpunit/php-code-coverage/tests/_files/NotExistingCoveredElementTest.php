<?php

namespace MolliePrefix;

class NotExistingCoveredElementTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    /**
     * @covers NotExistingClass
     */
    public function testOne()
    {
    }
    /**
     * @covers NotExistingClass::notExistingMethod
     */
    public function testTwo()
    {
    }
    /**
     * @covers NotExistingClass::<public>
     */
    public function testThree()
    {
    }
}
\class_alias('MolliePrefix\\NotExistingCoveredElementTest', 'NotExistingCoveredElementTest', \false);
