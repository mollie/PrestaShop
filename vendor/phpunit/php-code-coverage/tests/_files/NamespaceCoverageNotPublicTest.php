<?php

namespace MolliePrefix;

class NamespaceCoverageNotPublicTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    /**
     * @covers Foo\CoveredClass::<!public>
     */
    public function testSomething()
    {
        $o = new \MolliePrefix\Foo\CoveredClass();
        $o->publicMethod();
    }
}
\class_alias('MolliePrefix\\NamespaceCoverageNotPublicTest', 'MolliePrefix\\NamespaceCoverageNotPublicTest', \false);
