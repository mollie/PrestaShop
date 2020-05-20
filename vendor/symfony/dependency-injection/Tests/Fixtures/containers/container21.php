<?php

namespace _PhpScoper5ea00cc67502b;

use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Definition;
$container = new ContainerBuilder();
$bar = new Definition('Bar');
$bar->setConfigurator([new Definition('Baz'), 'configureBar']);
$fooFactory = new Definition('FooFactory');
$fooFactory->setFactory([new Definition('Foobar'), 'createFooFactory']);
$container->register('foo', 'Foo')->setFactory([$fooFactory, 'createFoo'])->setConfigurator([$bar, 'configureFoo'])->setPublic(true);
return $container;
