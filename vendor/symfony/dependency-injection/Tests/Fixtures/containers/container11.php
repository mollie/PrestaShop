<?php

namespace _PhpScoper5ea00cc67502b;

use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Definition;
$container = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder();
$container->register('foo', 'FooClass')->addArgument(new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Definition('BarClass', [new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Definition('BazClass')]))->setPublic(\true);
return $container;
