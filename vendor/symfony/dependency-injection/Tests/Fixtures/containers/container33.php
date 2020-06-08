<?php

namespace _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Fixtures\Container33;

use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\ContainerBuilder;
$container = new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\ContainerBuilder();
$container->register(\_PhpScoper5eddef0da618a\Foo\Foo::class)->setPublic(\true);
$container->register(\_PhpScoper5eddef0da618a\Bar\Foo::class)->setPublic(\true);
return $container;
