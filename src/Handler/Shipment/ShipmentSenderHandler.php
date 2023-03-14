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
use Mollie\Exception\ShipmentCannotBeSentException;
use Mollie\Service\ExceptionService;
use Mollie\Service\Shipment\ShipmentInformationSenderInterface;
use Mollie\Verification\Shipment\ShipmentVerificationInterface;
use Order;
use OrderState;
use Psr\Log\LoggerInterface;

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
     * @var LoggerInterface
     */
    private $moduleLogger;

    public function __construct(
        ShipmentVerificationInterface $canSendShipment,
        ShipmentInformationSenderInterface $shipmentInformationSender,
        ExceptionService $exceptionService,
        LoggerInterface $moduleLogger
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
            $this->shipmentInformationSender->sendShipmentInformation($apiClient, $order);
        } catch (ShipmentCannotBeSentException $exception) {
            $message = $this->exceptionService->getErrorMessageForException(
                $exception,
                $this->exceptionService->getErrorMessages(),
                ['orderReference' => $order->reference]
            );
            $this->moduleLogger->error($message);

            return false;
        } catch (\Exception $exception) {
            $this->moduleLogger->error($exception->getMessage());

            return false;
        }

        return true;
    }
}
