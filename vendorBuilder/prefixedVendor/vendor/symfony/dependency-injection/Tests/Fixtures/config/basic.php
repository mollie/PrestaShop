<?php

namespace MolliePrefix\Symfony\Component\DependencyInjection\Loader\Configurator;

use MolliePrefix\App\BarService;
return function (\MolliePrefix\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $c) {
    $s = $c->services();
    $s->set(\MolliePrefix\App\BarService::class)->args([inline('FooClass')]);
};
