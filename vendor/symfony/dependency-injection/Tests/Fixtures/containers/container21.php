<?php

namespace _PhpScoper5eddef0da618a;

use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\ContainerBuilder;
use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Definition;
$container = new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\ContainerBuilder();
$bar = new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Definition('Bar');
$bar->setConfigurator([new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Definition('Baz'), 'configureBar']);
$fooFactory = new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Definition('FooFactory');
$fooFactory->setFactory([new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Definition('Foobar'), 'createFooFactory']);
$container->register('foo', 'Foo')->setFactory([$fooFactory, 'createFoo'])->setConfigurator([$bar, 'configureFoo'])->setPublic(\true);
return $container;
