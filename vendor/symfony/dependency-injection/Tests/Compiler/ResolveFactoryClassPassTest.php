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
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Compiler\ResolveFactoryClassPass;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Definition;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Reference;
class ResolveFactoryClassPassTest extends TestCase
{
    public function testProcess()
    {
        $container = new ContainerBuilder();
        $factory = $container->register('factory', '_PhpScoper5ea00cc67502b\\Foo\\Bar');
        $factory->setFactory([null, 'create']);
        $pass = new ResolveFactoryClassPass();
        $pass->process($container);
        $this->assertSame(['_PhpScoper5ea00cc67502b\\Foo\\Bar', 'create'], $factory->getFactory());
    }
    public function testInlinedDefinitionFactoryIsProcessed()
    {
        $container = new ContainerBuilder();
        $factory = $container->register('factory');
        $factory->setFactory([(new Definition('_PhpScoper5ea00cc67502b\\Baz\\Qux'))->setFactory([null, 'getInstance']), 'create']);
        $pass = new ResolveFactoryClassPass();
        $pass->process($container);
        $this->assertSame(['_PhpScoper5ea00cc67502b\\Baz\\Qux', 'getInstance'], $factory->getFactory()[0]->getFactory());
    }
    public function provideFulfilledFactories()
    {
        return [[['_PhpScoper5ea00cc67502b\\Foo\\Bar', 'create']], [[new Reference('foo'), 'create']], [[new Definition('Baz'), 'create']]];
    }
    /**
     * @dataProvider provideFulfilledFactories
     */
    public function testIgnoresFulfilledFactories($factory)
    {
        $container = new ContainerBuilder();
        $definition = new Definition();
        $definition->setFactory($factory);
        $container->setDefinition('factory', $definition);
        $pass = new ResolveFactoryClassPass();
        $pass->process($container);
        $this->assertSame($factory, $container->getDefinition('factory')->getFactory());
    }
    public function testNotAnyClassThrowsException()
    {
        $this->expectException('_PhpScoper5ea00cc67502b\\Symfony\\Component\\DependencyInjection\\Exception\\RuntimeException');
        $this->expectExceptionMessage('The "factory" service is defined to be created by a factory, but is missing the factory class. Did you forget to define the factory or service class?');
        $container = new ContainerBuilder();
        $factory = $container->register('factory');
        $factory->setFactory([null, 'create']);
        $pass = new ResolveFactoryClassPass();
        $pass->process($container);
    }
}
