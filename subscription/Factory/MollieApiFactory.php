<?php

declare(strict_types=1);

namespace Mollie\Subscription\Factory;

use Module;
use Mollie;
use Mollie\Api\MollieApiClient;
use Mollie\Subscription\Config\Config;
use Mollie\Subscription\Exception\MollieModuleNotFoundException;

class MollieApiFactory
{
    public function getMollieClient(): MollieApiClient
    {
        try {
            /** @var Mollie $mollie */
            $mollie = Module::getInstanceByName(Config::MOLLIE_MODULE_NAME);
        } catch (\Exception $e) {
            throw new MollieModuleNotFoundException('Mollie is not installed');
        }

        return $mollie->getApiClient();
    }
}
