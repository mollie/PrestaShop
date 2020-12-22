<?php

namespace Mollie\Handler\Shipment;

use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Config\Config;
use Mollie\Enum\PaymentTypeEnum;
use Mollie\Exception\ShipmentCannotBeSentException;
use Mollie\Handler\Api\OrderEndpointPaymentTypeHandlerInterface;
use Mollie\Logger\ModuleLoggerInterface;
use Mollie\Service\ExceptionService;
use Mollie\Service\Shipment\ShipmentInformationSenderInterface;
use Mollie\Verification\Shipment\ShipmentVerificationInterface;
use MolliePrefix\Mollie\Api\MollieApiClient;
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

    /**
     * @var ExceptionService
     */
    private $exceptionService;

    /**
     * @var ModuleLoggerInterface
     */
    private $moduleLogger;

    public function __construct(
        ShipmentVerificationInterface $canSendShipment,
        ShipmentInformationSenderInterface $shipmentInformationSender,
        ExceptionService $exceptionService,
        ModuleLoggerInterface $moduleLogger
    ) {
        $this->canSendShipment = $canSendShipment;
        $this->shipmentInformationSender = $shipmentInformationSender;
        $this->exceptionService = $exceptionService;
        $this->moduleLogger = $moduleLogger;
    }

    /**
     * @param MollieApiClient $apiClient
     * @param Order $order
     * @param OrderState $orderState
     *
     * @return bool
     */
    public function handleShipmentSender(MollieApiClient $apiClient, Order $order, OrderState $orderState)
    {
        try {
            if (!$this->canSendShipment->verify($order, $orderState)) {
                return false;
            }
        } catch (ShipmentCannotBeSentException $exception) {
            $message = $this->exceptionService->getErrorMessageForException(
                $exception,
                $this->exceptionService->getErrorMessages(),
                ['orderReference' => $order->reference]
            );
            $this->moduleLogger->logException($exception, $message, Config::DEBUG_LOG_ALL);

            return false;
        }

        $this->shipmentInformationSender->sendShipmentInformation($apiClient, $order);

        return true;
    }
}