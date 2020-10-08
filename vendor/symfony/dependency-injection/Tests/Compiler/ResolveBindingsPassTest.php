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
use MolliePrefix\Symfony\Component\DependencyInjection\Argument\BoundArgument;
use MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AutowireRequiredMethodsPass;
use MolliePrefix\Symfony\Component\DependencyInjection\Compiler\DefinitionErrorExceptionPass;
use MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveBindingsPass;
use MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder;
use MolliePrefix\Symfony\Component\DependencyInjection\Definition;
use MolliePrefix\Symfony\Component\DependencyInjection\Reference;
use MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\CaseSensitiveClass;
use MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\NamedArgumentsDummy;
use MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\ParentNotExists;
use MolliePrefix\Symfony\Component\DependencyInjection\TypedReference;
require_once __DIR__ . '/../Fixtures/includes/autowiring_classes.php';
class ResolveBindingsPassTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    public function testProcess()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $bindings = [\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\CaseSensitiveClass::class => new \MolliePrefix\Symfony\Component\DependencyInjection\Argument\BoundArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('foo'))];
        $definition = $container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\NamedArgumentsDummy::class, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\NamedArgumentsDummy::class);
        $definition->setArguments([1 => '123']);
        $definition->addMethodCall('setSensitiveClass');
        $definition->setBindings($bindings);
        $container->register('foo', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\CaseSensitiveClass::class)->setBindings($bindings);
        $pass = new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveBindingsPass();
        $pass->process($container);
        $this->assertEquals([new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('foo'), '123'], $definition->getArguments());
        $this->assertEquals([['setSensitiveClass', [new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('foo')]]], $definition->getMethodCalls());
    }
    public function testUnusedBinding()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Exception\\InvalidArgumentException');
        $this->expectExceptionMessage('Unused binding "$quz" in service "Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\NamedArgumentsDummy".');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $definition = $container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\NamedArgumentsDummy::class, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\NamedArgumentsDummy::class);
        $definition->setBindings(['$quz' => '123']);
        $pass = new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveBindingsPass();
        $pass->process($container);
    }
    public function testMissingParent()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Exception\\InvalidArgumentException');
        $this->expectExceptionMessageMatches('/Unused binding "\\$quz" in service [\\s\\S]+/');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $definition = $container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\ParentNotExists::class, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\ParentNotExists::class);
        $definition->setBindings(['$quz' => '123']);
        $pass = new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveBindingsPass();
        $pass->process($container);
    }
    public function testTypedReferenceSupport()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $bindings = [\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\CaseSensitiveClass::class => new \MolliePrefix\Symfony\Component\DependencyInjection\Argument\BoundArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('foo'))];
        // Explicit service id
        $definition1 = $container->register('def1', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\NamedArgumentsDummy::class);
        $definition1->addArgument($typedRef = new \MolliePrefix\Symfony\Component\DependencyInjection\TypedReference('bar', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\CaseSensitiveClass::class));
        $definition1->setBindings($bindings);
        $definition2 = $container->register('def2', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\NamedArgumentsDummy::class);
        $definition2->addArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\TypedReference(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\CaseSensitiveClass::class, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\CaseSensitiveClass::class));
        $definition2->setBindings($bindings);
        $pass = new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveBindingsPass();
        $pass->process($container);
        $this->assertEquals([$typedRef], $container->getDefinition('def1')->getArguments());
        $this->assertEquals([new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('foo')], $container->getDefinition('def2')->getArguments());
    }
    public function testScalarSetter()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $definition = $container->autowire('foo', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\ScalarSetter::class);
        $definition->setBindings(['$defaultLocale' => 'fr']);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\AutowireRequiredMethodsPass())->process($container);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveBindingsPass())->process($container);
        $this->assertEquals([['setDefaultLocale', ['fr']]], $definition->getMethodCalls());
    }
    public function testWithNonExistingSetterAndBinding()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Exception\\RuntimeException');
        $this->expectExceptionMessage('Invalid service "Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\NamedArgumentsDummy": method "setLogger()" does not exist.');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $bindings = ['$c' => (new \MolliePrefix\Symfony\Component\DependencyInjection\Definition('logger'))->setFactory('logger')];
        $definition = $container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\NamedArgumentsDummy::class, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\NamedArgumentsDummy::class);
        $definition->addMethodCall('setLogger');
        $definition->setBindings($bindings);
        $pass = new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveBindingsPass();
        $pass->process($container);
    }
    public function testSyntheticServiceWithBind()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $argument = new \MolliePrefix\Symfony\Component\DependencyInjection\Argument\BoundArgument('bar');
        $container->register('foo', 'stdClass')->addArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('synthetic.service'));
        $container->register('synthetic.service')->setSynthetic(\true)->setBindings(['$apiKey' => $argument]);
        $container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\NamedArgumentsDummy::class, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\NamedArgumentsDummy::class)->setBindings(['$apiKey' => $argument]);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveBindingsPass())->process($container);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\DefinitionErrorExceptionPass())->process($container);
        $this->assertSame([1 => 'bar'], $container->getDefinition(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\NamedArgumentsDummy::class)->getArguments());
    }
}
