<?php

namespace _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Fixtures;

use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\ServiceSubscriberInterface;
class TestServiceSubscriber implements \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\ServiceSubscriberInterface
{
    public function __construct($container)
    {
    }
    public static function getSubscribedServices()
    {
        return [__CLASS__, '?' . \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition::class, 'bar' => \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition::class, 'baz' => '?' . \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition::class];
    }
}
