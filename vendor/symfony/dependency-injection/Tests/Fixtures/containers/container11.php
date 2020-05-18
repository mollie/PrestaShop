<?php

namespace _PhpScoper5ea00cc67502b;

use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Definition;
$container = new ContainerBuilder();
$container->register('foo', 'FooClass')->addArgument(new Definition('BarClass', [new Definition('BazClass')]))->setPublic(true);
return $container;
