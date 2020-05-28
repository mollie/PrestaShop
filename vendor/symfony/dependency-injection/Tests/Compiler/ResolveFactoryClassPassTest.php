<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Tests\Compiler;

use _PhpScoper5ece82d7231e4\PHPUnit\Framework\TestCase;
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Compiler\ResolveFactoryClassPass;
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ContainerBuilder;
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Definition;
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Reference;
class ResolveFactoryClassPassTest extends \_PhpScoper5ece82d7231e4\PHPUnit\Framework\TestCase
{
    public function testProcess()
    {
        $container = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ContainerBuilder();
        $factory = $container->register('factory', '_PhpScoper5ece82d7231e4\\Foo\\Bar');
        $factory->setFactory([null, 'create']);
        $pass = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Compiler\ResolveFactoryClassPass();
        $pass->process($container);
        $this->assertSame(['_PhpScoper5ece82d7231e4\\Foo\\Bar', 'create'], $factory->getFactory());
    }
    public function testInlinedDefinitionFactoryIsProcessed()
    {
        $container = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ContainerBuilder();
        $factory = $container->register('factory');
        $factory->setFactory([(new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Definition('_PhpScoper5ece82d7231e4\\Baz\\Qux'))->setFactory([null, 'getInstance']), 'create']);
        $pass = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Compiler\ResolveFactoryClassPass();
        $pass->process($container);
        $this->assertSame(['_PhpScoper5ece82d7231e4\\Baz\\Qux', 'getInstance'], $factory->getFactory()[0]->getFactory());
    }
    public function provideFulfilledFactories()
    {
        return [[['_PhpScoper5ece82d7231e4\\Foo\\Bar', 'create']], [[new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Reference('foo'), 'create']], [[new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Definition('Baz'), 'create']]];
    }
    /**
     * @dataProvider provideFulfilledFactories
     */
    public function testIgnoresFulfilledFactories($factory)
    {
        $container = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ContainerBuilder();
        $definition = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Definition();
        $definition->setFactory($factory);
        $container->setDefinition('factory', $definition);
        $pass = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Compiler\ResolveFactoryClassPass();
        $pass->process($container);
        $this->assertSame($factory, $container->getDefinition('factory')->getFactory());
    }
    public function testNotAnyClassThrowsException()
    {
        $this->expectException('_PhpScoper5ece82d7231e4\\Symfony\\Component\\DependencyInjection\\Exception\\RuntimeException');
        $this->expectExceptionMessage('The "factory" service is defined to be created by a factory, but is missing the factory class. Did you forget to define the factory or service class?');
        $container = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ContainerBuilder();
        $factory = $container->register('factory');
        $factory->setFactory([null, 'create']);
        $pass = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Compiler\ResolveFactoryClassPass();
        $pass->process($container);
    }
}
