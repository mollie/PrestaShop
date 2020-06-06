<?php

namespace _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Loader\Configurator;

use _PhpScoper5ea00cc67502b\App\BarService;
return function (\_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $c) {
    $c->services()->set('bar', 'Class1')->set(\_PhpScoper5ea00cc67502b\App\BarService::class)->abstract(\true)->lazy()->set('foo')->parent(\_PhpScoper5ea00cc67502b\App\BarService::class)->decorate('bar', 'b', 1)->args([ref('b')])->class('Class2')->file('file.php')->parent('bar')->parent(\_PhpScoper5ea00cc67502b\App\BarService::class);
};
