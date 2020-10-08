<?php

namespace MolliePrefix\Symfony\Component\DependencyInjection\Loader\Configurator;

use MolliePrefix\App\FooService;
use MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\Prototype;
return function (\MolliePrefix\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $c) {
    $s = $c->services();
    $s->instanceof(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\Prototype\Foo::class)->property('p', 0)->call('setFoo', [ref('foo')])->tag('tag', ['k' => 'v'])->share(\false)->lazy()->configurator('c')->property('p', 1);
    $s->load(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\Prototype::class . '\\', '../Prototype')->exclude('../Prototype/*/*');
    $s->set('foo', \MolliePrefix\App\FooService::class);
};
