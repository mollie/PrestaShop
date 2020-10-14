<?php

namespace MolliePrefix;

class Issue433Test extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    public function testOutputWithExpectationBefore()
    {
        $this->expectOutputString('test');
        print 'test';
    }
    public function testOutputWithExpectationAfter()
    {
        print 'test';
        $this->expectOutputString('test');
    }
    public function testNotMatchingOutput()
    {
        print 'bar';
        $this->expectOutputString('foo');
    }
}
\class_alias('MolliePrefix\\Issue433Test', 'Issue433Test', \false);
