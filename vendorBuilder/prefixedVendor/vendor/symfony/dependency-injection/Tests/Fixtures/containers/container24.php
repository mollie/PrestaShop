<?php

namespace MolliePrefix;

use MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder;
$container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
$container->register('foo', 'Foo')->setAutowired(\true)->setPublic(\true);
return $container;
