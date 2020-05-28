<?php

namespace _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Loader\Configurator;

use _PhpScoper5ece82d7231e4\App\FooService;
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Tests\Fixtures\Prototype;
return function (\_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $c) {
    $s = $c->services();
    $s->instanceof(\_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Tests\Fixtures\Prototype\Foo::class)->property('p', 0)->call('setFoo', [ref('foo')])->tag('tag', ['k' => 'v'])->share(\false)->lazy()->configurator('c')->property('p', 1);
    $s->load(\_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Tests\Fixtures\Prototype::class . '\\', '../Prototype')->exclude('../Prototype/*/*');
    $s->set('foo', \_PhpScoper5ece82d7231e4\App\FooService::class);
};
