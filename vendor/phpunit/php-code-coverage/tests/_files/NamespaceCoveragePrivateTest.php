<?php

namespace MolliePrefix;

class NamespaceCoveragePrivateTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    /**
     * @covers Foo\CoveredClass::<private>
     */
    public function testSomething()
    {
        $o = new \MolliePrefix\Foo\CoveredClass();
        $o->publicMethod();
    }
}
\class_alias('MolliePrefix\\NamespaceCoveragePrivateTest', 'MolliePrefix\\NamespaceCoveragePrivateTest', \false);
