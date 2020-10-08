<?php

namespace MolliePrefix;

use MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder;
use MolliePrefix\Symfony\Component\DependencyInjection\Definition;
$container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
$container->register('foo', 'FooClass')->addArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\Definition('BarClass', [new \MolliePrefix\Symfony\Component\DependencyInjection\Definition('BazClass')]))->setPublic(\true);
return $container;
