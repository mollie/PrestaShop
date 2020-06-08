<?php

namespace _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Loader\Configurator;

use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Fixtures\Prototype;
return function (\_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $c) {
    $di = $c->services()->defaults()->tag('baz');
    $di->load(\_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Fixtures\Prototype::class . '\\', '../Prototype')->autoconfigure()->exclude('../Prototype/{OtherDir,BadClasses}')->factory('f')->deprecate('%service_id%')->args([0])->args([1])->autoconfigure(\false)->tag('foo')->parent('foo');
    $di->set('foo')->lazy()->abstract();
    $di->get(\_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Fixtures\Prototype\Foo::class)->lazy(\false);
};
