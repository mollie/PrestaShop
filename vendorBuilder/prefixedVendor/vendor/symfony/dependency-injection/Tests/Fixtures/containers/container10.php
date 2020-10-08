<?php

namespace MolliePrefix;

require_once __DIR__ . '/../includes/classes.php';
use MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder;
use MolliePrefix\Symfony\Component\DependencyInjection\Reference;
$container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
$container->register('foo', 'FooClass')->addArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('bar'))->setPublic(\true);
return $container;
