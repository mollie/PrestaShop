<?php

namespace _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Tests\Fixtures;

use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ServiceSubscriberInterface;
class TestServiceSubscriber implements \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ServiceSubscriberInterface
{
    public function __construct($container)
    {
    }
    public static function getSubscribedServices()
    {
        return [__CLASS__, '?' . \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition::class, 'bar' => \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition::class, 'baz' => '?' . \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition::class];
    }
}
