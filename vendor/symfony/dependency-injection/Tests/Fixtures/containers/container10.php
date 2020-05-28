<?php

namespace _PhpScoper5ece82d7231e4;

require_once __DIR__ . '/../includes/classes.php';
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ContainerBuilder;
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Reference;
$container = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ContainerBuilder();
$container->register('foo', 'FooClass')->addArgument(new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Reference('bar'))->setPublic(\true);
return $container;
