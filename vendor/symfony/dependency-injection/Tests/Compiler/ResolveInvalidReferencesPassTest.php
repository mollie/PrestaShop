<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler;

use _PhpScoper5ea00cc67502b\PHPUnit\Framework\TestCase;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Compiler\ResolveInvalidReferencesPass;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerInterface;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Reference;
class ResolveInvalidReferencesPassTest extends \_PhpScoper5ea00cc67502b\PHPUnit\Framework\TestCase
{
    public function testProcess()
    {
        $container = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder();
        $def = $container->register('foo')->setArguments([new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Reference('bar', \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerInterface::NULL_ON_INVALID_REFERENCE), new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Reference('baz', \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerInterface::IGNORE_ON_INVALID_REFERENCE)])->addMethodCall('foo', [new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Reference('moo', \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerInterface::IGNORE_ON_INVALID_REFERENCE)]);
        $this->process($container);
        $arguments = $def->getArguments();
        $this->assertSame([null, null], $arguments);
        $this->assertCount(0, $def->getMethodCalls());
    }
    public function testProcessIgnoreInvalidArgumentInCollectionArgument()
    {
        $container = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('baz');
        $def = $container->register('foo')->setArguments([[new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Reference('bar', \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerInterface::IGNORE_ON_INVALID_REFERENCE), $baz = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Reference('baz', \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerInterface::IGNORE_ON_INVALID_REFERENCE), new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Reference('moo', \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerInterface::NULL_ON_INVALID_REFERENCE)]]);
        $this->process($container);
        $arguments = $def->getArguments();
        $this->assertSame([$baz, null], $arguments[0]);
    }
    public function testProcessKeepMethodCallOnInvalidArgumentInCollectionArgument()
    {
        $container = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('baz');
        $def = $container->register('foo')->addMethodCall('foo', [[new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Reference('bar', \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerInterface::IGNORE_ON_INVALID_REFERENCE), $baz = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Reference('baz', \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerInterface::IGNORE_ON_INVALID_REFERENCE), new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Reference('moo', \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerInterface::NULL_ON_INVALID_REFERENCE)]]);
        $this->process($container);
        $calls = $def->getMethodCalls();
        $this->assertCount(1, $def->getMethodCalls());
        $this->assertSame([$baz, null], $calls[0][1][0]);
    }
    public function testProcessIgnoreNonExistentServices()
    {
        $container = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder();
        $def = $container->register('foo')->setArguments([new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Reference('bar')]);
        $this->process($container);
        $arguments = $def->getArguments();
        $this->assertEquals('bar', (string) $arguments[0]);
    }
    public function testProcessRemovesPropertiesOnInvalid()
    {
        $container = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder();
        $def = $container->register('foo')->setProperty('foo', new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Reference('bar', \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerInterface::IGNORE_ON_INVALID_REFERENCE));
        $this->process($container);
        $this->assertEquals([], $def->getProperties());
    }
    public function testProcessRemovesArgumentsOnInvalid()
    {
        $container = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder();
        $def = $container->register('foo')->addArgument([[new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Reference('bar', \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerInterface::IGNORE_ON_INVALID_REFERENCE), new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument(new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Reference('baz', \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerInterface::IGNORE_ON_INVALID_REFERENCE))]]);
        $this->process($container);
        $this->assertSame([[[]]], $def->getArguments());
    }
    protected function process(\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder $container)
    {
        $pass = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Compiler\ResolveInvalidReferencesPass();
        $pass->process($container);
    }
}
