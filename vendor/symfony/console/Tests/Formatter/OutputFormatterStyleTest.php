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
class OutputFormatterStyleTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    public function testConstructor()
    {
        $style = new \MolliePrefix\Symfony\Component\Console\Formatter\OutputFormatterStyle('green', 'black', ['bold', 'underscore']);
        $this->assertEquals("\33[32;40;1;4mfoo\33[39;49;22;24m", $style->apply('foo'));
        $style = new \MolliePrefix\Symfony\Component\Console\Formatter\OutputFormatterStyle('red', null, ['blink']);
        $this->assertEquals("\33[31;5mfoo\33[39;25m", $style->apply('foo'));
        $style = new \MolliePrefix\Symfony\Component\Console\Formatter\OutputFormatterStyle(null, 'white');
        $this->assertEquals("\33[47mfoo\33[49m", $style->apply('foo'));
    }
    public function testForeground()
    {
        $style = new \MolliePrefix\Symfony\Component\Console\Formatter\OutputFormatterStyle();
        $style->setForeground('black');
        $this->assertEquals("\33[30mfoo\33[39m", $style->apply('foo'));
        $style->setForeground('blue');
        $this->assertEquals("\33[34mfoo\33[39m", $style->apply('foo'));
        $style->setForeground('default');
        $this->assertEquals("\33[39mfoo\33[39m", $style->apply('foo'));
        $this->expectException('InvalidArgumentException');
        $style->setForeground('undefined-color');
    }
    public function testBackground()
    {
        $style = new \MolliePrefix\Symfony\Component\Console\Formatter\OutputFormatterStyle();
        $style->setBackground('black');
        $this->assertEquals("\33[40mfoo\33[49m", $style->apply('foo'));
        $style->setBackground('yellow');
        $this->assertEquals("\33[43mfoo\33[49m", $style->apply('foo'));
        $style->setBackground('default');
        $this->assertEquals("\33[49mfoo\33[49m", $style->apply('foo'));
        $this->expectException('InvalidArgumentException');
        $style->setBackground('undefined-color');
    }
    public function testOptions()
    {
        $style = new \MolliePrefix\Symfony\Component\Console\Formatter\OutputFormatterStyle();
        $style->setOptions(['reverse', 'conceal']);
        $this->assertEquals("\33[7;8mfoo\33[27;28m", $style->apply('foo'));
        $style->setOption('bold');
        $this->assertEquals("\33[7;8;1mfoo\33[27;28;22m", $style->apply('foo'));
        $style->unsetOption('reverse');
        $this->assertEquals("\33[8;1mfoo\33[28;22m", $style->apply('foo'));
        $style->setOption('bold');
        $this->assertEquals("\33[8;1mfoo\33[28;22m", $style->apply('foo'));
        $style->setOptions(['bold']);
        $this->assertEquals("\33[1mfoo\33[22m", $style->apply('foo'));
        try {
            $style->setOption('foo');
            $this->fail('->setOption() throws an \\InvalidArgumentException when the option does not exist in the available options');
        } catch (\Exception $e) {
            $this->assertInstanceOf('\\InvalidArgumentException', $e, '->setOption() throws an \\InvalidArgumentException when the option does not exist in the available options');
            $this->assertStringContainsString('Invalid option specified: "foo"', $e->getMessage(), '->setOption() throws an \\InvalidArgumentException when the option does not exist in the available options');
        }
        try {
            $style->unsetOption('foo');
            $this->fail('->unsetOption() throws an \\InvalidArgumentException when the option does not exist in the available options');
        } catch (\Exception $e) {
            $this->assertInstanceOf('\\InvalidArgumentException', $e, '->unsetOption() throws an \\InvalidArgumentException when the option does not exist in the available options');
            $this->assertStringContainsString('Invalid option specified: "foo"', $e->getMessage(), '->unsetOption() throws an \\InvalidArgumentException when the option does not exist in the available options');
        }
    }
}
