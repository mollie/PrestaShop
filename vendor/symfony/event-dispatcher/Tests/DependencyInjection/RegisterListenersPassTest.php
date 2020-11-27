<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\EventDispatcher\Tests\DependencyInjection;

use MolliePrefix\PHPUnit\Framework\TestCase;
use MolliePrefix\Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument;
use MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder;
use MolliePrefix\Symfony\Component\DependencyInjection\Reference;
use MolliePrefix\Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass;
class RegisterListenersPassTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    /**
     * Tests that event subscribers not implementing EventSubscriberInterface
     * trigger an exception.
     */
    public function testEventSubscriberWithoutInterface()
    {
        $this->expectException('InvalidArgumentException');
        $builder = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $builder->register('event_dispatcher');
        $builder->register('my_event_subscriber', 'stdClass')->addTag('kernel.event_subscriber');
        $registerListenersPass = new \MolliePrefix\Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass();
        $registerListenersPass->process($builder);
    }
    public function testValidEventSubscriber()
    {
        $builder = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $eventDispatcherDefinition = $builder->register('event_dispatcher');
        $builder->register('my_event_subscriber', 'MolliePrefix\\Symfony\\Component\\EventDispatcher\\Tests\\DependencyInjection\\SubscriberService')->addTag('kernel.event_subscriber');
        $registerListenersPass = new \MolliePrefix\Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass();
        $registerListenersPass->process($builder);
        $expectedCalls = [['addListener', ['event', [new \MolliePrefix\Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('my_event_subscriber')), 'onEvent'], 0]]];
        $this->assertEquals($expectedCalls, $eventDispatcherDefinition->getMethodCalls());
    }
    public function testAbstractEventListener()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('The service "foo" tagged "kernel.event_listener" must not be abstract.');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('foo', 'stdClass')->setAbstract(\true)->addTag('kernel.event_listener', []);
        $container->register('event_dispatcher', 'stdClass');
        $registerListenersPass = new \MolliePrefix\Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass();
        $registerListenersPass->process($container);
    }
    public function testAbstractEventSubscriber()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('The service "foo" tagged "kernel.event_subscriber" must not be abstract.');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('foo', 'stdClass')->setAbstract(\true)->addTag('kernel.event_subscriber', []);
        $container->register('event_dispatcher', 'stdClass');
        $registerListenersPass = new \MolliePrefix\Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass();
        $registerListenersPass->process($container);
    }
    public function testEventSubscriberResolvableClassName()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->setParameter('subscriber.class', 'MolliePrefix\\Symfony\\Component\\EventDispatcher\\Tests\\DependencyInjection\\SubscriberService');
        $container->register('foo', '%subscriber.class%')->addTag('kernel.event_subscriber', []);
        $container->register('event_dispatcher', 'stdClass');
        $registerListenersPass = new \MolliePrefix\Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass();
        $registerListenersPass->process($container);
        $definition = $container->getDefinition('event_dispatcher');
        $expectedCalls = [['addListener', ['event', [new \MolliePrefix\Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('foo')), 'onEvent'], 0]]];
        $this->assertEquals($expectedCalls, $definition->getMethodCalls());
    }
    public function testHotPathEvents()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('foo', \MolliePrefix\Symfony\Component\EventDispatcher\Tests\DependencyInjection\SubscriberService::class)->addTag('kernel.event_subscriber', []);
        $container->register('event_dispatcher', 'stdClass');
        (new \MolliePrefix\Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass())->setHotPathEvents(['event'])->process($container);
        $this->assertTrue($container->getDefinition('foo')->hasTag('container.hot_path'));
    }
    public function testEventSubscriberUnresolvableClassName()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('You have requested a non-existent parameter "subscriber.class"');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('foo', '%subscriber.class%')->addTag('kernel.event_subscriber', []);
        $container->register('event_dispatcher', 'stdClass');
        $registerListenersPass = new \MolliePrefix\Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass();
        $registerListenersPass->process($container);
    }
}
class SubscriberService implements \MolliePrefix\Symfony\Component\EventDispatcher\EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return ['event' => 'onEvent'];
    }
}
