<?php

namespace _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Loader\Configurator;

use _PhpScoper5ece82d7231e4\App\BarService;
return function (\_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $c) {
    $c->services()->set('bar', 'Class1')->set(\_PhpScoper5ece82d7231e4\App\BarService::class)->abstract(\true)->lazy()->set('foo')->parent(\_PhpScoper5ece82d7231e4\App\BarService::class)->decorate('bar', 'b', 1)->args([ref('b')])->class('Class2')->file('file.php')->parent('bar')->parent(\_PhpScoper5ece82d7231e4\App\BarService::class);
};
