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
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Compiler\ResolveNamedArgumentsPass;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Reference;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\CaseSensitiveClass;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\FactoryDummyWithoutReturnTypes;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\NamedArgumentsDummy;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\SimilarArgumentsDummy;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\TestDefinition1;
/**
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
class ResolveNamedArgumentsPassTest extends \_PhpScoper5ea00cc67502b\PHPUnit\Framework\TestCase
{
    public function testProcess()
    {
        $container = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder();
        $definition = $container->register(\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\NamedArgumentsDummy::class, \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\NamedArgumentsDummy::class);
        $definition->setArguments([2 => 'http://api.example.com', '$apiKey' => '123', 0 => new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Reference('foo')]);
        $definition->addMethodCall('setApiKey', ['$apiKey' => '123']);
        $pass = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Compiler\ResolveNamedArgumentsPass();
        $pass->process($container);
        $this->assertEquals([0 => new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Reference('foo'), 1 => '123', 2 => 'http://api.example.com'], $definition->getArguments());
        $this->assertEquals([['setApiKey', ['123']]], $definition->getMethodCalls());
    }
    public function testWithFactory()
    {
        $container = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('factory', \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\NoConstructor::class);
        $definition = $container->register('foo', \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\NoConstructor::class)->setFactory([new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Reference('factory'), 'create'])->setArguments(['$apiKey' => '123']);
        $pass = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Compiler\ResolveNamedArgumentsPass();
        $pass->process($container);
        $this->assertSame([0 => '123'], $definition->getArguments());
    }
    public function testClassNull()
    {
        $this->expectException('_PhpScoper5ea00cc67502b\\Symfony\\Component\\DependencyInjection\\Exception\\RuntimeException');
        $container = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder();
        $definition = $container->register(\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\NamedArgumentsDummy::class);
        $definition->setArguments(['$apiKey' => '123']);
        $pass = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Compiler\ResolveNamedArgumentsPass();
        $pass->process($container);
    }
    public function testClassNotExist()
    {
        $this->expectException('_PhpScoper5ea00cc67502b\\Symfony\\Component\\DependencyInjection\\Exception\\RuntimeException');
        $container = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder();
        $definition = $container->register(\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\NotExist::class, \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\NotExist::class);
        $definition->setArguments(['$apiKey' => '123']);
        $pass = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Compiler\ResolveNamedArgumentsPass();
        $pass->process($container);
    }
    public function testClassNoConstructor()
    {
        $this->expectException('_PhpScoper5ea00cc67502b\\Symfony\\Component\\DependencyInjection\\Exception\\RuntimeException');
        $container = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder();
        $definition = $container->register(\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\NoConstructor::class, \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Compiler\NoConstructor::class);
        $definition->setArguments(['$apiKey' => '123']);
        $pass = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Compiler\ResolveNamedArgumentsPass();
        $pass->process($container);
    }
    public function testArgumentNotFound()
    {
        $this->expectException('_PhpScoper5ea00cc67502b\\Symfony\\Component\\DependencyInjection\\Exception\\InvalidArgumentException');
        $this->expectExceptionMessage('Invalid service "Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\NamedArgumentsDummy": method "__construct()" has no argument named "$notFound". Check your service definition.');
        $container = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder();
        $definition = $container->register(\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\NamedArgumentsDummy::class, \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\NamedArgumentsDummy::class);
        $definition->setArguments(['$notFound' => '123']);
        $pass = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Compiler\ResolveNamedArgumentsPass();
        $pass->process($container);
    }
    public function testCorrectMethodReportedInException()
    {
        $this->expectException('_PhpScoper5ea00cc67502b\\Symfony\\Component\\DependencyInjection\\Exception\\InvalidArgumentException');
        $this->expectExceptionMessage('Invalid service "Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\TestDefinition1": method "Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\FactoryDummyWithoutReturnTypes::createTestDefinition1()" has no argument named "$notFound". Check your service definition.');
        $container = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register(\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\FactoryDummyWithoutReturnTypes::class, \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\FactoryDummyWithoutReturnTypes::class);
        $definition = $container->register(\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\TestDefinition1::class, \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\TestDefinition1::class);
        $definition->setFactory([\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\FactoryDummyWithoutReturnTypes::class, 'createTestDefinition1']);
        $definition->setArguments(['$notFound' => '123']);
        $pass = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Compiler\ResolveNamedArgumentsPass();
        $pass->process($container);
    }
    public function testTypedArgument()
    {
        $container = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder();
        $definition = $container->register(\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\NamedArgumentsDummy::class, \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\NamedArgumentsDummy::class);
        $definition->setArguments(['$apiKey' => '123', \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\CaseSensitiveClass::class => new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Reference('foo')]);
        $pass = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Compiler\ResolveNamedArgumentsPass();
        $pass->process($container);
        $this->assertEquals([new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Reference('foo'), '123'], $definition->getArguments());
    }
    public function testResolvesMultipleArgumentsOfTheSameType()
    {
        $container = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder();
        $definition = $container->register(\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\SimilarArgumentsDummy::class, \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\SimilarArgumentsDummy::class);
        $definition->setArguments([\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\CaseSensitiveClass::class => new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Reference('foo'), '$token' => 'qwerty']);
        $pass = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Compiler\ResolveNamedArgumentsPass();
        $pass->process($container);
        $this->assertEquals([new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Reference('foo'), 'qwerty', new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Reference('foo')], $definition->getArguments());
    }
    public function testResolvePrioritizeNamedOverType()
    {
        $container = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder();
        $definition = $container->register(\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\SimilarArgumentsDummy::class, \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\SimilarArgumentsDummy::class);
        $definition->setArguments([\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\CaseSensitiveClass::class => new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Reference('foo'), '$token' => 'qwerty', '$class1' => new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Reference('bar')]);
        $pass = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Compiler\ResolveNamedArgumentsPass();
        $pass->process($container);
        $this->assertEquals([new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Reference('bar'), 'qwerty', new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Reference('foo')], $definition->getArguments());
    }
}
class NoConstructor
{
    public static function create($apiKey)
    {
    }
}
