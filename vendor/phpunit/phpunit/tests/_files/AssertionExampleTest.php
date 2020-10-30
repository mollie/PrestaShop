<?php

namespace MolliePrefix;

class AssertionExampleTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    public function testOne()
    {
        $e = new \MolliePrefix\AssertionExample();
        $e->doSomething();
    }
}
\class_alias('MolliePrefix\\AssertionExampleTest', 'MolliePrefix\\AssertionExampleTest', \false);
