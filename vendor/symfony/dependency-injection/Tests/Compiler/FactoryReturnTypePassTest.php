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
use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Compiler\FactoryReturnTypePass;
use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\ContainerBuilder;
use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference;
use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Fixtures\FactoryDummy;
use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Fixtures\factoryFunction;
use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Fixtures\FactoryParent;
/**
 * @author Guilhem N. <egetick@gmail.com>
 *
 * @group legacy
 */
class FactoryReturnTypePassTest extends \_PhpScoper5eddef0da618a\PHPUnit\Framework\TestCase
{
    public function testProcess()
    {
        $container = new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\ContainerBuilder();
        $factory = $container->register('factory');
        $factory->setFactory([\_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Fixtures\FactoryDummy::class, 'createFactory']);
        $container->setAlias('alias_factory', 'factory');
        $foo = $container->register('foo');
        $foo->setFactory([new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('alias_factory'), 'create']);
        $bar = $container->register('bar', __CLASS__);
        $bar->setFactory([new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('factory'), 'create']);
        $pass = new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Compiler\FactoryReturnTypePass();
        $pass->process($container);
        if (\method_exists(\ReflectionMethod::class, 'getReturnType')) {
            $this->assertEquals(\_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Fixtures\FactoryDummy::class, $factory->getClass());
            $this->assertEquals(\stdClass::class, $foo->getClass());
        } else {
            $this->assertNull($factory->getClass());
            $this->assertNull($foo->getClass());
        }
        $this->assertEquals(__CLASS__, $bar->getClass());
    }
    /**
     * @dataProvider returnTypesProvider
     */
    public function testReturnTypes($factory, $returnType, $hhvmSupport = \true)
    {
        if (!$hhvmSupport && \defined('HHVM_VERSION')) {
            $this->markTestSkipped('Scalar typehints not supported by hhvm.');
        }
        $container = new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\ContainerBuilder();
        $service = $container->register('service');
        $service->setFactory($factory);
        $pass = new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Compiler\FactoryReturnTypePass();
        $pass->process($container);
        if (\method_exists(\ReflectionMethod::class, 'getReturnType')) {
            $this->assertEquals($returnType, $service->getClass());
        } else {
            $this->assertNull($service->getClass());
        }
    }
    public function returnTypesProvider()
    {
        return [
            // must be loaded before the function as they are in the same file
            [[\_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Fixtures\FactoryDummy::class, 'createBuiltin'], null, \false],
            [[\_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Fixtures\FactoryDummy::class, 'createParent'], \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Fixtures\FactoryParent::class],
            [[\_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Fixtures\FactoryDummy::class, 'createSelf'], \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Fixtures\FactoryDummy::class],
            [\_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Fixtures\factoryFunction::class, \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Fixtures\FactoryDummy::class],
        ];
    }
    public function testCircularReference()
    {
        $container = new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\ContainerBuilder();
        $factory = $container->register('factory');
        $factory->setFactory([new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('factory2'), 'createSelf']);
        $factory2 = $container->register('factory2');
        $factory2->setFactory([new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('factory'), 'create']);
        $pass = new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Compiler\FactoryReturnTypePass();
        $pass->process($container);
        $this->assertNull($factory->getClass());
        $this->assertNull($factory2->getClass());
    }
    /**
     * @requires function ReflectionMethod::getReturnType
     * @expectedDeprecation Relying on its factory's return-type to define the class of service "factory" is deprecated since Symfony 3.3 and won't work in 4.0. Set the "class" attribute to "Symfony\Component\DependencyInjection\Tests\Fixtures\FactoryDummy" on the service definition instead.
     */
    public function testCompile()
    {
        $container = new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\ContainerBuilder();
        $factory = $container->register('factory');
        $factory->setFactory([\_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Fixtures\FactoryDummy::class, 'createFactory']);
        $container->compile();
        $this->assertEquals(\_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Fixtures\FactoryDummy::class, $container->getDefinition('factory')->getClass());
    }
}
