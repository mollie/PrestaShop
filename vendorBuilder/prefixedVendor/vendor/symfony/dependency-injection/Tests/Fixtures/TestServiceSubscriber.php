<?php

namespace MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures;

use MolliePrefix\Symfony\Component\DependencyInjection\ServiceSubscriberInterface;
class TestServiceSubscriber implements \MolliePrefix\Symfony\Component\DependencyInjection\ServiceSubscriberInterface
{
    public function __construct($container)
    {
    }
    public static function getSubscribedServices()
    {
        return [__CLASS__, '?' . \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition::class, 'bar' => \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition::class, 'baz' => '?' . \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition::class];
    }
}
