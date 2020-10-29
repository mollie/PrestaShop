<?php

namespace MolliePrefix;

class CoveragePublicTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    /**
     * @covers CoveredClass::<public>
     */
    public function testSomething()
    {
        $o = new \MolliePrefix\CoveredClass();
        $o->publicMethod();
    }
}
\class_alias('MolliePrefix\\CoveragePublicTest', 'MolliePrefix\\CoveragePublicTest', \false);
