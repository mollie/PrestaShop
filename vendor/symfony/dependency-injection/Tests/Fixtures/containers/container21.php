<?php

namespace _PhpScoper5ea00cc67502b;

use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Definition;
$container = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder();
$bar = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Definition('Bar');
$bar->setConfigurator([new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Definition('Baz'), 'configureBar']);
$fooFactory = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Definition('FooFactory');
$fooFactory->setFactory([new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Definition('Foobar'), 'createFooFactory']);
$container->register('foo', 'Foo')->setFactory([$fooFactory, 'createFoo'])->setConfigurator([$bar, 'configureFoo'])->setPublic(\true);
return $container;
