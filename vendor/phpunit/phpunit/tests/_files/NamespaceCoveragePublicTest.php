<?php

namespace MolliePrefix;

class NamespaceCoveragePublicTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    /**
     * @covers Foo\CoveredClass::<public>
     */
    public function testSomething()
    {
        $o = new \MolliePrefix\Foo\CoveredClass();
        $o->publicMethod();
    }
}
\class_alias('MolliePrefix\\NamespaceCoveragePublicTest', 'MolliePrefix\\NamespaceCoveragePublicTest', \false);
