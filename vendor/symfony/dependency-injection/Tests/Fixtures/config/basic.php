<?php

namespace _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Loader\Configurator;

use _PhpScoper5ea00cc67502b\App\BarService;
return function (ContainerConfigurator $c) {
    $s = $c->services();
    $s->set(BarService::class)->args([inline('FooClass')]);
};
