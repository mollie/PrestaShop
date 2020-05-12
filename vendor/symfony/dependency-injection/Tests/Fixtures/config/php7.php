<?php

namespace _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Loader\Configurator;

use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\Prototype\Foo;
return function (\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $c) {
    $c->parameters()('foo', 'Foo')('bar', 'Bar');
    $c->services()(\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\Prototype\Foo::class)->arg('$bar', ref('bar'))->public()('bar', \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\Prototype\Foo::class)->call('setFoo');
};
