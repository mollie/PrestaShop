<?php

namespace _PhpScoper5eddef0da618a;

use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\ContainerBuilder;
$container = new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\ContainerBuilder();
$container->register('foo', '_PhpScoper5eddef0da618a\\FooClass\\Foo')->addArgument('foo<>&bar')->addTag('_PhpScoper5eddef0da618a\\foo"bar\\bar', ['foo' => 'foo"barřž€'])->setPublic(\true);
return $container;
