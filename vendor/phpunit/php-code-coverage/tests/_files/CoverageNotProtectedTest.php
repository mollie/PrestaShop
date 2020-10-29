<?php

namespace MolliePrefix;

class CoverageNotProtectedTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    /**
     * @covers CoveredClass::<!protected>
     */
    public function testSomething()
    {
        $o = new \MolliePrefix\CoveredClass();
        $o->publicMethod();
    }
}
\class_alias('MolliePrefix\\CoverageNotProtectedTest', 'MolliePrefix\\CoverageNotProtectedTest', \false);
