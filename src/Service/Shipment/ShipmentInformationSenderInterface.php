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

namespace Mollie\Service\Shipment;

use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\MollieApiClient;
use Order;

interface ShipmentInformationSenderInterface
{
    /**
     * @param MollieApiClient|null $apiGateway
     * @param Order $order
     *
     * @returns void
     *
     * @throws ApiException
     * @throws \Exception
     */
    public function sendShipmentInformation($apiGateway, Order $order);
}
