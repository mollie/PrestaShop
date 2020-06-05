<?php

namespace _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Loader\Configurator;

use _PhpScoper5ea00cc67502b\App\BarService;
return function (\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $c) {
    $s = $c->services();
    $s->set(\_PhpScoper5ea00cc67502b\App\BarService::class)->args([inline('FooClass')]);
};
