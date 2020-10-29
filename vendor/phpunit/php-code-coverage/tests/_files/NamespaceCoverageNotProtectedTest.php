<?php

namespace MolliePrefix;

class NamespaceCoverageNotProtectedTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    /**
     * @covers Foo\CoveredClass::<!protected>
     */
    public function testSomething()
    {
        $o = new \MolliePrefix\Foo\CoveredClass();
        $o->publicMethod();
    }
}
\class_alias('MolliePrefix\\NamespaceCoverageNotProtectedTest', 'MolliePrefix\\NamespaceCoverageNotProtectedTest', \false);
