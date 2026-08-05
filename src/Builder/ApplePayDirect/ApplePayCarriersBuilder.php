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

namespace Mollie\Builder\ApplePayDirect;

use Carrier;
use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Config\Config;
use Mollie\DTO\ApplePay\Carrier\Carrier as AppleCarrier;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ApplePayCarriersBuilder
{
    /** @var ConfigurationAdapter */
    private $configuration;

    public function __construct(ConfigurationAdapter $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @return AppleCarrier[]
     */
    public function build(array $carriers, int $idZone): array
    {
        $excludedCarrierReferences = $this->getExcludedCarrierReferences();

        $price = 0;
        $applePayCarriers = [];
        foreach ($carriers as $carrier) {
            if (in_array((int) ($carrier['id_reference'] ?? 0), $excludedCarrierReferences, true)) {
                continue;
            }

            $carrierObj = new Carrier($carrier['id_carrier']);
            if ($carrierObj->getRangeTable()) {
                $priceRanges = Carrier::getDeliveryPriceByRanges($carrierObj->getRangeTable(), (int) $carrier['id_carrier']);
                foreach ($priceRanges as $priceRange) {
                    if ((int) $priceRange['id_zone'] === $idZone) {
                        $price = (float) $priceRange['price'];
                    }
                }
            }
            $applePayCarriers[] = new AppleCarrier(
                $carrier['name'],
                $carrier['delay'],
                $carrier['id_carrier'],
                $price
            );
        }

        return $applePayCarriers;
    }

    /**
     * @return int[]
     */
    public function getExcludedCarrierReferences(): array
    {
        $excludedCarriers = json_decode(
            $this->configuration->get(Config::MOLLIE_APPLE_PAY_DIRECT_EXCLUDED_CARRIERS) ?: '[]',
            true
        );

        if (!is_array($excludedCarriers)) {
            return [];
        }

        return array_map('intval', $excludedCarriers);
    }
}
