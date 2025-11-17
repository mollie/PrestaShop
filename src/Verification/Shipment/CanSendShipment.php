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

namespace Mollie\Verification\Shipment;

use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Config\Config;
use Mollie\Enum\PaymentTypeEnum;
use Mollie\Exception\ShipmentCannotBeSentException;
use Mollie\Handler\Api\OrderEndpointPaymentTypeHandlerInterface;
use Mollie\Provider\Shipment\AutomaticShipmentSenderStatusesProviderInterface;
use Mollie\Repository\PaymentMethodRepositoryInterface;
use Mollie\Service\ShipmentServiceInterface;
use Mollie\Verification\IsPaymentInformationAvailable;
use Order;
use OrderState;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CanSendShipment implements ShipmentVerificationInterface
{
    /**
     * @var ConfigurationAdapter
     */
    private $configurationAdapter;

    /**
     * @var AutomaticShipmentSenderStatusesProviderInterface
     */
    private $automaticShipmentSenderStatusesProvider;

    /**
     * @var OrderEndpointPaymentTypeHandlerInterface
     */
    private $endpointPaymentTypeHandler;

    /**
     * @var PaymentMethodRepositoryInterface
     */
    private $paymentMethodRepository;

    /**
     * @var ShipmentServiceInterface
     */
    private $shipmentService;
    /** @var IsPaymentInformationAvailable */
    private $isPaymentInformationAvailable;

    public function __construct(
        ConfigurationAdapter $configurationAdapter,
        AutomaticShipmentSenderStatusesProviderInterface $automaticShipmentSenderStatusesProvider,
        OrderEndpointPaymentTypeHandlerInterface $endpointPaymentTypeHandler,
        PaymentMethodRepositoryInterface $paymentMethodRepository,
        ShipmentServiceInterface $shipmentService,
        IsPaymentInformationAvailable $isPaymentInformationAvailable
    ) {
        $this->automaticShipmentSenderStatusesProvider = $automaticShipmentSenderStatusesProvider;
        $this->configurationAdapter = $configurationAdapter;
        $this->endpointPaymentTypeHandler = $endpointPaymentTypeHandler;
        $this->paymentMethodRepository = $paymentMethodRepository;
        $this->shipmentService = $shipmentService;
        $this->isPaymentInformationAvailable = $isPaymentInformationAvailable;
    }

    /**
     * {@inheritDoc}
     */
    public function verify(Order $order, OrderState $orderState): bool
    {
        if (!$this->isShippingApplicable((int) $order->id)) {
            return false;
        }

        if (!$this->isAutomaticShipmentAvailable((int) $orderState->id)) {
            return false;
        }

        if (!$this->isPaymentInformationAvailable->verify((int) $order->id)) {
            throw new ShipmentCannotBeSentException('Shipment information cannot be sent. Missing payment information', ShipmentCannotBeSentException::ORDER_HAS_NO_PAYMENT_INFORMATION, $order->reference);
        }

        return true;
    }

    private function isAutomaticShipmentAvailable(int $orderStateId): bool
    {
        if (!$this->isAutomaticShipmentInformationSenderEnabled()) {
            return false;
        }

        if (!$this->isOrderStateInAutomaticShipmentSenderOrderStateList($orderStateId)) {
            return false;
        }

        return true;
    }

    private function isAutomaticShipmentInformationSenderEnabled(): bool
    {
        return (bool) $this->configurationAdapter->get(Config::MOLLIE_AUTO_SHIP_MAIN);
    }

    private function isOrderStateInAutomaticShipmentSenderOrderStateList(int $orderStateId): bool
    {
        return in_array(
            $orderStateId,
            array_map(
                'intval',
                $this->automaticShipmentSenderStatusesProvider->getAutomaticShipmentSenderStatuses()
            ),
            true
        );
    }

    private function isShippingApplicable(int $orderId): bool
    {
        $payment = $this->paymentMethodRepository->getPaymentBy('order_id', $orderId);
        $paymentType = $this->endpointPaymentTypeHandler->getPaymentTypeFromTransactionId($payment['transaction_id']);

        if ($paymentType !== PaymentTypeEnum::PAYMENT_TYPE_ORDER) {
            return false;
        }

        return true;
    }
}
