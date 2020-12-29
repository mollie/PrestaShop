<?php

namespace MolliePrefix;

class CoverageProtectedTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    /**
     * @covers CoveredClass::<protected>
     */
    public function testSomething()
    {
        $o = new \MolliePrefix\CoveredClass();
        $o->publicMethod();
    }
}
\class_alias('MolliePrefix\\CoverageProtectedTest', 'CoverageProtectedTest', \false);
