<?php

namespace MolliePrefix;

class NamespaceCoverageClassTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    /**
     * @covers Foo\CoveredClass
     */
    public function testSomething()
    {
        $o = new \MolliePrefix\Foo\CoveredClass();
        $o->publicMethod();
    }
}
\class_alias('MolliePrefix\\NamespaceCoverageClassTest', 'MolliePrefix\\NamespaceCoverageClassTest', \false);
