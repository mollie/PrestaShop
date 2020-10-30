<?php

namespace MolliePrefix;

class CoverageNamespacedFunctionTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    /**
     * @covers foo\func()
     */
    public function testFunc()
    {
        \MolliePrefix\foo\func();
    }
}
\class_alias('MolliePrefix\\CoverageNamespacedFunctionTest', 'MolliePrefix\\CoverageNamespacedFunctionTest', \false);
