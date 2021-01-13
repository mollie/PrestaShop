<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\EventDispatcher\Tests\Debug;

use MolliePrefix\PHPUnit\Framework\TestCase;
use MolliePrefix\Symfony\Component\Debug\BufferingLogger;
use MolliePrefix\Symfony\Component\EventDispatcher\Debug\TraceableEventDispatcher;
use MolliePrefix\Symfony\Component\EventDispatcher\Event;
use MolliePrefix\Symfony\Component\EventDispatcher\EventDispatcher;
use MolliePrefix\Symfony\Component\EventDispatcher\EventDispatcherInterface;
use MolliePrefix\Symfony\Component\EventDispatcher\EventSubscriberInterface;
use MolliePrefix\Symfony\Component\Stopwatch\Stopwatch;
class TraceableEventDispatcherTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    public function testAddRemoveListener()
    {
        $dispatcher = new \MolliePrefix\Symfony\Component\EventDispatcher\EventDispatcher();
        $tdispatcher = new \MolliePrefix\Symfony\Component\EventDispatcher\Debug\TraceableEventDispatcher($dispatcher, new \MolliePrefix\Symfony\Component\Stopwatch\Stopwatch());
        $tdispatcher->addListener('foo', $listener = function () {
        });
        $listeners = $dispatcher->getListeners('foo');
        $this->assertCount(1, $listeners);
        $this->assertSame($listener, $listeners[0]);
        $tdispatcher->removeListener('foo', $listener);
        $this->assertCount(0, $dispatcher->getListeners('foo'));
    }
    public function testGetListeners()
    {
        $dispatcher = new \MolliePrefix\Symfony\Component\EventDispatcher\EventDispatcher();
        $tdispatcher = new \MolliePrefix\Symfony\Component\EventDispatcher\Debug\TraceableEventDispatcher($dispatcher, new \MolliePrefix\Symfony\Component\Stopwatch\Stopwatch());
        $tdispatcher->addListener('foo', $listener = function () {
        });
        $this->assertSame($dispatcher->getListeners('foo'), $tdispatcher->getListeners('foo'));
    }
    public function testHasListeners()
    {
        $dispatcher = new \MolliePrefix\Symfony\Component\EventDispatcher\EventDispatcher();
        $tdispatcher = new \MolliePrefix\Symfony\Component\EventDispatcher\Debug\TraceableEventDispatcher($dispatcher, new \MolliePrefix\Symfony\Component\Stopwatch\Stopwatch());
        $this->assertFalse($dispatcher->hasListeners('foo'));
        $this->assertFalse($tdispatcher->hasListeners('foo'));
        $tdispatcher->addListener('foo', $listener = function () {
        });
        $this->assertTrue($dispatcher->hasListeners('foo'));
        $this->assertTrue($tdispatcher->hasListeners('foo'));
    }
    public function testGetListenerPriority()
    {
        $dispatcher = new \MolliePrefix\Symfony\Component\EventDispatcher\EventDispatcher();
        $tdispatcher = new \MolliePrefix\Symfony\Component\EventDispatcher\Debug\TraceableEventDispatcher($dispatcher, new \MolliePrefix\Symfony\Component\Stopwatch\Stopwatch());
        $tdispatcher->addListener('foo', function () {
        }, 123);
        $listeners = $dispatcher->getListeners('foo');
        $this->assertSame(123, $tdispatcher->getListenerPriority('foo', $listeners[0]));
        // Verify that priority is preserved when listener is removed and re-added
        // in preProcess() and postProcess().
        $tdispatcher->dispatch('foo', new \MolliePrefix\Symfony\Component\EventDispatcher\Event());
        $listeners = $dispatcher->getListeners('foo');
        $this->assertSame(123, $tdispatcher->getListenerPriority('foo', $listeners[0]));
    }
    public function testGetListenerPriorityWhileDispatching()
    {
        $tdispatcher = new \MolliePrefix\Symfony\Component\EventDispatcher\Debug\TraceableEventDispatcher(new \MolliePrefix\Symfony\Component\EventDispatcher\EventDispatcher(), new \MolliePrefix\Symfony\Component\Stopwatch\Stopwatch());
        $priorityWhileDispatching = null;
        $listener = function () use($tdispatcher, &$priorityWhileDispatching, &$listener) {
            $priorityWhileDispatching = $tdispatcher->getListenerPriority('bar', $listener);
        };
        $tdispatcher->addListener('bar', $listener, 5);
        $tdispatcher->dispatch('bar');
        $this->assertSame(5, $priorityWhileDispatching);
    }
    public function testAddRemoveSubscriber()
    {
        $dispatcher = new \MolliePrefix\Symfony\Component\EventDispatcher\EventDispatcher();
        $tdispatcher = new \MolliePrefix\Symfony\Component\EventDispatcher\Debug\TraceableEventDispatcher($dispatcher, new \MolliePrefix\Symfony\Component\Stopwatch\Stopwatch());
        $subscriber = new \MolliePrefix\Symfony\Component\EventDispatcher\Tests\Debug\EventSubscriber();
        $tdispatcher->addSubscriber($subscriber);
        $listeners = $dispatcher->getListeners('foo');
        $this->assertCount(1, $listeners);
        $this->assertSame([$subscriber, 'call'], $listeners[0]);
        $tdispatcher->removeSubscriber($subscriber);
        $this->assertCount(0, $dispatcher->getListeners('foo'));
    }
    public function testGetCalledListeners()
    {
        $tdispatcher = new \MolliePrefix\Symfony\Component\EventDispatcher\Debug\TraceableEventDispatcher(new \MolliePrefix\Symfony\Component\EventDispatcher\EventDispatcher(), new \MolliePrefix\Symfony\Component\Stopwatch\Stopwatch());
        $tdispatcher->addListener('foo', function () {
        }, 5);
        $listeners = $tdispatcher->getNotCalledListeners();
        $this->assertArrayHasKey('stub', $listeners[0]);
        unset($listeners[0]['stub']);
        $this->assertEquals([], $tdispatcher->getCalledListeners());
        $this->assertEquals([['event' => 'foo', 'pretty' => 'closure', 'priority' => 5]], $listeners);
        $tdispatcher->dispatch('foo');
        $listeners = $tdispatcher->getCalledListeners();
        $this->assertArrayHasKey('stub', $listeners[0]);
        unset($listeners[0]['stub']);
        $this->assertEquals([['event' => 'foo', 'pretty' => 'closure', 'priority' => 5]], $listeners);
        $this->assertEquals([], $tdispatcher->getNotCalledListeners());
    }
    public function testClearCalledListeners()
    {
        $tdispatcher = new \MolliePrefix\Symfony\Component\EventDispatcher\Debug\TraceableEventDispatcher(new \MolliePrefix\Symfony\Component\EventDispatcher\EventDispatcher(), new \MolliePrefix\Symfony\Component\Stopwatch\Stopwatch());
        $tdispatcher->addListener('foo', function () {
        }, 5);
        $tdispatcher->dispatch('foo');
        $tdispatcher->reset();
        $listeners = $tdispatcher->getNotCalledListeners();
        $this->assertArrayHasKey('stub', $listeners[0]);
        unset($listeners[0]['stub']);
        $this->assertEquals([], $tdispatcher->getCalledListeners());
        $this->assertEquals([['event' => 'foo', 'pretty' => 'closure', 'priority' => 5]], $listeners);
    }
    public function testDispatchAfterReset()
    {
        $tdispatcher = new \MolliePrefix\Symfony\Component\EventDispatcher\Debug\TraceableEventDispatcher(new \MolliePrefix\Symfony\Component\EventDispatcher\EventDispatcher(), new \MolliePrefix\Symfony\Component\Stopwatch\Stopwatch());
        $tdispatcher->addListener('foo', function () {
        }, 5);
        $tdispatcher->reset();
        $tdispatcher->dispatch('foo');
        $listeners = $tdispatcher->getCalledListeners();
        $this->assertArrayHasKey('stub', $listeners[0]);
    }
    public function testGetCalledListenersNested()
    {
        $tdispatcher = null;
        $dispatcher = new \MolliePrefix\Symfony\Component\EventDispatcher\Debug\TraceableEventDispatcher(new \MolliePrefix\Symfony\Component\EventDispatcher\EventDispatcher(), new \MolliePrefix\Symfony\Component\Stopwatch\Stopwatch());
        $dispatcher->addListener('foo', function (\MolliePrefix\Symfony\Component\EventDispatcher\Event $event, $eventName, $dispatcher) use(&$tdispatcher) {
            $tdispatcher = $dispatcher;
            $dispatcher->dispatch('bar');
        });
        $dispatcher->addListener('bar', function (\MolliePrefix\Symfony\Component\EventDispatcher\Event $event) {
        });
        $dispatcher->dispatch('foo');
        $this->assertSame($dispatcher, $tdispatcher);
        $this->assertCount(2, $dispatcher->getCalledListeners());
    }
    public function testLogger()
    {
        $logger = new \MolliePrefix\Symfony\Component\Debug\BufferingLogger();
        $dispatcher = new \MolliePrefix\Symfony\Component\EventDispatcher\EventDispatcher();
        $tdispatcher = new \MolliePrefix\Symfony\Component\EventDispatcher\Debug\TraceableEventDispatcher($dispatcher, new \MolliePrefix\Symfony\Component\Stopwatch\Stopwatch(), $logger);
        $tdispatcher->addListener('foo', $listener1 = function () {
        });
        $tdispatcher->addListener('foo', $listener2 = function () {
        });
        $tdispatcher->dispatch('foo');
        $this->assertSame([['debug', 'Notified event "{event}" to listener "{listener}".', ['event' => 'foo', 'listener' => 'closure']], ['debug', 'Notified event "{event}" to listener "{listener}".', ['event' => 'foo', 'listener' => 'closure']]], $logger->cleanLogs());
    }
    public function testLoggerWithStoppedEvent()
    {
        $logger = new \MolliePrefix\Symfony\Component\Debug\BufferingLogger();
        $dispatcher = new \MolliePrefix\Symfony\Component\EventDispatcher\EventDispatcher();
        $tdispatcher = new \MolliePrefix\Symfony\Component\EventDispatcher\Debug\TraceableEventDispatcher($dispatcher, new \MolliePrefix\Symfony\Component\Stopwatch\Stopwatch(), $logger);
        $tdispatcher->addListener('foo', $listener1 = function (\MolliePrefix\Symfony\Component\EventDispatcher\Event $event) {
            $event->stopPropagation();
        });
        $tdispatcher->addListener('foo', $listener2 = function () {
        });
        $tdispatcher->dispatch('foo');
        $this->assertSame([['debug', 'Notified event "{event}" to listener "{listener}".', ['event' => 'foo', 'listener' => 'closure']], ['debug', 'Listener "{listener}" stopped propagation of the event "{event}".', ['event' => 'foo', 'listener' => 'closure']], ['debug', 'Listener "{listener}" was not called for event "{event}".', ['event' => 'foo', 'listener' => 'closure']]], $logger->cleanLogs());
    }
    public function testDispatchCallListeners()
    {
        $called = [];
        $dispatcher = new \MolliePrefix\Symfony\Component\EventDispatcher\EventDispatcher();
        $tdispatcher = new \MolliePrefix\Symfony\Component\EventDispatcher\Debug\TraceableEventDispatcher($dispatcher, new \MolliePrefix\Symfony\Component\Stopwatch\Stopwatch());
        $tdispatcher->addListener('foo', function () use(&$called) {
            $called[] = 'foo1';
        }, 10);
        $tdispatcher->addListener('foo', function () use(&$called) {
            $called[] = 'foo2';
        }, 20);
        $tdispatcher->dispatch('foo');
        $this->assertSame(['foo2', 'foo1'], $called);
    }
    public function testDispatchNested()
    {
        $dispatcher = new \MolliePrefix\Symfony\Component\EventDispatcher\Debug\TraceableEventDispatcher(new \MolliePrefix\Symfony\Component\EventDispatcher\EventDispatcher(), new \MolliePrefix\Symfony\Component\Stopwatch\Stopwatch());
        $loop = 1;
        $dispatchedEvents = 0;
        $dispatcher->addListener('foo', $listener1 = function () use($dispatcher, &$loop) {
            ++$loop;
            if (2 == $loop) {
                $dispatcher->dispatch('foo');
            }
        });
        $dispatcher->addListener('foo', function () use(&$dispatchedEvents) {
            ++$dispatchedEvents;
        });
        $dispatcher->dispatch('foo');
        $this->assertSame(2, $dispatchedEvents);
    }
    public function testDispatchReusedEventNested()
    {
        $nestedCall = \false;
        $dispatcher = new \MolliePrefix\Symfony\Component\EventDispatcher\Debug\TraceableEventDispatcher(new \MolliePrefix\Symfony\Component\EventDispatcher\EventDispatcher(), new \MolliePrefix\Symfony\Component\Stopwatch\Stopwatch());
        $dispatcher->addListener('foo', function (\MolliePrefix\Symfony\Component\EventDispatcher\Event $e) use($dispatcher) {
            $dispatcher->dispatch('bar', $e);
        });
        $dispatcher->addListener('bar', function (\MolliePrefix\Symfony\Component\EventDispatcher\Event $e) use(&$nestedCall) {
            $nestedCall = \true;
        });
        $this->assertFalse($nestedCall);
        $dispatcher->dispatch('foo');
        $this->assertTrue($nestedCall);
    }
    public function testListenerCanRemoveItselfWhenExecuted()
    {
        $eventDispatcher = new \MolliePrefix\Symfony\Component\EventDispatcher\Debug\TraceableEventDispatcher(new \MolliePrefix\Symfony\Component\EventDispatcher\EventDispatcher(), new \MolliePrefix\Symfony\Component\Stopwatch\Stopwatch());
        $listener1 = function ($event, $eventName, \MolliePrefix\Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher) use(&$listener1) {
            $dispatcher->removeListener('foo', $listener1);
        };
        $eventDispatcher->addListener('foo', $listener1);
        $eventDispatcher->addListener('foo', function () {
        });
        $eventDispatcher->dispatch('foo');
        $this->assertCount(1, $eventDispatcher->getListeners('foo'), 'expected listener1 to be removed');
    }
}
class EventSubscriber implements \MolliePrefix\Symfony\Component\EventDispatcher\EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return ['foo' => 'call'];
    }
}
