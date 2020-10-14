<?php

namespace MolliePrefix;

class NamespaceCoverageProtectedTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    /**
     * @covers Foo\CoveredClass::<protected>
     */
    public function testSomething()
    {
        $o = new \MolliePrefix\Foo\CoveredClass();
        $o->publicMethod();
    }
}
\class_alias('MolliePrefix\\NamespaceCoverageProtectedTest', 'NamespaceCoverageProtectedTest', \false);
