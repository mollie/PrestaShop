<?php

namespace MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\Container33;

use MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder;
$container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
$container->register(\MolliePrefix\Foo\Foo::class)->setPublic(\true);
$container->register(\MolliePrefix\Bar\Foo::class)->setPublic(\true);
return $container;
