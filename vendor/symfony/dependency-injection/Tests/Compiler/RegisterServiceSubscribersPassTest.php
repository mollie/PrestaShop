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
use MolliePrefix\Psr\Container\ContainerInterface as PsrContainerInterface;
use MolliePrefix\Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument;
use MolliePrefix\Symfony\Component\DependencyInjection\Compiler\RegisterServiceSubscribersPass;
use MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveServiceSubscribersPass;
use MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder;
use MolliePrefix\Symfony\Component\DependencyInjection\ContainerInterface;
use MolliePrefix\Symfony\Component\DependencyInjection\Reference;
use MolliePrefix\Symfony\Component\DependencyInjection\ServiceLocator;
use MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition;
use MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber;
use MolliePrefix\Symfony\Component\DependencyInjection\TypedReference;
require_once __DIR__ . '/../Fixtures/includes/classes.php';
class RegisterServiceSubscribersPassTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    public function testInvalidClass()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Exception\\InvalidArgumentException');
        $this->expectExceptionMessage('Service "foo" must implement interface "Symfony\\Component\\DependencyInjection\\ServiceSubscriberInterface".');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('foo', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition::class)->addTag('container.service_subscriber');
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\RegisterServiceSubscribersPass())->process($container);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveServiceSubscribersPass())->process($container);
    }
    public function testInvalidAttributes()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Exception\\InvalidArgumentException');
        $this->expectExceptionMessage('The "container.service_subscriber" tag accepts only the "key" and "id" attributes, "bar" given for service "foo".');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('foo', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber::class)->addTag('container.service_subscriber', ['bar' => '123']);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\RegisterServiceSubscribersPass())->process($container);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveServiceSubscribersPass())->process($container);
    }
    public function testNoAttributes()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('foo', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber::class)->addArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\Reference(\MolliePrefix\Psr\Container\ContainerInterface::class))->addTag('container.service_subscriber');
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\RegisterServiceSubscribersPass())->process($container);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveServiceSubscribersPass())->process($container);
        $foo = $container->getDefinition('foo');
        $locator = $container->getDefinition((string) $foo->getArgument(0));
        $this->assertFalse($locator->isPublic());
        $this->assertSame(\MolliePrefix\Symfony\Component\DependencyInjection\ServiceLocator::class, $locator->getClass());
        $expected = [\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber::class => new \MolliePrefix\Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\TypedReference(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber::class, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber::class, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber::class)), \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition::class => new \MolliePrefix\Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\TypedReference(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition::class, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition::class, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber::class, \MolliePrefix\Symfony\Component\DependencyInjection\ContainerInterface::IGNORE_ON_INVALID_REFERENCE)), 'bar' => new \MolliePrefix\Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\TypedReference(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition::class, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition::class, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber::class)), 'baz' => new \MolliePrefix\Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\TypedReference(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition::class, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition::class, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber::class, \MolliePrefix\Symfony\Component\DependencyInjection\ContainerInterface::IGNORE_ON_INVALID_REFERENCE))];
        $this->assertEquals($expected, $container->getDefinition((string) $locator->getFactory()[0])->getArgument(0));
    }
    public function testWithAttributes()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('foo', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber::class)->setAutowired(\true)->addArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\Reference(\MolliePrefix\Psr\Container\ContainerInterface::class))->addTag('container.service_subscriber', ['key' => 'bar', 'id' => 'bar'])->addTag('container.service_subscriber', ['key' => 'bar', 'id' => 'baz']);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\RegisterServiceSubscribersPass())->process($container);
        (new \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\ResolveServiceSubscribersPass())->process($container);
        $foo = $container->getDefinition('foo');
        $locator = $container->getDefinition((string) $foo->getArgument(0));
        $this->assertFalse($locator->isPublic());
        $this->assertSame(\MolliePrefix\Symfony\Component\DependencyInjection\ServiceLocator::class, $locator->getClass());
        $expected = [\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber::class => new \MolliePrefix\Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\TypedReference(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber::class, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber::class, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber::class)), \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition::class => new \MolliePrefix\Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\TypedReference(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition::class, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition::class, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber::class, \MolliePrefix\Symfony\Component\DependencyInjection\ContainerInterface::IGNORE_ON_INVALID_REFERENCE)), 'bar' => new \MolliePrefix\Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\TypedReference('bar', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition::class, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber::class)), 'baz' => new \MolliePrefix\Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\TypedReference(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition::class, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition::class, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber::class, \MolliePrefix\Symfony\Component\DependencyInjection\ContainerInterface::IGNORE_ON_INVALID_REFERENCE))];
        $this->assertEquals($expected, $container->getDefinition((string) $locator->getFactory()[0])->getArgument(0));
    }
    public function testExtraServiceSubscriber()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\DependencyInjection\\Exception\\InvalidArgumentException');
        $this->expectExceptionMessage('Service key "test" does not exist in the map returned by "Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\TestServiceSubscriber::getSubscribedServices()" for service "foo_service".');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('foo_service', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber::class)->setAutowired(\true)->addArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\Reference(\MolliePrefix\Psr\Container\ContainerInterface::class))->addTag('container.service_subscriber', ['key' => 'test', 'id' => \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber::class]);
        $container->register(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber::class, \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber::class);
        $container->compile();
    }
}
