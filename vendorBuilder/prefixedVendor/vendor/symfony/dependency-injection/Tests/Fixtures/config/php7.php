<?php

namespace MolliePrefix\Symfony\Component\DependencyInjection\Loader\Configurator;

use MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\Prototype\Foo;
return function (\MolliePrefix\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $c) {
    $c->parameters()('foo', 'Foo')('bar', 'Bar');
    $c->services()(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\Prototype\Foo::class)->arg('$bar', ref('bar'))->public()('bar', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\Prototype\Foo::class)->call('setFoo');
};
