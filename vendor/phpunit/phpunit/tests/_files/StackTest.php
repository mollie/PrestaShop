<?php

namespace MolliePrefix;

class StackTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    public function testPush()
    {
        $stack = [];
        $this->assertCount(0, $stack);
        \array_push($stack, 'foo');
        $this->assertEquals('foo', $stack[\count($stack) - 1]);
        $this->assertCount(1, $stack);
        return $stack;
    }
    /**
     * @depends testPush
     */
    public function testPop(array $stack)
    {
        $this->assertEquals('foo', \array_pop($stack));
        $this->assertCount(0, $stack);
    }
}
\class_alias('MolliePrefix\\StackTest', 'MolliePrefix\\StackTest', \false);
