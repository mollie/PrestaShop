<?php

namespace MolliePrefix;

class IgnoreCodeCoverageClassTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    public function testReturnTrue()
    {
        $sut = new \MolliePrefix\IgnoreCodeCoverageClass();
        $this->assertTrue($sut->returnTrue());
    }
    public function testReturnFalse()
    {
        $sut = new \MolliePrefix\IgnoreCodeCoverageClass();
        $this->assertFalse($sut->returnFalse());
    }
}
\class_alias('MolliePrefix\\IgnoreCodeCoverageClassTest', 'IgnoreCodeCoverageClassTest', \false);
