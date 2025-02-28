<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 *
 * @see        https://github.com/mollie/PrestaShop
 * @codingStandardsIgnoreStart
 */

declare(strict_types=1);

namespace Mollie\Subscription\Factory;

use Mollie;
use Mollie\Api\MollieApiClient;
use Mollie\Factory\ModuleFactory;
use Mollie\Subscription\Exception\MollieModuleNotFoundException;

if (!defined('_PS_VERSION_')) {
    exit;
}

class MollieApiFactory
{
    public function getMollieClient(): ?MollieApiClient
    {
        try {
            /** @var Mollie $mollie */
            $mollie = (new ModuleFactory())->getModule();
        } catch (\Exception $e) {
            throw new MollieModuleNotFoundException('Mollie is not installed');
        }

        return $mollie->getApiClient();
    }
}
