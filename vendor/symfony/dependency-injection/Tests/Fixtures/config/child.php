<?php

namespace MolliePrefix\Symfony\Component\DependencyInjection\Loader\Configurator;

use MolliePrefix\App\BarService;
return function (\MolliePrefix\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $c) {
    $c->services()->set('bar', 'Class1')->set(\MolliePrefix\App\BarService::class)->abstract(\true)->lazy()->set('foo')->parent(\MolliePrefix\App\BarService::class)->decorate('bar', 'b', 1)->args([ref('b')])->class('Class2')->file('file.php')->parent('bar')->parent(\MolliePrefix\App\BarService::class);
};
