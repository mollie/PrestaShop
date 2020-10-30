<?php

namespace MolliePrefix;

class NamespaceCoverageClassExtendedTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    /**
     * @covers Foo\CoveredClass<extended>
     */
    public function testSomething()
    {
        $o = new \MolliePrefix\Foo\CoveredClass();
        $o->publicMethod();
    }
}
\class_alias('MolliePrefix\\NamespaceCoverageClassExtendedTest', 'MolliePrefix\\NamespaceCoverageClassExtendedTest', \false);
