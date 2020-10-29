<?php

namespace MolliePrefix;

/**
 * @coversDefaultClass \NamespaceOne
 * @coversDefaultClass \AnotherDefault\Name\Space\Does\Not\Work
 */
class CoverageTwoDefaultClassAnnotations
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
/**
 * @coversDefaultClass \NamespaceOne
 * @coversDefaultClass \AnotherDefault\Name\Space\Does\Not\Work
 */
\class_alias('MolliePrefix\\CoverageTwoDefaultClassAnnotations', 'MolliePrefix\\CoverageTwoDefaultClassAnnotations', \false);
