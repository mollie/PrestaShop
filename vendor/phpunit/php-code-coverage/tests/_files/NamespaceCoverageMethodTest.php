<?php

namespace MolliePrefix;

class NamespaceCoverageMethodTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    /**
     * @covers Foo\CoveredClass::publicMethod
     */
    public function testSomething()
    {
        $o = new \MolliePrefix\Foo\CoveredClass();
        $o->publicMethod();
    }
}
\class_alias('MolliePrefix\\NamespaceCoverageMethodTest', 'MolliePrefix\\NamespaceCoverageMethodTest', \false);
