<?php

namespace _PhpScoper5eddef0da618a;

use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\ContainerBuilder;
use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Definition;
$container = new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\ContainerBuilder();
$container->register('foo', 'FooClass')->addArgument(new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Definition('BarClass', [new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Definition('BazClass')]))->setPublic(\true);
return $container;
