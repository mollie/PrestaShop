<?php

namespace MolliePrefix\Symfony\Component\DependencyInjection\Loader\Configurator;

return function (\MolliePrefix\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $c) {
    $c->services()->set('parent_service', \stdClass::class)->set('child_service')->parent('parent_service')->autoconfigure(\true);
};
