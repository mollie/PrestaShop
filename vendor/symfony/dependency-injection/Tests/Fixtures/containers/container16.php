<?php

namespace _PhpScoper5ea00cc67502b;

use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder;
$container = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder();
$container->register('foo', '_PhpScoper5ea00cc67502b\\FooClass\\Foo')->setDecoratedService('bar')->setPublic(\true);
return $container;
