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

use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\MollieApiClient;
use Mollie\Exception\ShipmentCannotBeSentException;
use Order;
use OrderState;

interface ShipmentSenderHandlerInterface
{
    /**
     * @throws ShipmentCannotBeSentException
     * @throws ApiException
     */
    public function handleShipmentSender(?MollieApiClient $apiClient, Order $order, OrderState $orderState);
}
