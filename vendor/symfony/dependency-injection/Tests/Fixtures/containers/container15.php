<?php

namespace _PhpScoper5eddef0da618a;

use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\ContainerBuilder;
$container = new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\ContainerBuilder();
$container->register('foo', '_PhpScoper5eddef0da618a\\FooClass\\Foo')->setDecoratedService('bar', 'bar.woozy')->setPublic(\true);
return $container;
