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

namespace Mollie\Service;

use Db;
use Exception;
use Mollie;
use Mollie\Api\Resources\Order as MollieOrderAlias;
use Mollie\Api\Resources\Payment as MolliePaymentAlias;
use Mollie\Api\Types\PaymentStatus;
use Mollie\Config\Config;
use Mollie\DTO\OrderData;
use Mollie\DTO\PaymentData;
use Mollie\Errors\Http\HttpStatusCode;
use Mollie\Exception\OrderCreationException;
use Mollie\Handler\ErrorHandler\ErrorHandler;
use Mollie\Handler\Exception\OrderExceptionHandler;
use MolPaymentMethod;
use PrestaShopException;

if (!defined('_PS_VERSION_')) {
    exit;
}

class MollieOrderCreationService
{
    /**
     * @var OrderExceptionHandler
     */
    private $exceptionHandler;
    /**
     * @var Mollie
     */
    private $module;

    public function __construct(OrderExceptionHandler $exceptionHandler, Mollie $module)
    {
        $this->exceptionHandler = $exceptionHandler;
        $this->module = $module;
    }

    /**
     * @param PaymentData|OrderData $data
     *
     * @return false|MollieOrderAlias|MolliePaymentAlias
     */
    public function createMollieOrder($data, MolPaymentMethod $paymentMethodObj)
    {
        try {
            $apiPayment = $this->createPayment($data, $paymentMethodObj->method);
        } catch (Exception $e) {
            if ($data instanceof OrderData) {
                $data->setDeliveryPhoneNumber(null);
                $data->setBillingPhoneNumber(null);
            }
            try {
                $apiPayment = $this->createPayment($data, $paymentMethodObj->method);
            } catch (OrderCreationException $e) {
                $errorHandler = ErrorHandler::getInstance();
                $errorHandler->handle($e, HttpStatusCode::HTTP_BAD_REQUEST, true);
            } catch (Exception $e) {
                $errorHandler = ErrorHandler::getInstance();
                $errorHandler->handle($e, HttpStatusCode::HTTP_INTERNAL_SERVER_ERROR, true);
            }
        }

        /* @phpstan-ignore-next-line */
        return $apiPayment;
    }

    /**
     * @param PaymentData|OrderData $data
     *
     * @return false|MollieOrderAlias|MolliePaymentAlias
     *
     * @throws PrestaShopException
     */
    public function createMollieApplePayDirectOrder($data, MolPaymentMethod $paymentMethodObj)
    {
        try {
            $apiPayment = $this->createPayment($data, $paymentMethodObj->method);
        } catch (OrderCreationException $e) {
            $errorHandler = ErrorHandler::getInstance();
            $errorHandler->handle($e, $e->getCode(), true);
        } catch (Exception $e) {
            $errorHandler = ErrorHandler::getInstance();
            $errorHandler->handle($e, $e->getCode(), true);
        }

        /* @phpstan-ignore-next-line */
        return $apiPayment;
    }

    /**
     * @param MolliePaymentAlias|MollieOrderAlias $apiPayment
     * @param ?int $orderId
     *
     * @throws \PrestaShopDatabaseException
     */
    public function createMolliePayment($apiPayment, int $cartId, string $orderReference, ?int $orderId = null, string $status = PaymentStatus::STATUS_OPEN): void
    {
        $mandateId = '';
        if ($apiPayment instanceof MolliePaymentAlias) {
            $mandateId = $apiPayment->mandateId;
        }

        Db::getInstance()->insert(
            'mollie_payments',
            [
                'cart_id' => (int) $cartId,
                'order_id' => (int) $orderId,
                'method' => pSQL($apiPayment->method),
                'transaction_id' => pSQL($apiPayment->id),
                'order_reference' => pSQL($orderReference),
                'bank_status' => $status,
                'mandate_id' => $mandateId,
                'created_at' => ['type' => 'sql', 'value' => 'NOW()'],
            ]
        );
    }

    public function updateMolliePaymentReference(string $transactionId, string $orderReference)
    {
        Db::getInstance()->update(
            'mollie_payments',
            [
                'order_reference' => pSQL($orderReference),
                'updated_at' => ['type' => 'sql', 'value' => 'NOW()'],
            ],
            'transaction_id = "' . pSQL($transactionId) . '"'
        );
    }

    public function addTransactionMandate(string $transactionId, string $mandateId)
    {
        Db::getInstance()->update(
            'mollie_payments',
            [
                'mandate_id' => pSQL($mandateId),
                'updated_at' => ['type' => 'sql', 'value' => 'NOW()'],
            ],
            'transaction_id = "' . pSQL($transactionId) . '"'
        );
    }

    /**
     * @param PaymentData|OrderData $data
     *
     * @return MollieOrderAlias|MolliePaymentAlias
     *
     * @throws OrderCreationException
     */
    private function createPayment($data, string $selectedApi)
    {
        $subscriptionOrder = false;

        if ($data instanceof PaymentData) {
            $subscriptionOrder = $data->isSubscriptionOrder();
        }

        $serializedData = $data->jsonSerialize();

        try {
            if (Config::MOLLIE_ORDERS_API === $selectedApi) {
                /** @var MollieOrderAlias $payment */
                $payment = $this->module->getApiClient(null, $subscriptionOrder)->orders->create($serializedData, ['embed' => 'payments']);
            } else {
                /** @var MolliePaymentAlias $payment */
                $payment = $this->module->getApiClient(null, $subscriptionOrder)->payments->create($serializedData);
            }

            return $payment;
        } catch (Exception $e) {
            throw $this->exceptionHandler->handle($e);
        }
    }
}
