<?php

namespace _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Tests\Fixtures\Container33;

use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ContainerBuilder;
$container = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ContainerBuilder();
$container->register(\_PhpScoper5ece82d7231e4\Foo\Foo::class)->setPublic(\true);
$container->register(\_PhpScoper5ece82d7231e4\Bar\Foo::class)->setPublic(\true);
return $container;
