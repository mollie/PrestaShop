<?php

namespace MolliePrefix;

use MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder;
use MolliePrefix\Symfony\Component\DependencyInjection\Reference;
$container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
$container->register('foo', 'FooClass')->addArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('bar'))->setPublic(\true);
$container->register('bar', 'BarClass')->setPublic(\true);
$container->compile();
return $container;
