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

use MolliePrefix\Symfony\Component\EventDispatcher\Event;
use MolliePrefix\Symfony\Component\EventDispatcher\EventDispatcherInterface;
use MolliePrefix\Symfony\Component\Stopwatch\Stopwatch;
use MolliePrefix\Symfony\Component\VarDumper\Caster\ClassStub;
/**
 * @author Fabien Potencier <fabien@symfony.com>
 */
class WrappedListener
{
    private $listener;
    private $name;
    private $called;
    private $stoppedPropagation;
    private $stopwatch;
    private $dispatcher;
    private $pretty;
    private $stub;
    private $priority;
    private static $hasClassStub;
    public function __construct($listener, $name, \MolliePrefix\Symfony\Component\Stopwatch\Stopwatch $stopwatch, \MolliePrefix\Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher = null)
    {
        $this->listener = $listener;
        $this->stopwatch = $stopwatch;
        $this->dispatcher = $dispatcher;
        $this->called = \false;
        $this->stoppedPropagation = \false;
        if (\is_array($listener)) {
            $this->name = \is_object($listener[0]) ? \get_class($listener[0]) : $listener[0];
            $this->pretty = $this->name . '::' . $listener[1];
        } elseif ($listener instanceof \Closure) {
            $r = new \ReflectionFunction($listener);
            if (\false !== \strpos($r->name, '{closure}')) {
                $this->pretty = $this->name = 'closure';
            } elseif ($class = $r->getClosureScopeClass()) {
                $this->name = $class->name;
                $this->pretty = $this->name . '::' . $r->name;
            } else {
                $this->pretty = $this->name = $r->name;
            }
        } elseif (\is_string($listener)) {
            $this->pretty = $this->name = $listener;
        } else {
            $this->name = \get_class($listener);
            $this->pretty = $this->name . '::__invoke';
        }
        if (null !== $name) {
            $this->name = $name;
        }
        if (null === self::$hasClassStub) {
            self::$hasClassStub = \class_exists(\MolliePrefix\Symfony\Component\VarDumper\Caster\ClassStub::class);
        }
    }
    public function getWrappedListener()
    {
        return $this->listener;
    }
    public function wasCalled()
    {
        return $this->called;
    }
    public function stoppedPropagation()
    {
        return $this->stoppedPropagation;
    }
    public function getPretty()
    {
        return $this->pretty;
    }
    public function getInfo($eventName)
    {
        if (null === $this->stub) {
            $this->stub = self::$hasClassStub ? new \MolliePrefix\Symfony\Component\VarDumper\Caster\ClassStub($this->pretty . '()', $this->listener) : $this->pretty . '()';
        }
        return ['event' => $eventName, 'priority' => null !== $this->priority ? $this->priority : (null !== $this->dispatcher ? $this->dispatcher->getListenerPriority($eventName, $this->listener) : null), 'pretty' => $this->pretty, 'stub' => $this->stub];
    }
    public function __invoke(\MolliePrefix\Symfony\Component\EventDispatcher\Event $event, $eventName, \MolliePrefix\Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher)
    {
        $dispatcher = $this->dispatcher ?: $dispatcher;
        $this->called = \true;
        $this->priority = $dispatcher->getListenerPriority($eventName, $this->listener);
        $e = $this->stopwatch->start($this->name, 'event_listener');
        \call_user_func($this->listener, $event, $eventName, $dispatcher);
        if ($e->isStarted()) {
            $e->stop();
        }
        if ($event->isPropagationStopped()) {
            $this->stoppedPropagation = \true;
        }
    }
}
