<?php

namespace _PhpScoper5eddef0da618a;

use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\ContainerBuilder;
use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference;
$container = new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\ContainerBuilder();
$container->register('foo', 'FooClass')->addArgument(new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('bar'))->setPublic(\true);
$container->register('bar', 'BarClass')->setPublic(\true);
$container->compile();
return $container;
