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

use Exception;
use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Config\Config;
use Mollie\Enum\PaymentTypeEnum;
use Mollie\Handler\Api\OrderEndpointPaymentTypeHandlerInterface;
use Mollie\Provider\Shipment\AutomaticShipmentSenderStatusesProviderInterface;
use Mollie\Repository\PaymentMethodRepositoryInterface;
use Mollie\Service\ShipmentServiceInterface;
use Order;
use OrderState;
use PrestaShopLogger;

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

    public function __construct(
        ConfigurationAdapter $configurationAdapter,
        AutomaticShipmentSenderStatusesProviderInterface $automaticShipmentSenderStatusesProvider,
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
     * {@inheritDoc}
     */
    public function verify(Order $order, OrderState $orderState): bool
    {
        /* todo: doesnt work with no tracking information. Will need to create new validation */
        //		if (!$this->hasShipmentInformation($order->reference)) {
        //			throw new ShipmentCannotBeSentException('Shipment information cannot be sent. No shipment information found by order reference', ShipmentCannotBeSentException::NO_SHIPPING_INFORMATION, $order->reference);
        //		}

        /**
         * TODO can't throw exception as this method is being called on every actionOrderStatusUpdate hook.
         * If initial order's state has available shipment, then it will fail as there is no payment inserted yet.
         * On actual failure exceptions would help us a lot, but we need to figure out how to prevent calling this service when it should not be called.
         */

        if (!$this->isAutomaticShipmentAvailable($orderState->id)) {
            return false;
        }

        if (!$this->hasPaymentInformation($order->id)) {
            return false;
        }

        if (!$this->isRegularPayment($order->id)) {
            return false;
        }

        return true;
    }

    /**
     * @param int $orderId
     *
     * @return bool
     */
    private function isRegularPayment(int $orderId): bool
    {
        $payment = $this->paymentMethodRepository->getPaymentBy('order_id', (int) $orderId);

        if (empty($payment)) {
            return false;
        }
        $paymentType = $this->endpointPaymentTypeHandler->getPaymentTypeFromTransactionId($payment['transaction_id']);

        if ((int) $paymentType !== PaymentTypeEnum::PAYMENT_TYPE_ORDER) {
            return false;
        }

        return true;
    }

    /**
     * @param int $orderStateId
     *
     * @return bool
     */
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

    /**
     * @param int $orderId
     *
     * @return bool
     */
    private function hasPaymentInformation(int $orderId): bool
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
    private function hasShipmentInformation(string $orderReference): bool
    {
        try {
            return !empty($this->shipmentService->getShipmentInformation($orderReference));
        } catch (Exception $e) {
            PrestaShopLogger::addLog($e);

            return false;
        }
    }

    /**
     * @return bool
     */
    private function isAutomaticShipmentInformationSenderEnabled(): bool
    {
        return (bool) $this->configurationAdapter->get(Config::MOLLIE_AUTO_SHIP_MAIN);
    }

    /**
     * @param int $orderStateId
     *
     * @return bool
     */
    private function isOrderStateInAutomaticShipmentSenderOrderStateList(int $orderStateId): bool
    {
        return in_array(
            (int) $orderStateId,
            array_map(
                'intval',
                $this->automaticShipmentSenderStatusesProvider->getAutomaticShipmentSenderStatuses()
            ),
            true
        );
    }
}
