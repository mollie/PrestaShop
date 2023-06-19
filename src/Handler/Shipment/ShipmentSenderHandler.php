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
use Mollie\Service\Shipment\ShipmentInformationSenderInterface;
use Mollie\Verification\Shipment\ShipmentVerificationInterface;
use Order;
use OrderState;

class ShipmentSenderHandler implements ShipmentSenderHandlerInterface
{
    /**
     * @var ShipmentVerificationInterface
     */
    private $canSendShipment;

    /**
     * @var ShipmentInformationSenderInterface
     */
    private $shipmentInformationSender;

    public function __construct(
        ShipmentVerificationInterface $canSendShipment,
        ShipmentInformationSenderInterface $shipmentInformationSender
    ) {
        $this->canSendShipment = $canSendShipment;
        $this->shipmentInformationSender = $shipmentInformationSender;
    }

    /**
     * @throws ShipmentCannotBeSentException
     * @throws ApiException
     */
    public function handleShipmentSender(?MollieApiClient $apiClient, Order $order, OrderState $orderState): void
    {
        // TODO testing doesn't make sense as we can't even see if bool has changed anything
        if (!$this->canSendShipment->verify($order, $orderState)) {
            return;
        }

        $this->shipmentInformationSender->sendShipmentInformation($apiClient, $order);
    }
}
