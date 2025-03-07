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

namespace Mollie\Subscription\Provider;

use Mollie;
use Mollie\Factory\ModuleFactory;

if (!defined('_PS_VERSION_')) {
    exit;
}

class MollieModuleServiceProvider
{
    public function get(string $service)
    {
        /** @var Mollie $mollie */
        $mollie = (new ModuleFactory())->getModule();

        return $mollie->getService($service);
    }
}
