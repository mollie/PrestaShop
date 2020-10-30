<?php

namespace MolliePrefix;

class CoverageFunctionParenthesesTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    /**
     * @covers ::globalFunction()
     */
    public function testSomething()
    {
        \MolliePrefix\globalFunction();
    }
}
\class_alias('MolliePrefix\\CoverageFunctionParenthesesTest', 'MolliePrefix\\CoverageFunctionParenthesesTest', \false);
