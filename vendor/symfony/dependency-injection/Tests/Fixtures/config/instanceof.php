<?php

namespace _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Loader\Configurator;

use _PhpScoper5ea00cc67502b\App\FooService;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\Prototype;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Fixtures\Prototype\Foo;

return function (ContainerConfigurator $c) {
    $s = $c->services();
    $s->instanceof(Foo::class)->property('p', 0)->call('setFoo', [ref('foo')])->tag('tag', ['k' => 'v'])->share(false)->lazy()->configurator('c')->property('p', 1);
    $s->load(Prototype::class . '\\', '../Prototype')->exclude('../Prototype/*/*');
    $s->set('foo', FooService::class);
};
