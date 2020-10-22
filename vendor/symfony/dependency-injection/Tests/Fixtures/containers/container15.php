<?php

namespace MolliePrefix;

use MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder;
$container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
$container->register('foo', 'MolliePrefix\\FooClass\\Foo')->setDecoratedService('bar', 'bar.woozy')->setPublic(\true);
return $container;
