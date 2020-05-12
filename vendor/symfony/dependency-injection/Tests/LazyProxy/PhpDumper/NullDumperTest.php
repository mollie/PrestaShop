<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\LazyProxy\PhpDumper;

use _PhpScoper5ea00cc67502b\PHPUnit\Framework\TestCase;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Definition;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\LazyProxy\PhpDumper\NullDumper;
/**
 * Tests for {@see \Symfony\Component\DependencyInjection\LazyProxy\PhpDumper\NullDumper}.
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 */
class NullDumperTest extends \_PhpScoper5ea00cc67502b\PHPUnit\Framework\TestCase
{
    public function testNullDumper()
    {
        $dumper = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\LazyProxy\PhpDumper\NullDumper();
        $definition = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Definition('stdClass');
        $this->assertFalse($dumper->isProxyCandidate($definition));
        $this->assertSame('', $dumper->getProxyFactoryCode($definition, 'foo', '(false)'));
        $this->assertSame('', $dumper->getProxyCode($definition));
    }
}
