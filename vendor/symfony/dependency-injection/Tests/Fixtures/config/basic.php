<?php

namespace _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Loader\Configurator;

use _PhpScoper5ece82d7231e4\App\BarService;
return function (\_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $c) {
    $s = $c->services();
    $s->set(\_PhpScoper5ece82d7231e4\App\BarService::class)->args([inline('FooClass')]);
};
