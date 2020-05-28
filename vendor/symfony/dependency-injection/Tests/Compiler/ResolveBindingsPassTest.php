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
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Argument\BoundArgument;
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Compiler\AutowireRequiredMethodsPass;
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Compiler\DefinitionErrorExceptionPass;
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Compiler\ResolveBindingsPass;
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ContainerBuilder;
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Definition;
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Reference;
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Tests\Fixtures\CaseSensitiveClass;
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Tests\Fixtures\NamedArgumentsDummy;
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Tests\Fixtures\ParentNotExists;
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\TypedReference;
require_once __DIR__ . '/../Fixtures/includes/autowiring_classes.php';
class ResolveBindingsPassTest extends \_PhpScoper5ece82d7231e4\PHPUnit\Framework\TestCase
{
    public function testProcess()
    {
        $container = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ContainerBuilder();
        $bindings = [\_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Tests\Fixtures\CaseSensitiveClass::class => new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Argument\BoundArgument(new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Reference('foo'))];
        $definition = $container->register(\_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Tests\Fixtures\NamedArgumentsDummy::class, \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Tests\Fixtures\NamedArgumentsDummy::class);
        $definition->setArguments([1 => '123']);
        $definition->addMethodCall('setSensitiveClass');
        $definition->setBindings($bindings);
        $container->register('foo', \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Tests\Fixtures\CaseSensitiveClass::class)->setBindings($bindings);
        $pass = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Compiler\ResolveBindingsPass();
        $pass->process($container);
        $this->assertEquals([new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Reference('foo'), '123'], $definition->getArguments());
        $this->assertEquals([['setSensitiveClass', [new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Reference('foo')]]], $definition->getMethodCalls());
    }
    public function testUnusedBinding()
    {
        $this->expectException('_PhpScoper5ece82d7231e4\\Symfony\\Component\\DependencyInjection\\Exception\\InvalidArgumentException');
        $this->expectExceptionMessage('Unused binding "$quz" in service "Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\NamedArgumentsDummy".');
        $container = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ContainerBuilder();
        $definition = $container->register(\_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Tests\Fixtures\NamedArgumentsDummy::class, \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Tests\Fixtures\NamedArgumentsDummy::class);
        $definition->setBindings(['$quz' => '123']);
        $pass = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Compiler\ResolveBindingsPass();
        $pass->process($container);
    }
    public function testMissingParent()
    {
        $this->expectException('_PhpScoper5ece82d7231e4\\Symfony\\Component\\DependencyInjection\\Exception\\InvalidArgumentException');
        $this->expectExceptionMessageRegExp('/Unused binding "\\$quz" in service [\\s\\S]+/');
        $container = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ContainerBuilder();
        $definition = $container->register(\_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Tests\Fixtures\ParentNotExists::class, \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Tests\Fixtures\ParentNotExists::class);
        $definition->setBindings(['$quz' => '123']);
        $pass = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Compiler\ResolveBindingsPass();
        $pass->process($container);
    }
    public function testTypedReferenceSupport()
    {
        $container = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ContainerBuilder();
        $bindings = [\_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Tests\Fixtures\CaseSensitiveClass::class => new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Argument\BoundArgument(new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Reference('foo'))];
        // Explicit service id
        $definition1 = $container->register('def1', \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Tests\Fixtures\NamedArgumentsDummy::class);
        $definition1->addArgument($typedRef = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\TypedReference('bar', \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Tests\Fixtures\CaseSensitiveClass::class));
        $definition1->setBindings($bindings);
        $definition2 = $container->register('def2', \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Tests\Fixtures\NamedArgumentsDummy::class);
        $definition2->addArgument(new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\TypedReference(\_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Tests\Fixtures\CaseSensitiveClass::class, \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Tests\Fixtures\CaseSensitiveClass::class));
        $definition2->setBindings($bindings);
        $pass = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Compiler\ResolveBindingsPass();
        $pass->process($container);
        $this->assertEquals([$typedRef], $container->getDefinition('def1')->getArguments());
        $this->assertEquals([new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Reference('foo')], $container->getDefinition('def2')->getArguments());
    }
    public function testScalarSetter()
    {
        $container = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ContainerBuilder();
        $definition = $container->autowire('foo', \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Tests\Compiler\ScalarSetter::class);
        $definition->setBindings(['$defaultLocale' => 'fr']);
        (new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Compiler\AutowireRequiredMethodsPass())->process($container);
        (new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Compiler\ResolveBindingsPass())->process($container);
        $this->assertEquals([['setDefaultLocale', ['fr']]], $definition->getMethodCalls());
    }
    public function testWithNonExistingSetterAndBinding()
    {
        $this->expectException('_PhpScoper5ece82d7231e4\\Symfony\\Component\\DependencyInjection\\Exception\\RuntimeException');
        $this->expectExceptionMessage('Invalid service "Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\NamedArgumentsDummy": method "setLogger()" does not exist.');
        $container = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ContainerBuilder();
        $bindings = ['$c' => (new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Definition('logger'))->setFactory('logger')];
        $definition = $container->register(\_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Tests\Fixtures\NamedArgumentsDummy::class, \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Tests\Fixtures\NamedArgumentsDummy::class);
        $definition->addMethodCall('setLogger');
        $definition->setBindings($bindings);
        $pass = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Compiler\ResolveBindingsPass();
        $pass->process($container);
    }
    public function testSyntheticServiceWithBind()
    {
        $container = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ContainerBuilder();
        $argument = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Argument\BoundArgument('bar');
        $container->register('foo', 'stdClass')->addArgument(new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Reference('synthetic.service'));
        $container->register('synthetic.service')->setSynthetic(\true)->setBindings(['$apiKey' => $argument]);
        $container->register(\_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Tests\Fixtures\NamedArgumentsDummy::class, \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Tests\Fixtures\NamedArgumentsDummy::class)->setBindings(['$apiKey' => $argument]);
        (new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Compiler\ResolveBindingsPass())->process($container);
        (new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Compiler\DefinitionErrorExceptionPass())->process($container);
        $this->assertSame([1 => 'bar'], $container->getDefinition(\_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Tests\Fixtures\NamedArgumentsDummy::class)->getArguments());
    }
}
