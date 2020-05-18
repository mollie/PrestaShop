<?php

namespace _PhpScoper5ea00cc67502b;

use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder;
$container = new ContainerBuilder();
$container->register('foo', '_PhpScoper5ea00cc67502b\\FooClass\\Foo')->setDecoratedService('bar', 'bar.woozy')->setPublic(true);
return $container;
