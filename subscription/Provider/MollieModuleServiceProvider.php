<?php

declare(strict_types=1);

namespace Mollie\Subscription\Provider;

use Module;
use Mollie;

class MollieModuleServiceProvider
{
    public function get(string $service)
    {
        /** @var Mollie $mollie */
        $mollie = Module::getInstanceByName('mollie');

        return $mollie->getService($service);
    }
}
