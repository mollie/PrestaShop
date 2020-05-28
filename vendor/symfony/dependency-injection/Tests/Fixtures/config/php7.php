<?php

namespace _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Loader\Configurator;

use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Tests\Fixtures\Prototype\Foo;
return function (\_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $c) {
    $c->parameters()('foo', 'Foo')('bar', 'Bar');
    $c->services()(\_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Tests\Fixtures\Prototype\Foo::class)->arg('$bar', ref('bar'))->public()('bar', \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Tests\Fixtures\Prototype\Foo::class)->call('setFoo');
};
