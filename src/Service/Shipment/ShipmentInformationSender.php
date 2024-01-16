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

use Mollie\Api\MollieApiClient;
use Mollie\Api\Resources\Order as ApiOrder;
use Mollie\Repository\PaymentMethodRepositoryInterface;
use Mollie\Service\ShipmentServiceInterface;
use Order;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ShipmentInformationSender implements ShipmentInformationSenderInterface
{
    /**
     * @var PaymentMethodRepositoryInterface
     */
    private $paymentMethodRepository;

    /**
     * @var ShipmentServiceInterface
     */
    private $shipmentService;

    public function __construct(
        PaymentMethodRepositoryInterface $paymentMethodRepository,
        ShipmentServiceInterface $shipmentService
    ) {
        $this->paymentMethodRepository = $paymentMethodRepository;
        $this->shipmentService = $shipmentService;
    }

    /**
     * {@inheritDoc}
     */
    public function sendShipmentInformation(?MollieApiClient $apiGateway, Order $order): void
    {
        if (empty($apiGateway)) {
            return;
        }

        $payment = $this->paymentMethodRepository->getPaymentBy('order_id', (int) $order->id);

        $apiOrder = $apiGateway->orders->get($payment['transaction_id']);

        if (empty($apiOrder)) {
            return;
        }

        if (!$this->hasShippableItems($apiOrder)) {
            return;
        }

        $apiOrder->shipAll($this->shipmentService->getShipmentInformation($order->reference));
    }

    private function hasShippableItems(ApiOrder $apiOrder): bool
    {
        $shippableItems = 0;

        foreach ($apiOrder->lines as $line) {
            $shippableItems += $line->shippableQuantity;
        }

        return !empty($shippableItems);
    }
}
