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
class ResolveInvalidReferencesPassTest extends TestCase
{
    public function testProcess()
    {
        $container = new ContainerBuilder();
        $def = $container->register('foo')->setArguments([new Reference('bar', ContainerInterface::NULL_ON_INVALID_REFERENCE), new Reference('baz', ContainerInterface::IGNORE_ON_INVALID_REFERENCE)])->addMethodCall('foo', [new Reference('moo', ContainerInterface::IGNORE_ON_INVALID_REFERENCE)]);
        $this->process($container);
        $arguments = $def->getArguments();
        $this->assertSame([null, null], $arguments);
        $this->assertCount(0, $def->getMethodCalls());
    }
    public function testProcessIgnoreInvalidArgumentInCollectionArgument()
    {
        $container = new ContainerBuilder();
        $container->register('baz');
        $def = $container->register('foo')->setArguments([[new Reference('bar', ContainerInterface::IGNORE_ON_INVALID_REFERENCE), $baz = new Reference('baz', ContainerInterface::IGNORE_ON_INVALID_REFERENCE), new Reference('moo', ContainerInterface::NULL_ON_INVALID_REFERENCE)]]);
        $this->process($container);
        $arguments = $def->getArguments();
        $this->assertSame([$baz, null], $arguments[0]);
    }
    public function testProcessKeepMethodCallOnInvalidArgumentInCollectionArgument()
    {
        $container = new ContainerBuilder();
        $container->register('baz');
        $def = $container->register('foo')->addMethodCall('foo', [[new Reference('bar', ContainerInterface::IGNORE_ON_INVALID_REFERENCE), $baz = new Reference('baz', ContainerInterface::IGNORE_ON_INVALID_REFERENCE), new Reference('moo', ContainerInterface::NULL_ON_INVALID_REFERENCE)]]);
        $this->process($container);
        $calls = $def->getMethodCalls();
        $this->assertCount(1, $def->getMethodCalls());
        $this->assertSame([$baz, null], $calls[0][1][0]);
    }
    public function testProcessIgnoreNonExistentServices()
    {
        $container = new ContainerBuilder();
        $def = $container->register('foo')->setArguments([new Reference('bar')]);
        $this->process($container);
        $arguments = $def->getArguments();
        $this->assertEquals('bar', (string) $arguments[0]);
    }
    public function testProcessRemovesPropertiesOnInvalid()
    {
        $container = new ContainerBuilder();
        $def = $container->register('foo')->setProperty('foo', new Reference('bar', ContainerInterface::IGNORE_ON_INVALID_REFERENCE));
        $this->process($container);
        $this->assertEquals([], $def->getProperties());
    }
    public function testProcessRemovesArgumentsOnInvalid()
    {
        $container = new ContainerBuilder();
        $def = $container->register('foo')->addArgument([[new Reference('bar', ContainerInterface::IGNORE_ON_INVALID_REFERENCE), new ServiceClosureArgument(new Reference('baz', ContainerInterface::IGNORE_ON_INVALID_REFERENCE))]]);
        $this->process($container);
        $this->assertSame([[[]]], $def->getArguments());
    }
    protected function process(ContainerBuilder $container)
    {
        $pass = new ResolveInvalidReferencesPass();
        $pass->process($container);
    }
}
