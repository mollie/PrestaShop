<?php

namespace MolliePrefix;

class CoverageClassExtendedTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    /**
     * @covers CoveredClass<extended>
     */
    public function testSomething()
    {
        $o = new \MolliePrefix\CoveredClass();
        $o->publicMethod();
    }
}
\class_alias('MolliePrefix\\CoverageClassExtendedTest', 'CoverageClassExtendedTest', \false);
