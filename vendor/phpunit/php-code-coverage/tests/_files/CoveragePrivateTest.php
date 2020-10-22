<?php

namespace MolliePrefix;

class CoveragePrivateTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    /**
     * @covers CoveredClass::<private>
     */
    public function testSomething()
    {
        $o = new \MolliePrefix\CoveredClass();
        $o->publicMethod();
    }
}
\class_alias('MolliePrefix\\CoveragePrivateTest', 'CoveragePrivateTest', \false);
