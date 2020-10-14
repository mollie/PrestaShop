<?php

namespace MolliePrefix;

/**
 * @coversDefaultClass \Foo\CoveredClass
 */
class NamespaceCoverageCoversClassPublicTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    /**
     * @covers ::publicMethod
     */
    public function testSomething()
    {
        $o = new \MolliePrefix\Foo\CoveredClass();
        $o->publicMethod();
    }
}
/**
 * @coversDefaultClass \Foo\CoveredClass
 */
\class_alias('MolliePrefix\\NamespaceCoverageCoversClassPublicTest', 'NamespaceCoverageCoversClassPublicTest', \false);
