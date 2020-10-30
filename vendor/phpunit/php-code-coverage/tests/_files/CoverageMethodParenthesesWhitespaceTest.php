<?php

namespace MolliePrefix;

class CoverageMethodParenthesesWhitespaceTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    /**
     * @covers CoveredClass::publicMethod ( )
     */
    public function testSomething()
    {
        $o = new \MolliePrefix\CoveredClass();
        $o->publicMethod();
    }
}
\class_alias('MolliePrefix\\CoverageMethodParenthesesWhitespaceTest', 'MolliePrefix\\CoverageMethodParenthesesWhitespaceTest', \false);
