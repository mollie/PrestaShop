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

namespace Mollie\Subscription\Validator;

use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Config\Config;
use Mollie\Repository\CarrierRepositoryInterface;
use Mollie\Subscription\Exception\CouldNotValidateSubscriptionSettings;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SubscriptionSettingsValidator
{
    /** @var ConfigurationAdapter */
    private $configuration;
    /** @var CarrierRepositoryInterface */
    private $carrierRepository;

    public function __construct(
        ConfigurationAdapter $configuration,
        CarrierRepositoryInterface $carrierRepository
    ) {
        $this->configuration = $configuration;
        $this->carrierRepository = $carrierRepository;
    }

    /**
     * @throws CouldNotValidateSubscriptionSettings
     */
    public function validate(): bool
    {
        if (!$this->isSubscriptionActive()) {
            throw CouldNotValidateSubscriptionSettings::subscriptionServiceDisabled();
        }

        if (!$this->isSubscriptionCarrierValid()) {
            throw CouldNotValidateSubscriptionSettings::subscriptionCarrierInvalid();
        }

        return true;
    }

    private function isSubscriptionActive(): bool
    {
        return (bool) $this->configuration->get(Config::MOLLIE_SUBSCRIPTION_ENABLED);
    }

    private function isSubscriptionCarrierValid(): bool
    {
        $carrierId = (int) $this->configuration->get(Config::MOLLIE_SUBSCRIPTION_ORDER_CARRIER_ID);

        /** @var \Carrier|null $carrier */
        $carrier = $this->carrierRepository->findOneBy([
            'id_carrier' => $carrierId,
            'active' => 1,
            'deleted' => 0,
        ]);

        return (bool) $carrier;
    }
}
