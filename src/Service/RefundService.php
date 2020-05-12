<?php


namespace Mollie\Service;

use _PhpScoper5ea00cc67502b\Mollie\Api\Exceptions\ApiException;
use Mollie;
use _PhpScoper5ea00cc67502b\Mollie\Api\Resources\Order as MollieOrderAlias;
use _PhpScoper5ea00cc67502b\Mollie\Api\Resources\Payment;
use Mollie\Utility\EnvironmentUtility;
use MollieWebhookModuleFrontController;
use Tools;

class RefundService
{
    /**
     * @var Mollie
     */
    private $module;

    public function __construct(Mollie $module)
    {
        $this->module = $module;
    }

    /**
     * @param string $transactionId Transaction/Mollie Order ID
     * @param float|null $amount Amount to refund, refund all if `null`
     *
     * @return array
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     * @throws \PrestaShop\PrestaShop\Adapter\CoreException
     * @throws ApiException
     * @since 3.3.0 Renamed `doRefund` to `doPaymentRefund`, added `$amount`
     * @since 3.3.2 Omit $orderId
     */
    public function doPaymentRefund($transactionId, $amount = null)
    {
        try {
            /** @var Payment $payment */
            $payment = $this->module->api->payments->get($transactionId);
            if ($amount) {
                $payment->refund([
                    'amount' => [
                        'currency' => (string)$payment->amount->currency,
                        'value' => (string)number_format($amount, 2, '.', ''),
                    ],
                ]);
            } elseif ((float)$payment->settlementAmount->value - (float)$payment->amountRefunded->value > 0) {
                $payment->refund([
                    'amount' => [
                        'currency' => (string)$payment->amount->currency,
                        'value' => (string)number_format(((float)$payment->settlementAmount->value - (float)$payment->amountRefunded->value), 2, '.', ''),
                    ],
                ]);
            }
        } catch (ApiException $e) {
            return [
                'status' => 'fail',
                'msg_fail' => $this->module->l('The order could not be refunded!'),
                'msg_details' => $this->module->l('Reason:') . ' ' . $e->getMessage(),
            ];
        }

        if (EnvironmentUtility::isLocalEnvironment()) {
            // Refresh payment on local environments
            /** @var Payment $payment */
            $apiPayment = $this->module->api->payments->get($transactionId);
            if (!Tools::isSubmit('module')) {
                $_GET['module'] = $this->module->name;
            }
            $webhookController = new MollieWebhookModuleFrontController();
            $webhookController->processTransaction($apiPayment);
        }

        return [
            'status' => 'success',
            'msg_success' => $this->module->l('The order has been refunded!'),
            'msg_details' => $this->module->l('Mollie B.V. will transfer the money back to the customer on the next business day.'),
        ];
    }

    /**
     * @param string $transactionId
     * @param array $lines
     *
     * @return array
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     * @throws \PrestaShop\PrestaShop\Adapter\CoreException
     * @throws \SmartyException
     * @since 3.3.0
     */
    public function doRefundOrderLines($transactionId, $lines = [])
    {
        try {
            /** @var MollieOrderAlias $payment */
            $order = $this->module->api->orders->get($transactionId, ['embed' => 'payments']);
            $refund = [
                'lines' => array_map(function ($line) {
                    return array_intersect_key(
                        (array)$line,
                        array_flip([
                            'id',
                            'quantity',
                        ]));
                }, $lines),
            ];
            $order->refund($refund);

            if (EnvironmentUtility::isLocalEnvironment()) {
                // Refresh payment on local environments
                /** @var Payment $payment */
                $apiPayment = $this->module->api->orders->get($transactionId, ['embed' => 'payments']);
                if (!Tools::isSubmit('module')) {
                    $_GET['module'] = $this->module->name;
                }
                $webhookController = new MollieWebhookModuleFrontController();
                $webhookController->processTransaction($apiPayment);
            }
        } catch (ApiException $e) {
            return [
                'success' => false,
                'message' => $this->module->l('The product(s) could not be refunded!'),
                'detailed' => $e->getMessage(),
            ];
        }

        return [
            'success' => true,
            'message' => '',
            'detailed' => '',
        ];
    }

}