<?php

namespace MolliePrefix;

use MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder;
use MolliePrefix\Symfony\Component\DependencyInjection\Definition;
$container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
$bar = new \MolliePrefix\Symfony\Component\DependencyInjection\Definition('Bar');
$bar->setConfigurator([new \MolliePrefix\Symfony\Component\DependencyInjection\Definition('Baz'), 'configureBar']);
$fooFactory = new \MolliePrefix\Symfony\Component\DependencyInjection\Definition('FooFactory');
$fooFactory->setFactory([new \MolliePrefix\Symfony\Component\DependencyInjection\Definition('Foobar'), 'createFooFactory']);
$container->register('foo', 'Foo')->setFactory([$fooFactory, 'createFoo'])->setConfigurator([$bar, 'configureFoo'])->setPublic(\true);
return $container;
