<?php

namespace _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\Container33;

use _PhpScoper5ea00cc67502b\Bar\Foo;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder;
$container = new ContainerBuilder();
$container->register(\_PhpScoper5ea00cc67502b\Foo\Foo::class)->setPublic(true);
$container->register(Foo::class)->setPublic(true);
return $container;
