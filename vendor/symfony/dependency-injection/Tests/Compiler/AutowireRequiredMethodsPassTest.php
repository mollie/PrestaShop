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
use MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AutowireRequiredMethodsPass;
use MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveClassPass;
use MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder;
require_once __DIR__ . '/../Fixtures/includes/autowiring_classes.php';
class AutowireRequiredMethodsPassTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    public function testSetterInjection()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Foo::class);
        $container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\A::class);
        $container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\CollisionA::class);
        $container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\CollisionB::class);
        // manually configure *one* call, to override autowiring
        $container->register('setter_injection', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\SetterInjection::class)->setAutowired(\true)->addMethodCall('setWithCallsConfigured', ['manual_arg1', 'manual_arg2']);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveClassPass())->process($container);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AutowireRequiredMethodsPass())->process($container);
        $methodCalls = $container->getDefinition('setter_injection')->getMethodCalls();
        $this->assertEquals(['setWithCallsConfigured', 'setFoo', 'setDependencies', 'setChildMethodWithoutDocBlock'], \array_column($methodCalls, 0));
        // test setWithCallsConfigured args
        $this->assertEquals(['manual_arg1', 'manual_arg2'], $methodCalls[0][1]);
        // test setFoo args
        $this->assertEquals([], $methodCalls[1][1]);
    }
    public function testExplicitMethodInjection()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\Foo::class);
        $container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\A::class);
        $container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\CollisionA::class);
        $container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\CollisionB::class);
        $container->register('setter_injection', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\SetterInjection::class)->setAutowired(\true)->addMethodCall('notASetter', []);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveClassPass())->process($container);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AutowireRequiredMethodsPass())->process($container);
        $methodCalls = $container->getDefinition('setter_injection')->getMethodCalls();
        $this->assertEquals(['notASetter', 'setFoo', 'setDependencies', 'setWithCallsConfigured', 'setChildMethodWithoutDocBlock'], \array_column($methodCalls, 0));
        $this->assertEquals([], $methodCalls[0][1]);
    }
}
