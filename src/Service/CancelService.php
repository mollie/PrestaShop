<?php

namespace Mollie\Service;

use Mollie;
use Mollie\Api\Resources\Payment;
use Mollie\Utility\EnvironmentUtility;
use MollieWebhookModuleFrontController;
use Tools;

class CancelService
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
    public function doCancelOrderLines($transactionId, $lines = [])
    {
        try {
            /** @var \Mollie\Api\Resources\Order $payment */
            $order = $this->module->api->orders->get($transactionId, ['embed' => 'payments']);
            if ($lines === []) {
                $order->cancel();
            } else {
                $cancelableLines = [];
                foreach ($lines as $line) {
                    $cancelableLines[] = ['id' => $line['id'], 'quantity' => $line['quantity']];
                }
                $order->cancelLines(['lines' => $cancelableLines]);
            }

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
        } catch (\Mollie\Api\Exceptions\ApiException $e) {
            return [
                'success' => false,
                'message' => $this->l('The product(s) could not be canceled!'),
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