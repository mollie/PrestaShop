<?php

namespace MolliePrefix\Symfony\Component\DependencyInjection\Loader\Configurator;

use MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\Prototype\Foo;
return function (\MolliePrefix\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $c) {
    $c->import('basic.php');
    $s = $c->services()->defaults()->public()->private()->autoconfigure()->autowire()->tag('t', ['a' => 'b'])->bind(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\Prototype\Foo::class, ref('bar'))->private();
    $s->set(\MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\Prototype\Foo::class)->args([ref('bar')])->public();
    $s->set('bar', \MolliePrefix\Symfony\Component\DependencyInjection\Tests\Fixtures\Prototype\Foo::class)->call('setFoo')->autoconfigure(\false);
};
