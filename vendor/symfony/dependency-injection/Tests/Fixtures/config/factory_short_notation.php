<?php

namespace _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Loader\Configurator;

use stdClass;

return function (ContainerConfigurator $c) {
    $c->services()->set('service', stdClass::class)->factory('factory:method');
};
