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

namespace Mollie\Handler\Shipment;

use Mollie\Api\MollieApiClient;
use Order;
use OrderState;

interface ShipmentSenderHandlerInterface
{
    /**
     * @param MollieApiClient $apiClient
     * @param Order $order
     * @param OrderState $orderState
     */
    public function handleShipmentSender(MollieApiClient $apiClient, Order $order, OrderState $orderState);
}
