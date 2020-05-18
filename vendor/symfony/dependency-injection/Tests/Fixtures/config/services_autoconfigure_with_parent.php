<?php

namespace _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Loader\Configurator;

use stdClass;

return function (ContainerConfigurator $c) {
    $c->services()->set('parent_service', stdClass::class)->set('child_service')->parent('parent_service')->autoconfigure(true);
};
