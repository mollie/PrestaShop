<?php

namespace MolliePrefix;

class CoverageNoneTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    public function testSomething()
    {
        $o = new \MolliePrefix\CoveredClass();
        $o->publicMethod();
    }
}
\class_alias('MolliePrefix\\CoverageNoneTest', 'CoverageNoneTest', \false);
