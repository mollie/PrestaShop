<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\Yaml\Tests;

use MolliePrefix\PHPUnit\Framework\TestCase;
use MolliePrefix\Symfony\Component\Yaml\Yaml;
class YamlTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    public function testParseAndDump()
    {
        $data = ['lorem' => 'ipsum', 'dolor' => 'sit'];
        $yml = \MolliePrefix\Symfony\Component\Yaml\Yaml::dump($data);
        $parsed = \MolliePrefix\Symfony\Component\Yaml\Yaml::parse($yml);
        $this->assertEquals($data, $parsed);
    }
    public function testZeroIndentationThrowsException()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('The indentation must be greater than zero');
        \MolliePrefix\Symfony\Component\Yaml\Yaml::dump(['lorem' => 'ipsum', 'dolor' => 'sit'], 2, 0);
    }
    public function testNegativeIndentationThrowsException()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('The indentation must be greater than zero');
        \MolliePrefix\Symfony\Component\Yaml\Yaml::dump(['lorem' => 'ipsum', 'dolor' => 'sit'], 2, -4);
    }
}
