<?php

namespace Mollie\Handler\OrderState;

use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Config\Config;
use Mollie\Enum\PaymentTypeEnum;
use Mollie\Handler\Api\OrderEndpointPaymentTypeHandlerInterface;
use Mollie\Provider\OrderState\OrderStateAutomaticShipmentSenderStatusesProviderInterface;
use Mollie\Repository\PaymentMethodRepositoryInterface;
use Mollie\Service\ShipmentServiceInterface;
use Order;
use OrderState;

class OrderStateShipmentSenderDecisionHandler implements OrderStateShipmentSenderDecisionHandlerInterface
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
    public function canShipmentDataBeSent(Order $order, OrderState $orderState)
    {
        if (!$this->hasShipmentInformation($order->reference)) {
            return false;
        }

        if (!$this->isAutomaticShipmentAvailable($orderState->id)) {
            return false;
        }

        if (!$this->hasPaymentInformation($order->id)) {
            return false;
        }

        if ($this->isRegularPayment($order->id)) {
            return false;
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
     * @param $orderStateId
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
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    private function hasShipmentInformation($orderReference)
    {
        return !empty($this->shipmentService->getShipmentInformation($orderReference));
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