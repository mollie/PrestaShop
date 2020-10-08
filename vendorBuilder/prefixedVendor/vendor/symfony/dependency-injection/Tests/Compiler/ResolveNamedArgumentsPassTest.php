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
use MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveNamedArgumentsPass;
use MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder;
use MolliePrefix\Symfony\Component\DependencyInjection\Reference;
use MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\CaseSensitiveClass;
use MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\FactoryDummyWithoutReturnTypes;
use MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\NamedArgumentsDummy;
use MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\SimilarArgumentsDummy;
use MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\TestDefinition1;
/**
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
class ResolveNamedArgumentsPassTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    public function testProcess()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $definition = $container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\NamedArgumentsDummy::class, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\NamedArgumentsDummy::class);
        $definition->setArguments([2 => 'http://api.example.com', '$apiKey' => '123', 0 => new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('foo')]);
        $definition->addMethodCall('setApiKey', ['$apiKey' => '123']);
        $pass = new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveNamedArgumentsPass();
        $pass->process($container);
        $this->assertEquals([0 => new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('foo'), 1 => '123', 2 => 'http://api.example.com'], $definition->getArguments());
        $this->assertEquals([['setApiKey', ['123']]], $definition->getMethodCalls());
    }
    public function testWithFactory()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('factory', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\NoConstructor::class);
        $definition = $container->register('foo', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\NoConstructor::class)->setFactory([new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('factory'), 'create'])->setArguments(['$apiKey' => '123']);
        $pass = new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveNamedArgumentsPass();
        $pass->process($container);
        $this->assertSame([0 => '123'], $definition->getArguments());
    }
    public function testClassNull()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Exception\\RuntimeException');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $definition = $container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\NamedArgumentsDummy::class);
        $definition->setArguments(['$apiKey' => '123']);
        $pass = new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveNamedArgumentsPass();
        $pass->process($container);
    }
    public function testClassNotExist()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Exception\\RuntimeException');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $definition = $container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\NotExist::class, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\NotExist::class);
        $definition->setArguments(['$apiKey' => '123']);
        $pass = new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveNamedArgumentsPass();
        $pass->process($container);
    }
    public function testClassNoConstructor()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Exception\\RuntimeException');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $definition = $container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\NoConstructor::class, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Compiler\NoConstructor::class);
        $definition->setArguments(['$apiKey' => '123']);
        $pass = new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveNamedArgumentsPass();
        $pass->process($container);
    }
    public function testArgumentNotFound()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Exception\\InvalidArgumentException');
        $this->expectExceptionMessage('Invalid service "Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\NamedArgumentsDummy": method "__construct()" has no argument named "$notFound". Check your service definition.');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $definition = $container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\NamedArgumentsDummy::class, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\NamedArgumentsDummy::class);
        $definition->setArguments(['$notFound' => '123']);
        $pass = new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveNamedArgumentsPass();
        $pass->process($container);
    }
    public function testCorrectMethodReportedInException()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Exception\\InvalidArgumentException');
        $this->expectExceptionMessage('Invalid service "Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\TestDefinition1": method "Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\FactoryDummyWithoutReturnTypes::createTestDefinition1()" has no argument named "$notFound". Check your service definition.');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\FactoryDummyWithoutReturnTypes::class, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\FactoryDummyWithoutReturnTypes::class);
        $definition = $container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\TestDefinition1::class, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\TestDefinition1::class);
        $definition->setFactory([\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\FactoryDummyWithoutReturnTypes::class, 'createTestDefinition1']);
        $definition->setArguments(['$notFound' => '123']);
        $pass = new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveNamedArgumentsPass();
        $pass->process($container);
    }
    public function testTypedArgument()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $definition = $container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\NamedArgumentsDummy::class, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\NamedArgumentsDummy::class);
        $definition->setArguments(['$apiKey' => '123', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\CaseSensitiveClass::class => new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('foo')]);
        $pass = new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveNamedArgumentsPass();
        $pass->process($container);
        $this->assertEquals([new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('foo'), '123'], $definition->getArguments());
    }
    public function testResolvesMultipleArgumentsOfTheSameType()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $definition = $container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\SimilarArgumentsDummy::class, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\SimilarArgumentsDummy::class);
        $definition->setArguments([\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\CaseSensitiveClass::class => new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('foo'), '$token' => 'qwerty']);
        $pass = new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveNamedArgumentsPass();
        $pass->process($container);
        $this->assertEquals([new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('foo'), 'qwerty', new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('foo')], $definition->getArguments());
    }
    public function testResolvePrioritizeNamedOverType()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $definition = $container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\SimilarArgumentsDummy::class, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\SimilarArgumentsDummy::class);
        $definition->setArguments([\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\CaseSensitiveClass::class => new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('foo'), '$token' => 'qwerty', '$class1' => new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('bar')]);
        $pass = new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveNamedArgumentsPass();
        $pass->process($container);
        $this->assertEquals([new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('bar'), 'qwerty', new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('foo')], $definition->getArguments());
    }
}
class NoConstructor
{
    public static function create($apiKey)
    {
    }
}
