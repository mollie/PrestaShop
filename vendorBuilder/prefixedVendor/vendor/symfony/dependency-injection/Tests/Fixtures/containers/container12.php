<?php

namespace MolliePrefix;

use MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder;
$container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
$container->register('foo', 'MolliePrefix\\FooClass\\Foo')->addArgument('foo<>&bar')->addTag('MolliePrefix\\foo"bar\\bar', ['foo' => 'foo"barřž€'])->setPublic(\true);
return $container;
