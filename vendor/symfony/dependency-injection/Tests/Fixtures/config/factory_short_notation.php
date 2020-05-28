<?php

namespace _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Loader\Configurator;

return function (\_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $c) {
    $c->services()->set('service', \stdClass::class)->factory('factory:method');
};
