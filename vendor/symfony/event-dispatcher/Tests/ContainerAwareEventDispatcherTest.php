<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\EventDispatcher\Tests;

use MolliePrefix\Symfony\Component\DependencyInjection\Container;
use MolliePrefix\Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher;
use MolliePrefix\Symfony\Component\EventDispatcher\Event;
use MolliePrefix\Symfony\Component\EventDispatcher\EventSubscriberInterface;
/**
 * @group legacy
 */
class ContainerAwareEventDispatcherTest extends \MolliePrefix\Symfony\Component\EventDispatcher\Tests\AbstractEventDispatcherTest
{
    protected function createEventDispatcher()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\Container();
        return new \MolliePrefix\Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher($container);
    }
    public function testAddAListenerService()
    {
        $event = new \MolliePrefix\Symfony\Component\EventDispatcher\Event();
        $service = $this->getMockBuilder('MolliePrefix\\Symfony\\Component\\EventDispatcher\\Tests\\Service')->getMock();
        $service->expects($this->once())->method('onEvent')->with($event);
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\Container();
        $container->set('service.listener', $service);
        $dispatcher = new \MolliePrefix\Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher($container);
        $dispatcher->addListenerService('onEvent', ['service.listener', 'onEvent']);
        $dispatcher->dispatch('onEvent', $event);
    }
    public function testAddASubscriberService()
    {
        $event = new \MolliePrefix\Symfony\Component\EventDispatcher\Event();
        $service = $this->getMockBuilder('MolliePrefix\\Symfony\\Component\\EventDispatcher\\Tests\\SubscriberService')->getMock();
        $service->expects($this->once())->method('onEvent')->with($event);
        $service->expects($this->once())->method('onEventWithPriority')->with($event);
        $service->expects($this->once())->method('onEventNested')->with($event);
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\Container();
        $container->set('service.subscriber', $service);
        $dispatcher = new \MolliePrefix\Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher($container);
        $dispatcher->addSubscriberService('service.subscriber', 'MolliePrefix\\Symfony\\Component\\EventDispatcher\\Tests\\SubscriberService');
        $dispatcher->dispatch('onEvent', $event);
        $dispatcher->dispatch('onEventWithPriority', $event);
        $dispatcher->dispatch('onEventNested', $event);
    }
    public function testPreventDuplicateListenerService()
    {
        $event = new \MolliePrefix\Symfony\Component\EventDispatcher\Event();
        $service = $this->getMockBuilder('MolliePrefix\\Symfony\\Component\\EventDispatcher\\Tests\\Service')->getMock();
        $service->expects($this->once())->method('onEvent')->with($event);
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\Container();
        $container->set('service.listener', $service);
        $dispatcher = new \MolliePrefix\Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher($container);
        $dispatcher->addListenerService('onEvent', ['service.listener', 'onEvent'], 5);
        $dispatcher->addListenerService('onEvent', ['service.listener', 'onEvent'], 10);
        $dispatcher->dispatch('onEvent', $event);
    }
    public function testHasListenersOnLazyLoad()
    {
        $event = new \MolliePrefix\Symfony\Component\EventDispatcher\Event();
        $service = $this->getMockBuilder('MolliePrefix\\Symfony\\Component\\EventDispatcher\\Tests\\Service')->getMock();
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\Container();
        $container->set('service.listener', $service);
        $dispatcher = new \MolliePrefix\Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher($container);
        $dispatcher->addListenerService('onEvent', ['service.listener', 'onEvent']);
        $service->expects($this->once())->method('onEvent')->with($event);
        $this->assertTrue($dispatcher->hasListeners());
        if ($dispatcher->hasListeners('onEvent')) {
            $dispatcher->dispatch('onEvent');
        }
    }
    public function testGetListenersOnLazyLoad()
    {
        $service = $this->getMockBuilder('MolliePrefix\\Symfony\\Component\\EventDispatcher\\Tests\\Service')->getMock();
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\Container();
        $container->set('service.listener', $service);
        $dispatcher = new \MolliePrefix\Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher($container);
        $dispatcher->addListenerService('onEvent', ['service.listener', 'onEvent']);
        $listeners = $dispatcher->getListeners();
        $this->assertArrayHasKey('onEvent', $listeners);
        $this->assertCount(1, $dispatcher->getListeners('onEvent'));
    }
    public function testRemoveAfterDispatch()
    {
        $service = $this->getMockBuilder('MolliePrefix\\Symfony\\Component\\EventDispatcher\\Tests\\Service')->getMock();
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\Container();
        $container->set('service.listener', $service);
        $dispatcher = new \MolliePrefix\Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher($container);
        $dispatcher->addListenerService('onEvent', ['service.listener', 'onEvent']);
        $dispatcher->dispatch('onEvent', new \MolliePrefix\Symfony\Component\EventDispatcher\Event());
        $dispatcher->removeListener('onEvent', [$container->get('service.listener'), 'onEvent']);
        $this->assertFalse($dispatcher->hasListeners('onEvent'));
    }
    public function testRemoveBeforeDispatch()
    {
        $service = $this->getMockBuilder('MolliePrefix\\Symfony\\Component\\EventDispatcher\\Tests\\Service')->getMock();
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\Container();
        $container->set('service.listener', $service);
        $dispatcher = new \MolliePrefix\Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher($container);
        $dispatcher->addListenerService('onEvent', ['service.listener', 'onEvent']);
        $dispatcher->removeListener('onEvent', [$container->get('service.listener'), 'onEvent']);
        $this->assertFalse($dispatcher->hasListeners('onEvent'));
    }
}
class Service
{
    public function onEvent(\MolliePrefix\Symfony\Component\EventDispatcher\Event $e)
    {
    }
}
class SubscriberService implements \MolliePrefix\Symfony\Component\EventDispatcher\EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return ['onEvent' => 'onEvent', 'onEventWithPriority' => ['onEventWithPriority', 10], 'onEventNested' => [['onEventNested']]];
    }
    public function onEvent(\MolliePrefix\Symfony\Component\EventDispatcher\Event $e)
    {
    }
    public function onEventWithPriority(\MolliePrefix\Symfony\Component\EventDispatcher\Event $e)
    {
    }
    public function onEventNested(\MolliePrefix\Symfony\Component\EventDispatcher\Event $e)
    {
    }
}
