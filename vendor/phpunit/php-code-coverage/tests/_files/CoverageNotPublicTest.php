<?php

namespace MolliePrefix;

class CoverageNotPublicTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    /**
     * @covers CoveredClass::<!public>
     */
    public function testSomething()
    {
        $o = new \MolliePrefix\CoveredClass();
        $o->publicMethod();
    }
}
\class_alias('MolliePrefix\\CoverageNotPublicTest', 'CoverageNotPublicTest', \false);
