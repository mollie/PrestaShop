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
use MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveFactoryClassPass;
use MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder;
use MolliePrefix\Symfony\Component\DependencyInjection\Definition;
use MolliePrefix\Symfony\Component\DependencyInjection\Reference;
class ResolveFactoryClassPassTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    public function testProcess()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $factory = $container->register('factory', 'MolliePrefix\\Foo\\Bar');
        $factory->setFactory([null, 'create']);
        $pass = new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveFactoryClassPass();
        $pass->process($container);
        $this->assertSame(['MolliePrefix\\Foo\\Bar', 'create'], $factory->getFactory());
    }
    public function testInlinedDefinitionFactoryIsProcessed()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $factory = $container->register('factory');
        $factory->setFactory([(new \MolliePrefix\Symfony\Component\DependencyInjection\Definition('MolliePrefix\\Baz\\Qux'))->setFactory([null, 'getInstance']), 'create']);
        $pass = new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveFactoryClassPass();
        $pass->process($container);
        $this->assertSame(['MolliePrefix\\Baz\\Qux', 'getInstance'], $factory->getFactory()[0]->getFactory());
    }
    public function provideFulfilledFactories()
    {
        return [[['MolliePrefix\\Foo\\Bar', 'create']], [[new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('foo'), 'create']], [[new \MolliePrefix\Symfony\Component\DependencyInjection\Definition('Baz'), 'create']]];
    }
    /**
     * @dataProvider provideFulfilledFactories
     */
    public function testIgnoresFulfilledFactories($factory)
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $definition = new \MolliePrefix\Symfony\Component\DependencyInjection\Definition();
        $definition->setFactory($factory);
        $container->setDefinition('factory', $definition);
        $pass = new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveFactoryClassPass();
        $pass->process($container);
        $this->assertSame($factory, $container->getDefinition('factory')->getFactory());
    }
    public function testNotAnyClassThrowsException()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Exception\\RuntimeException');
        $this->expectExceptionMessage('The "factory" service is defined to be created by a factory, but is missing the factory class. Did you forget to define the factory or service class?');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $factory = $container->register('factory');
        $factory->setFactory([null, 'create']);
        $pass = new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveFactoryClassPass();
        $pass->process($container);
    }
}
