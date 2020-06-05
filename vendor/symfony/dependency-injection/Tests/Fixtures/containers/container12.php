<?php

namespace _PhpScoper5ea00cc67502b;

use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder;
$container = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder();
$container->register('foo', '_PhpScoper5ea00cc67502b\\FooClass\\Foo')->addArgument('foo<>&bar')->addTag('_PhpScoper5ea00cc67502b\\foo"bar\\bar', ['foo' => 'foo"barřž€'])->setPublic(\true);
return $container;
