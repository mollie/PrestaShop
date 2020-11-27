<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\EventDispatcher\Debug;

use MolliePrefix\Psr\Log\LoggerInterface;
use MolliePrefix\Symfony\Component\EventDispatcher\Event;
use MolliePrefix\Symfony\Component\EventDispatcher\EventDispatcherInterface;
use MolliePrefix\Symfony\Component\EventDispatcher\EventSubscriberInterface;
use MolliePrefix\Symfony\Component\Stopwatch\Stopwatch;
/**
 * Collects some data about event listeners.
 *
 * This event dispatcher delegates the dispatching to another one.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class TraceableEventDispatcher implements \MolliePrefix\Symfony\Component\EventDispatcher\Debug\TraceableEventDispatcherInterface
{
    protected $logger;
    protected $stopwatch;
    private $callStack;
    private $dispatcher;
    private $wrappedListeners;
    public function __construct(\MolliePrefix\Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher, \MolliePrefix\Symfony\Component\Stopwatch\Stopwatch $stopwatch, \MolliePrefix\Psr\Log\LoggerInterface $logger = null)
    {
        $this->dispatcher = $dispatcher;
        $this->stopwatch = $stopwatch;
        $this->logger = $logger;
        $this->wrappedListeners = [];
    }
    /**
     * {@inheritdoc}
     */
    public function addListener($eventName, $listener, $priority = 0)
    {
        $this->dispatcher->addListener($eventName, $listener, $priority);
    }
    /**
     * {@inheritdoc}
     */
    public function addSubscriber(\MolliePrefix\Symfony\Component\EventDispatcher\EventSubscriberInterface $subscriber)
    {
        $this->dispatcher->addSubscriber($subscriber);
    }
    /**
     * {@inheritdoc}
     */
    public function removeListener($eventName, $listener)
    {
        if (isset($this->wrappedListeners[$eventName])) {
            foreach ($this->wrappedListeners[$eventName] as $index => $wrappedListener) {
                if ($wrappedListener->getWrappedListener() === $listener) {
                    $listener = $wrappedListener;
                    unset($this->wrappedListeners[$eventName][$index]);
                    break;
                }
            }
        }
        return $this->dispatcher->removeListener($eventName, $listener);
    }
    /**
     * {@inheritdoc}
     */
    public function removeSubscriber(\MolliePrefix\Symfony\Component\EventDispatcher\EventSubscriberInterface $subscriber)
    {
        return $this->dispatcher->removeSubscriber($subscriber);
    }
    /**
     * {@inheritdoc}
     */
    public function getListeners($eventName = null)
    {
        return $this->dispatcher->getListeners($eventName);
    }
    /**
     * {@inheritdoc}
     */
    public function getListenerPriority($eventName, $listener)
    {
        // we might have wrapped listeners for the event (if called while dispatching)
        // in that case get the priority by wrapper
        if (isset($this->wrappedListeners[$eventName])) {
            foreach ($this->wrappedListeners[$eventName] as $index => $wrappedListener) {
                if ($wrappedListener->getWrappedListener() === $listener) {
                    return $this->dispatcher->getListenerPriority($eventName, $wrappedListener);
                }
            }
        }
        return $this->dispatcher->getListenerPriority($eventName, $listener);
    }
    /**
     * {@inheritdoc}
     */
    public function hasListeners($eventName = null)
    {
        return $this->dispatcher->hasListeners($eventName);
    }
    /**
     * {@inheritdoc}
     */
    public function dispatch($eventName, \MolliePrefix\Symfony\Component\EventDispatcher\Event $event = null)
    {
        if (null === $this->callStack) {
            $this->callStack = new \SplObjectStorage();
        }
        if (null === $event) {
            $event = new \MolliePrefix\Symfony\Component\EventDispatcher\Event();
        }
        if (null !== $this->logger && $event->isPropagationStopped()) {
            $this->logger->debug(\sprintf('The "%s" event is already stopped. No listeners have been called.', $eventName));
        }
        $this->preProcess($eventName);
        try {
            $this->preDispatch($eventName, $event);
            try {
                $e = $this->stopwatch->start($eventName, 'section');
                try {
                    $this->dispatcher->dispatch($eventName, $event);
                } finally {
                    if ($e->isStarted()) {
                        $e->stop();
                    }
                }
            } finally {
                $this->postDispatch($eventName, $event);
            }
        } finally {
            $this->postProcess($eventName);
        }
        return $event;
    }
    /**
     * {@inheritdoc}
     */
    public function getCalledListeners()
    {
        if (null === $this->callStack) {
            return [];
        }
        $called = [];
        foreach ($this->callStack as $listener) {
            list($eventName) = $this->callStack->getInfo();
            $called[] = $listener->getInfo($eventName);
        }
        return $called;
    }
    /**
     * {@inheritdoc}
     */
    public function getNotCalledListeners()
    {
        try {
            $allListeners = $this->getListeners();
        } catch (\Exception $e) {
            if (null !== $this->logger) {
                $this->logger->info('An exception was thrown while getting the uncalled listeners.', ['exception' => $e]);
            }
            // unable to retrieve the uncalled listeners
            return [];
        }
        $calledListeners = [];
        if (null !== $this->callStack) {
            foreach ($this->callStack as $calledListener) {
                $calledListeners[] = $calledListener->getWrappedListener();
            }
        }
        $notCalled = [];
        foreach ($allListeners as $eventName => $listeners) {
            foreach ($listeners as $listener) {
                if (!\in_array($listener, $calledListeners, \true)) {
                    if (!$listener instanceof \MolliePrefix\Symfony\Component\EventDispatcher\Debug\WrappedListener) {
                        $listener = new \MolliePrefix\Symfony\Component\EventDispatcher\Debug\WrappedListener($listener, null, $this->stopwatch, $this);
                    }
                    $notCalled[] = $listener->getInfo($eventName);
                }
            }
        }
        \uasort($notCalled, [$this, 'sortNotCalledListeners']);
        return $notCalled;
    }
    public function reset()
    {
        $this->callStack = null;
    }
    /**
     * Proxies all method calls to the original event dispatcher.
     *
     * @param string $method    The method name
     * @param array  $arguments The method arguments
     *
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        return \call_user_func_array([$this->dispatcher, $method], $arguments);
    }
    /**
     * Called before dispatching the event.
     *
     * @param string $eventName The event name
     * @param Event  $event     The event
     */
    protected function preDispatch($eventName, \MolliePrefix\Symfony\Component\EventDispatcher\Event $event)
    {
    }
    /**
     * Called after dispatching the event.
     *
     * @param string $eventName The event name
     * @param Event  $event     The event
     */
    protected function postDispatch($eventName, \MolliePrefix\Symfony\Component\EventDispatcher\Event $event)
    {
    }
    private function preProcess($eventName)
    {
        foreach ($this->dispatcher->getListeners($eventName) as $listener) {
            $priority = $this->getListenerPriority($eventName, $listener);
            $wrappedListener = new \MolliePrefix\Symfony\Component\EventDispatcher\Debug\WrappedListener($listener instanceof \MolliePrefix\Symfony\Component\EventDispatcher\Debug\WrappedListener ? $listener->getWrappedListener() : $listener, null, $this->stopwatch, $this);
            $this->wrappedListeners[$eventName][] = $wrappedListener;
            $this->dispatcher->removeListener($eventName, $listener);
            $this->dispatcher->addListener($eventName, $wrappedListener, $priority);
            $this->callStack->attach($wrappedListener, [$eventName]);
        }
    }
    private function postProcess($eventName)
    {
        unset($this->wrappedListeners[$eventName]);
        $skipped = \false;
        foreach ($this->dispatcher->getListeners($eventName) as $listener) {
            if (!$listener instanceof \MolliePrefix\Symfony\Component\EventDispatcher\Debug\WrappedListener) {
                // #12845: a new listener was added during dispatch.
                continue;
            }
            // Unwrap listener
            $priority = $this->getListenerPriority($eventName, $listener);
            $this->dispatcher->removeListener($eventName, $listener);
            $this->dispatcher->addListener($eventName, $listener->getWrappedListener(), $priority);
            if (null !== $this->logger) {
                $context = ['event' => $eventName, 'listener' => $listener->getPretty()];
            }
            if ($listener->wasCalled()) {
                if (null !== $this->logger) {
                    $this->logger->debug('Notified event "{event}" to listener "{listener}".', $context);
                }
            } else {
                $this->callStack->detach($listener);
            }
            if (null !== $this->logger && $skipped) {
                $this->logger->debug('Listener "{listener}" was not called for event "{event}".', $context);
            }
            if ($listener->stoppedPropagation()) {
                if (null !== $this->logger) {
                    $this->logger->debug('Listener "{listener}" stopped propagation of the event "{event}".', $context);
                }
                $skipped = \true;
            }
        }
    }
    private function sortNotCalledListeners(array $a, array $b)
    {
        if (0 !== ($cmp = \strcmp($a['event'], $b['event']))) {
            return $cmp;
        }
        if (\is_int($a['priority']) && !\is_int($b['priority'])) {
            return 1;
        }
        if (!\is_int($a['priority']) && \is_int($b['priority'])) {
            return -1;
        }
        if ($a['priority'] === $b['priority']) {
            return 0;
        }
        if ($a['priority'] > $b['priority']) {
            return -1;
        }
        return 1;
    }
}
