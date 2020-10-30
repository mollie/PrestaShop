<?php

namespace MolliePrefix;

class OutputTestCase extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    public function testExpectOutputStringFooActualFoo()
    {
        $this->expectOutputString('foo');
        print 'foo';
    }
    public function testExpectOutputStringFooActualBar()
    {
        $this->expectOutputString('foo');
        print 'bar';
    }
    public function testExpectOutputRegexFooActualFoo()
    {
        $this->expectOutputRegex('/foo/');
        print 'foo';
    }
    public function testExpectOutputRegexFooActualBar()
    {
        $this->expectOutputRegex('/foo/');
        print 'bar';
    }
}
\class_alias('MolliePrefix\\OutputTestCase', 'MolliePrefix\\OutputTestCase', \false);
