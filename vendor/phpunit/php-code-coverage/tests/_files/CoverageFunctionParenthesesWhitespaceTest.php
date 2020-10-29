<?php

namespace MolliePrefix;

class CoverageFunctionParenthesesWhitespaceTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    /**
     * @covers ::globalFunction ( )
     */
    public function testSomething()
    {
        \MolliePrefix\globalFunction();
    }
}
\class_alias('MolliePrefix\\CoverageFunctionParenthesesWhitespaceTest', 'MolliePrefix\\CoverageFunctionParenthesesWhitespaceTest', \false);
