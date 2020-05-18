<?php

namespace _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Loader\Configurator;

use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\Prototype;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\Prototype\Foo;

return function (ContainerConfigurator $c) {
    $di = $c->services()->defaults()->tag('baz');
    $di->load(Prototype::class . '\\', '../Prototype')->autoconfigure()->exclude('../Prototype/{OtherDir,BadClasses}')->factory('f')->deprecate('%service_id%')->args([0])->args([1])->autoconfigure(false)->tag('foo')->parent('foo');
    $di->set('foo')->lazy()->abstract();
    $di->get(Foo::class)->lazy(false);
};
