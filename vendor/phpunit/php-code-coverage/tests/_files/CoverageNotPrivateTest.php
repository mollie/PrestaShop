<?php

namespace MolliePrefix;

class CoverageNotPrivateTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    /**
     * @covers CoveredClass::<!private>
     */
    public function testSomething()
    {
        $o = new \MolliePrefix\CoveredClass();
        $o->publicMethod();
    }
}
\class_alias('MolliePrefix\\CoverageNotPrivateTest', 'MolliePrefix\\CoverageNotPrivateTest', \false);
