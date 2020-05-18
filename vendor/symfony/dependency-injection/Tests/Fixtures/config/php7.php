<?php

namespace _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Loader\Configurator;

use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\Prototype\Foo;
return function (ContainerConfigurator $c) {
    $c->parameters()('foo', 'Foo')('bar', 'Bar');
    $c->services()(Foo::class)->arg('$bar', ref('bar'))->public()('bar', Foo::class)->call('setFoo');
};
