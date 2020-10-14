<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\Console\Tests\Formatter;

use MolliePrefix\PHPUnit\Framework\TestCase;
use MolliePrefix\Symfony\Component\Console\Formatter\OutputFormatterStyle;
use MolliePrefix\Symfony\Component\Console\Formatter\OutputFormatterStyleStack;
class OutputFormatterStyleStackTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    public function testPush()
    {
        $stack = new \MolliePrefix\Symfony\Component\Console\Formatter\OutputFormatterStyleStack();
        $stack->push($s1 = new \MolliePrefix\Symfony\Component\Console\Formatter\OutputFormatterStyle('white', 'black'));
        $stack->push($s2 = new \MolliePrefix\Symfony\Component\Console\Formatter\OutputFormatterStyle('yellow', 'blue'));
        $this->assertEquals($s2, $stack->getCurrent());
        $stack->push($s3 = new \MolliePrefix\Symfony\Component\Console\Formatter\OutputFormatterStyle('green', 'red'));
        $this->assertEquals($s3, $stack->getCurrent());
    }
    public function testPop()
    {
        $stack = new \MolliePrefix\Symfony\Component\Console\Formatter\OutputFormatterStyleStack();
        $stack->push($s1 = new \MolliePrefix\Symfony\Component\Console\Formatter\OutputFormatterStyle('white', 'black'));
        $stack->push($s2 = new \MolliePrefix\Symfony\Component\Console\Formatter\OutputFormatterStyle('yellow', 'blue'));
        $this->assertEquals($s2, $stack->pop());
        $this->assertEquals($s1, $stack->pop());
    }
    public function testPopEmpty()
    {
        $stack = new \MolliePrefix\Symfony\Component\Console\Formatter\OutputFormatterStyleStack();
        $style = new \MolliePrefix\Symfony\Component\Console\Formatter\OutputFormatterStyle();
        $this->assertEquals($style, $stack->pop());
    }
    public function testPopNotLast()
    {
        $stack = new \MolliePrefix\Symfony\Component\Console\Formatter\OutputFormatterStyleStack();
        $stack->push($s1 = new \MolliePrefix\Symfony\Component\Console\Formatter\OutputFormatterStyle('white', 'black'));
        $stack->push($s2 = new \MolliePrefix\Symfony\Component\Console\Formatter\OutputFormatterStyle('yellow', 'blue'));
        $stack->push($s3 = new \MolliePrefix\Symfony\Component\Console\Formatter\OutputFormatterStyle('green', 'red'));
        $this->assertEquals($s2, $stack->pop($s2));
        $this->assertEquals($s1, $stack->pop());
    }
    public function testInvalidPop()
    {
        $this->expectException('InvalidArgumentException');
        $stack = new \MolliePrefix\Symfony\Component\Console\Formatter\OutputFormatterStyleStack();
        $stack->push(new \MolliePrefix\Symfony\Component\Console\Formatter\OutputFormatterStyle('white', 'black'));
        $stack->pop(new \MolliePrefix\Symfony\Component\Console\Formatter\OutputFormatterStyle('yellow', 'blue'));
    }
}
