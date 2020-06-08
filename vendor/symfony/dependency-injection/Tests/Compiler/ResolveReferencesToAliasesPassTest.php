<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Compiler;

use _PhpScoper5eddef0da618a\PHPUnit\Framework\TestCase;
use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Alias;
use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Compiler\ResolveReferencesToAliasesPass;
use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\ContainerBuilder;
use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Definition;
use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference;
class ResolveReferencesToAliasesPassTest extends \_PhpScoper5eddef0da618a\PHPUnit\Framework\TestCase
{
    public function testProcess()
    {
        $container = new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->setAlias('bar', 'foo');
        $def = $container->register('moo')->setArguments([new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('bar')]);
        $this->process($container);
        $arguments = $def->getArguments();
        $this->assertEquals('foo', (string) $arguments[0]);
    }
    public function testProcessRecursively()
    {
        $container = new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->setAlias('bar', 'foo');
        $container->setAlias('moo', 'bar');
        $def = $container->register('foobar')->setArguments([new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('moo')]);
        $this->process($container);
        $arguments = $def->getArguments();
        $this->assertEquals('foo', (string) $arguments[0]);
    }
    public function testAliasCircularReference()
    {
        $this->expectException('_PhpScoper5eddef0da618a\\Symfony\\Component\\DependencyInjection\\Exception\\ServiceCircularReferenceException');
        $container = new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->setAlias('bar', 'foo');
        $container->setAlias('foo', 'bar');
        $this->process($container);
    }
    public function testResolveFactory()
    {
        $container = new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('factory', 'Factory');
        $container->setAlias('factory_alias', new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Alias('factory'));
        $foo = new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Definition();
        $foo->setFactory([new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('factory_alias'), 'createFoo']);
        $container->setDefinition('foo', $foo);
        $bar = new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Definition();
        $bar->setFactory(['Factory', 'createFoo']);
        $container->setDefinition('bar', $bar);
        $this->process($container);
        $resolvedFooFactory = $container->getDefinition('foo')->getFactory();
        $resolvedBarFactory = $container->getDefinition('bar')->getFactory();
        $this->assertSame('factory', (string) $resolvedFooFactory[0]);
        $this->assertSame('Factory', (string) $resolvedBarFactory[0]);
    }
    protected function process(\_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\ContainerBuilder $container)
    {
        $pass = new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Compiler\ResolveReferencesToAliasesPass();
        $pass->process($container);
    }
}
