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

namespace Mollie\Install;

use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Config\Config;
use OrderState;
use Validate;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * The status mapping keys survive an uninstall, so a reinstall or a module reset
 * must keep what the merchant chose instead of writing the defaults again.
 */
class OrderStatusMapping
{
    /** @var ConfigurationAdapter */
    private $configurationAdapter;

    public function __construct(ConfigurationAdapter $configurationAdapter)
    {
        $this->configurationAdapter = $configurationAdapter;
    }

    public function install(): void
    {
        $defaults = [
            Config::MOLLIE_STATUS_OPEN => $this->configurationAdapter->get(Config::MOLLIE_STATUS_AWAITING),
            Config::MOLLIE_STATUS_PAID => $this->configurationAdapter->get('PS_OS_PAYMENT'),
            Config::MOLLIE_STATUS_COMPLETED => $this->configurationAdapter->get(Config::MOLLIE_STATUS_ORDER_COMPLETED),
            Config::MOLLIE_STATUS_CANCELED => $this->configurationAdapter->get('PS_OS_CANCELED'),
            Config::MOLLIE_STATUS_EXPIRED => $this->configurationAdapter->get('PS_OS_CANCELED'),
            Config::MOLLIE_STATUS_REFUNDED => $this->configurationAdapter->get('PS_OS_REFUND'),
            Config::MOLLIE_STATUS_SHIPPING => $this->configurationAdapter->get(Config::MOLLIE_STATUS_PARTIALLY_SHIPPED),
        ];

        foreach ($defaults as $key => $defaultStateId) {
            if ($this->isSet($key)) {
                continue;
            }

            $this->configurationAdapter->updateValue($key, $defaultStateId);
        }
    }

    public function isSet(string $key, ?int $shopId = null): bool
    {
        if ($this->isSkipped($key, $shopId)) {
            return true;
        }

        $stateId = $this->configurationAdapter->getAsInteger($key, $shopId);

        return $stateId > 0 && Validate::isLoadedObject(new OrderState($stateId));
    }

    public function isSkipped(string $key, ?int $shopId = null): bool
    {
        if (!in_array($key, Config::ORDER_STATUSES_WITH_SKIP, true)) {
            return false;
        }

        return $this->configurationAdapter->has($key, $shopId)
            && $this->configurationAdapter->getAsInteger($key, $shopId) === 0;
    }
}
