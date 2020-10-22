<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler;

use MolliePrefix\PHPUnit\Framework\TestCase;
use MolliePrefix\Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument;
use MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveInvalidReferencesPass;
use MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder;
use MolliePrefix\Symfony\Component\DependencyInjection\ContainerInterface;
use MolliePrefix\Symfony\Component\DependencyInjection\Reference;
class ResolveInvalidReferencesPassTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    public function testProcess()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $def = $container->register('foo')->setArguments([new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('bar', \MolliePrefix\Symfony\Component\DependencyInjection\ContainerInterface::NULL_ON_INVALID_REFERENCE), new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('baz', \MolliePrefix\Symfony\Component\DependencyInjection\ContainerInterface::IGNORE_ON_INVALID_REFERENCE)])->addMethodCall('foo', [new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('moo', \MolliePrefix\Symfony\Component\DependencyInjection\ContainerInterface::IGNORE_ON_INVALID_REFERENCE)]);
        $this->process($container);
        $arguments = $def->getArguments();
        $this->assertSame([null, null], $arguments);
        $this->assertCount(0, $def->getMethodCalls());
    }
    public function testProcessIgnoreInvalidArgumentInCollectionArgument()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('baz');
        $def = $container->register('foo')->setArguments([[new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('bar', \MolliePrefix\Symfony\Component\DependencyInjection\ContainerInterface::IGNORE_ON_INVALID_REFERENCE), $baz = new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('baz', \MolliePrefix\Symfony\Component\DependencyInjection\ContainerInterface::IGNORE_ON_INVALID_REFERENCE), new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('moo', \MolliePrefix\Symfony\Component\DependencyInjection\ContainerInterface::NULL_ON_INVALID_REFERENCE)]]);
        $this->process($container);
        $arguments = $def->getArguments();
        $this->assertSame([$baz, null], $arguments[0]);
    }
    public function testProcessKeepMethodCallOnInvalidArgumentInCollectionArgument()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('baz');
        $def = $container->register('foo')->addMethodCall('foo', [[new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('bar', \MolliePrefix\Symfony\Component\DependencyInjection\ContainerInterface::IGNORE_ON_INVALID_REFERENCE), $baz = new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('baz', \MolliePrefix\Symfony\Component\DependencyInjection\ContainerInterface::IGNORE_ON_INVALID_REFERENCE), new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('moo', \MolliePrefix\Symfony\Component\DependencyInjection\ContainerInterface::NULL_ON_INVALID_REFERENCE)]]);
        $this->process($container);
        $calls = $def->getMethodCalls();
        $this->assertCount(1, $def->getMethodCalls());
        $this->assertSame([$baz, null], $calls[0][1][0]);
    }
    public function testProcessIgnoreNonExistentServices()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $def = $container->register('foo')->setArguments([new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('bar')]);
        $this->process($container);
        $arguments = $def->getArguments();
        $this->assertEquals('bar', (string) $arguments[0]);
    }
    public function testProcessRemovesPropertiesOnInvalid()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $def = $container->register('foo')->setProperty('foo', new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('bar', \MolliePrefix\Symfony\Component\DependencyInjection\ContainerInterface::IGNORE_ON_INVALID_REFERENCE));
        $this->process($container);
        $this->assertEquals([], $def->getProperties());
    }
    public function testProcessRemovesArgumentsOnInvalid()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $def = $container->register('foo')->addArgument([[new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('bar', \MolliePrefix\Symfony\Component\DependencyInjection\ContainerInterface::IGNORE_ON_INVALID_REFERENCE), new \MolliePrefix\Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('baz', \MolliePrefix\Symfony\Component\DependencyInjection\ContainerInterface::IGNORE_ON_INVALID_REFERENCE))]]);
        $this->process($container);
        $this->assertSame([[[]]], $def->getArguments());
    }
    protected function process(\MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder $container)
    {
        $pass = new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveInvalidReferencesPass();
        $pass->process($container);
    }
}
