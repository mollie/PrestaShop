<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\DependencyInjection\Tests\LazyProxy\PhpDumper;

use MolliePrefix\PHPUnit\Framework\TestCase;
use MolliePrefix\Symfony\Component\DependencyInjection\Definition;
use MolliePrefix\Symfony\Component\DependencyInjection\LazyProxy\PhpDumper\NullDumper;
/**
 * Tests for {@see \Symfony\Component\DependencyInjection\LazyProxy\PhpDumper\NullDumper}.
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 */
class NullDumperTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    public function testNullDumper()
    {
        $dumper = new \MolliePrefix\Symfony\Component\DependencyInjection\LazyProxy\PhpDumper\NullDumper();
        $definition = new \MolliePrefix\Symfony\Component\DependencyInjection\Definition('stdClass');
        $this->assertFalse($dumper->isProxyCandidate($definition));
        $this->assertSame('', $dumper->getProxyFactoryCode($definition, 'foo', '(false)'));
        $this->assertSame('', $dumper->getProxyCode($definition));
    }
}
