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

namespace Mollie\Service\ExpressComponent;

use Mollie\Logger\PrestaLoggerInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * ps_onepagecheckout prices a guest's carrier choice against a temporary address, stores the
 * choice under that address id and deletes the address again. Core then drops the whole
 * delivery_option map and falls back to the cheapest carrier, so the cart total the express
 * session is built from (and the order the webhook creates) ignores the carrier the shopper
 * picked. The module moves the choice onto the real address only on its own submit, which an
 * express payment never reaches.
 */
class ExpressCartCarrierService
{
    const FILE_NAME = 'ExpressCartCarrierService';

    /** @var PrestaLoggerInterface */
    private $logger;

    public function __construct(PrestaLoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Re-keys a single carrier choice stored under a deleted address onto the cart's delivery address.
     */
    public function restoreCarrierChoice(\Cart $cart): bool
    {
        $deliveryAddressId = (int) $cart->id_address_delivery;
        $deliveryOption = json_decode((string) $cart->delivery_option, true);

        if ($deliveryAddressId <= 0 || !is_array($deliveryOption) || 1 !== count($deliveryOption)) {
            return false;
        }

        $storedAddressId = (int) key($deliveryOption);
        $carrierKey = (string) current($deliveryOption);

        if ($storedAddressId === $deliveryAddressId || '' === $carrierKey || $this->addressExists($storedAddressId)) {
            return false;
        }

        $cart->setDeliveryOption([$deliveryAddressId => $carrierKey]);
        $cart->save();

        $this->logger->debug(sprintf('%s - Carrier choice moved from a deleted address to the delivery address', self::FILE_NAME), [
            'cart_id' => (int) $cart->id,
            'from_address_id' => $storedAddressId,
            'to_address_id' => $deliveryAddressId,
            'delivery_option' => $carrierKey,
        ]);

        return true;
    }

    protected function addressExists(int $addressId): bool
    {
        return (bool) \Address::addressExists($addressId);
    }
}
