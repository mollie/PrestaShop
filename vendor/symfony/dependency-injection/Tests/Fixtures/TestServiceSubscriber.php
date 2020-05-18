<?php

namespace _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures;

use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ServiceSubscriberInterface;
class TestServiceSubscriber implements \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ServiceSubscriberInterface
{
    public function __construct($container)
    {
    }
    public static function getSubscribedServices()
    {
        return [__CLASS__, '?' . \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition::class, 'bar' => \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition::class, 'baz' => '?' . \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition::class];
    }
}
