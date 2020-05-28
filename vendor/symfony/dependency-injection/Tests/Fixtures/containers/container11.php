<?php

namespace _PhpScoper5ece82d7231e4;

use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ContainerBuilder;
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Definition;
$container = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ContainerBuilder();
$container->register('foo', 'FooClass')->addArgument(new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Definition('BarClass', [new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Definition('BazClass')]))->setPublic(\true);
return $container;
