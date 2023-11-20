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

use Module;
use Mollie;

if (!defined('_PS_VERSION_')) {
    exit;
}

class MollieModuleServiceProvider
{
    public function get(string $service)
    {
        /** @var Mollie $mollie */
        $mollie = Module::getInstanceByName('mollie');

        return $mollie->getService($service);
    }
}
