<?php

namespace MolliePrefix;

class CoverageNothingTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    /**
     * @covers CoveredClass::publicMethod
     * @coversNothing
     */
    public function testSomething()
    {
        $o = new \MolliePrefix\CoveredClass();
        $o->publicMethod();
    }
}
\class_alias('MolliePrefix\\CoverageNothingTest', 'CoverageNothingTest', \false);
