<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\EventDispatcher\DependencyInjection;

use MolliePrefix\Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument;
use MolliePrefix\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder;
use MolliePrefix\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use MolliePrefix\Symfony\Component\DependencyInjection\Reference;
use MolliePrefix\Symfony\Component\EventDispatcher\EventDispatcher;
use MolliePrefix\Symfony\Component\EventDispatcher\EventSubscriberInterface;
/**
 * Compiler pass to register tagged services for an event dispatcher.
 */
class RegisterListenersPass implements \MolliePrefix\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface
{
    protected $dispatcherService;
    protected $listenerTag;
    protected $subscriberTag;
    private $hotPathEvents = [];
    private $hotPathTagName;
    /**
     * @param string $dispatcherService Service name of the event dispatcher in processed container
     * @param string $listenerTag       Tag name used for listener
     * @param string $subscriberTag     Tag name used for subscribers
     */
    public function __construct($dispatcherService = 'event_dispatcher', $listenerTag = 'kernel.event_listener', $subscriberTag = 'kernel.event_subscriber')
    {
        $this->dispatcherService = $dispatcherService;
        $this->listenerTag = $listenerTag;
        $this->subscriberTag = $subscriberTag;
    }
    public function setHotPathEvents(array $hotPathEvents, $tagName = 'container.hot_path')
    {
        $this->hotPathEvents = \array_flip($hotPathEvents);
        $this->hotPathTagName = $tagName;
        return $this;
    }
    public function process(\MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder $container)
    {
        if (!$container->hasDefinition($this->dispatcherService) && !$container->hasAlias($this->dispatcherService)) {
            return;
        }
        $definition = $container->findDefinition($this->dispatcherService);
        foreach ($container->findTaggedServiceIds($this->listenerTag, \true) as $id => $events) {
            foreach ($events as $event) {
                $priority = isset($event['priority']) ? $event['priority'] : 0;
                if (!isset($event['event'])) {
                    throw new \MolliePrefix\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException(\sprintf('Service "%s" must define the "event" attribute on "%s" tags.', $id, $this->listenerTag));
                }
                if (!isset($event['method'])) {
                    $event['method'] = 'on' . \preg_replace_callback(['/(?<=\\b)[a-z]/i', '/[^a-z0-9]/i'], function ($matches) {
                        return \strtoupper($matches[0]);
                    }, $event['event']);
                    $event['method'] = \preg_replace('/[^a-z0-9]/i', '', $event['method']);
                }
                $definition->addMethodCall('addListener', [$event['event'], [new \MolliePrefix\Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\Reference($id)), $event['method']], $priority]);
                if (isset($this->hotPathEvents[$event['event']])) {
                    $container->getDefinition($id)->addTag($this->hotPathTagName);
                }
            }
        }
        $extractingDispatcher = new \MolliePrefix\Symfony\Component\EventDispatcher\DependencyInjection\ExtractingEventDispatcher();
        foreach ($container->findTaggedServiceIds($this->subscriberTag, \true) as $id => $attributes) {
            $def = $container->getDefinition($id);
            // We must assume that the class value has been correctly filled, even if the service is created by a factory
            $class = $def->getClass();
            if (!($r = $container->getReflectionClass($class))) {
                throw new \MolliePrefix\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException(\sprintf('Class "%s" used for service "%s" cannot be found.', $class, $id));
            }
            if (!$r->isSubclassOf(\MolliePrefix\Symfony\Component\EventDispatcher\EventSubscriberInterface::class)) {
                throw new \MolliePrefix\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException(\sprintf('Service "%s" must implement interface "%s".', $id, \MolliePrefix\Symfony\Component\EventDispatcher\EventSubscriberInterface::class));
            }
            $class = $r->name;
            \MolliePrefix\Symfony\Component\EventDispatcher\DependencyInjection\ExtractingEventDispatcher::$subscriber = $class;
            $extractingDispatcher->addSubscriber($extractingDispatcher);
            foreach ($extractingDispatcher->listeners as $args) {
                $args[1] = [new \MolliePrefix\Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\Reference($id)), $args[1]];
                $definition->addMethodCall('addListener', $args);
                if (isset($this->hotPathEvents[$args[0]])) {
                    $container->getDefinition($id)->addTag($this->hotPathTagName);
                }
            }
            $extractingDispatcher->listeners = [];
        }
    }
}
/**
 * @internal
 */
class ExtractingEventDispatcher extends \MolliePrefix\Symfony\Component\EventDispatcher\EventDispatcher implements \MolliePrefix\Symfony\Component\EventDispatcher\EventSubscriberInterface
{
    public $listeners = [];
    public static $subscriber;
    public function addListener($eventName, $listener, $priority = 0)
    {
        $this->listeners[] = [$eventName, $listener[1], $priority];
    }
    public static function getSubscribedEvents()
    {
        $callback = [self::$subscriber, 'getSubscribedEvents'];
        return $callback();
    }
}
