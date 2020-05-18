<?php

namespace _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures;

use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ServiceSubscriberInterface;
class TestServiceSubscriber implements ServiceSubscriberInterface
{
    public function __construct($container)
    {
    }
    public static function getSubscribedServices()
    {
        return [__CLASS__, '?' . CustomDefinition::class, 'bar' => CustomDefinition::class, 'baz' => '?' . CustomDefinition::class];
    }
}
