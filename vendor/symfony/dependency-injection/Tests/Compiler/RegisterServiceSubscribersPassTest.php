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
use _PhpScoper5ea00cc67502b\Psr\Container\ContainerInterface as PsrContainerInterface;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Compiler\RegisterServiceSubscribersPass;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Compiler\ResolveServiceSubscribersPass;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerInterface;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Reference;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ServiceLocator;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\TypedReference;
require_once __DIR__ . '/../Fixtures/includes/classes.php';
class RegisterServiceSubscribersPassTest extends \_PhpScoper5ea00cc67502b\PHPUnit\Framework\TestCase
{
    public function testInvalidClass()
    {
        $this->expectException('_PhpScoper5ea00cc67502b\\Symfony\\Component\\DependencyInjection\\Exception\\InvalidArgumentException');
        $this->expectExceptionMessage('Service "foo" must implement interface "Symfony\\Component\\DependencyInjection\\ServiceSubscriberInterface".');
        $container = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('foo', \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition::class)->addTag('container.service_subscriber');
        (new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Compiler\RegisterServiceSubscribersPass())->process($container);
        (new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Compiler\ResolveServiceSubscribersPass())->process($container);
    }
    public function testInvalidAttributes()
    {
        $this->expectException('_PhpScoper5ea00cc67502b\\Symfony\\Component\\DependencyInjection\\Exception\\InvalidArgumentException');
        $this->expectExceptionMessage('The "container.service_subscriber" tag accepts only the "key" and "id" attributes, "bar" given for service "foo".');
        $container = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('foo', \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber::class)->addTag('container.service_subscriber', ['bar' => '123']);
        (new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Compiler\RegisterServiceSubscribersPass())->process($container);
        (new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Compiler\ResolveServiceSubscribersPass())->process($container);
    }
    public function testNoAttributes()
    {
        $container = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('foo', \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber::class)->addArgument(new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Reference(\_PhpScoper5ea00cc67502b\Psr\Container\ContainerInterface::class))->addTag('container.service_subscriber');
        (new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Compiler\RegisterServiceSubscribersPass())->process($container);
        (new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Compiler\ResolveServiceSubscribersPass())->process($container);
        $foo = $container->getDefinition('foo');
        $locator = $container->getDefinition((string) $foo->getArgument(0));
        $this->assertFalse($locator->isPublic());
        $this->assertSame(\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ServiceLocator::class, $locator->getClass());
        $expected = [\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber::class => new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument(new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\TypedReference(\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber::class, \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber::class, \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber::class)), \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition::class => new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument(new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\TypedReference(\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition::class, \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition::class, \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber::class, \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerInterface::IGNORE_ON_INVALID_REFERENCE)), 'bar' => new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument(new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\TypedReference(\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition::class, \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition::class, \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber::class)), 'baz' => new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument(new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\TypedReference(\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition::class, \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition::class, \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber::class, \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerInterface::IGNORE_ON_INVALID_REFERENCE))];
        $this->assertEquals($expected, $container->getDefinition((string) $locator->getFactory()[0])->getArgument(0));
    }
    public function testWithAttributes()
    {
        $container = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('foo', \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber::class)->setAutowired(\true)->addArgument(new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Reference(\_PhpScoper5ea00cc67502b\Psr\Container\ContainerInterface::class))->addTag('container.service_subscriber', ['key' => 'bar', 'id' => 'bar'])->addTag('container.service_subscriber', ['key' => 'bar', 'id' => 'baz']);
        (new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Compiler\RegisterServiceSubscribersPass())->process($container);
        (new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Compiler\ResolveServiceSubscribersPass())->process($container);
        $foo = $container->getDefinition('foo');
        $locator = $container->getDefinition((string) $foo->getArgument(0));
        $this->assertFalse($locator->isPublic());
        $this->assertSame(\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ServiceLocator::class, $locator->getClass());
        $expected = [\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber::class => new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument(new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\TypedReference(\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber::class, \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber::class, \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber::class)), \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition::class => new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument(new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\TypedReference(\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition::class, \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition::class, \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber::class, \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerInterface::IGNORE_ON_INVALID_REFERENCE)), 'bar' => new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument(new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\TypedReference('bar', \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition::class, \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber::class)), 'baz' => new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument(new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\TypedReference(\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition::class, \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition::class, \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber::class, \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerInterface::IGNORE_ON_INVALID_REFERENCE))];
        $this->assertEquals($expected, $container->getDefinition((string) $locator->getFactory()[0])->getArgument(0));
    }
    public function testExtraServiceSubscriber()
    {
        $this->expectException('_PhpScoper5ea00cc67502b\\Symfony\\Component\\DependencyInjection\\Exception\\InvalidArgumentException');
        $this->expectExceptionMessage('Service key "test" does not exist in the map returned by "Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\TestServiceSubscriber::getSubscribedServices()" for service "foo_service".');
        $container = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('foo_service', \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber::class)->setAutowired(\true)->addArgument(new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Reference(\_PhpScoper5ea00cc67502b\Psr\Container\ContainerInterface::class))->addTag('container.service_subscriber', ['key' => 'test', 'id' => \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber::class]);
        $container->register(\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber::class, \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber::class);
        $container->compile();
    }
}
