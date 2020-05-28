<?php

namespace _PhpScoper5ece82d7231e4;

use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ContainerBuilder;
$container = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ContainerBuilder();
$container->register('foo', '_PhpScoper5ece82d7231e4\\FooClass\\Foo')->setDecoratedService('bar')->setPublic(\true);
return $container;
