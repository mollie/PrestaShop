<?php

namespace Mollie\Verification\Shipment;

use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Config\Config;
use Mollie\Enum\PaymentTypeEnum;
use Mollie\Exception\ShipmentCannotBeSentException;
use Mollie\Handler\Api\OrderEndpointPaymentTypeHandlerInterface;
use Mollie\Provider\OrderState\OrderStateAutomaticShipmentSenderStatusesProviderInterface;
use Mollie\Repository\PaymentMethodRepositoryInterface;
use Mollie\Service\ShipmentServiceInterface;
use Order;
use OrderState;

class CanShipmentBeSent implements ShipmentVerificationInterface
{
    /**
     * @var ConfigurationAdapter
     */
    private $configurationAdapter;

    /**
     * @var OrderStateAutomaticShipmentSenderStatusesProviderInterface
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

    public function __construct(
        ConfigurationAdapter $configurationAdapter,
        OrderStateAutomaticShipmentSenderStatusesProviderInterface $automaticShipmentSenderStatusesProvider,
        OrderEndpointPaymentTypeHandlerInterface $endpointPaymentTypeHandler,
        PaymentMethodRepositoryInterface $paymentMethodRepository,
        ShipmentServiceInterface $shipmentService
    ) {
        $this->automaticShipmentSenderStatusesProvider = $automaticShipmentSenderStatusesProvider;
        $this->configurationAdapter = $configurationAdapter;
        $this->endpointPaymentTypeHandler = $endpointPaymentTypeHandler;
        $this->paymentMethodRepository = $paymentMethodRepository;
        $this->shipmentService = $shipmentService;
    }

    /**
     * @inheritDoc
     */
    public function verify(Order $order, OrderState $orderState)
    {
        if (!$this->hasShipmentInformation($order->reference)) {
            throw new ShipmentCannotBeSentException(
                'Shipment information cannot be sent. No shipment information found by order reference',
                ShipmentCannotBeSentException::NO_SHIPPING_INFORMATION,
                $order->reference
            );
        }

        if (!$this->isAutomaticShipmentAvailable($orderState->id)) {
            throw new ShipmentCannotBeSentException(
                'Shipment information cannot be sent. Automatic shipment sender is not available',
                ShipmentCannotBeSentException::AUTOMATIC_SHIPMENT_SENDER_IS_NOT_AVAILABLE,
                $order->reference
            );
        }

        if (!$this->hasPaymentInformation($order->id)) {
            throw new ShipmentCannotBeSentException(
                'Shipment information cannot be sent. Order has no payment information',
                ShipmentCannotBeSentException::ORDER_HAS_NO_PAYMENT_INFORMATION,
                $order->reference
            );
        }

        if ($this->isRegularPayment($order->id)) {
            throw new ShipmentCannotBeSentException(
                'Shipment information cannot be sent. Order has no payment information',
                ShipmentCannotBeSentException::PAYMENT_IS_REGULAR,
                $order->reference
            );
        }

        return true;
    }

    /**
     * @param int $orderId
     *
     * @return bool
     */
    private function isRegularPayment($orderId)
    {
        $payment = $this->paymentMethodRepository->getPaymentBy('order_id', (int) $orderId);

        if (empty($payment)) {
            return false;
        }
        $paymentType = $this->endpointPaymentTypeHandler->retrievePaymentTypeFromTransactionId($payment['transaction_id']);

        if ($paymentType !== PaymentTypeEnum::PAYMENT_TYPE_REGULAR) {
            return false;
        }

        return true;
    }

    /**
     * @param int $orderStateId
     *
     * @return bool
     */
    private function isAutomaticShipmentAvailable($orderStateId)
    {
        if (!$this->isAutomaticShipmentInformationSenderEnabled()) {
            return false;
        }

        if (!$this->isOrderStateInAutomaticShipmentSenderOrderStateList($orderStateId)) {
            return false;
        }

        return true;
    }


    /**
     * @param int $orderId
     *
     * @return bool
     */
    private function hasPaymentInformation($orderId)
    {
        $payment = $this->paymentMethodRepository->getPaymentBy('order_id', (int) $orderId);

        if (empty($payment)) {
            return false;
        }

        if (empty($payment['transaction_id'])) {
            return false;
        }

        return true;
    }

    /**
     * @param string $orderReference
     *
     * @return bool
     */
    private function hasShipmentInformation($orderReference)
    {
        try {
            return !empty($this->shipmentService->getShipmentInformation($orderReference));
        } catch (\Exception $e) {
            \PrestaShopLogger::addLog($e);

            return false;
        }
    }

    /**
     * @return bool
     */
    private function isAutomaticShipmentInformationSenderEnabled()
    {
        return (bool) $this->configurationAdapter->get(Config::MOLLIE_AUTO_SHIP_MAIN);
    }

    /**
     * @param int $orderStateId
     *
     * @return bool
     */
    private function isOrderStateInAutomaticShipmentSenderOrderStateList($orderStateId)
    {
        return in_array(
            (int) $orderStateId,
            array_map('intval', $this->automaticShipmentSenderStatusesProvider->provideAutomaticShipmentSenderStatuses())
        );
    }
}