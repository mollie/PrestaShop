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

use Mollie\Adapter\Context;
use Mollie\Builder\ApiTestFeedbackBuilder;
use Mollie\Config\Config;
use Mollie\Provider\CreditCardLogoProvider;
use Mollie\Provider\TaxCalculatorProvider;
use Mollie\Repository\PaymentMethodRepository;
use Mollie\Repository\PaymentMethodRepositoryInterface;
use Mollie\Service\ApiService;
use Mollie\Service\CancelService;
use Mollie\Service\CaptureService;
use Mollie\Service\MolliePaymentMailService;
use Mollie\Service\RefundService;
use Mollie\Service\ShipService;
use Mollie\Utility\NumberUtility;
use Mollie\Utility\TimeUtility;
use Mollie\Utility\TransactionUtility;

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminMollieAjaxController extends ModuleAdminController
{
    /** @var Mollie */
    public $module;

    public function postProcess()
    {
        if (!Tools::isSubmit('ajax')) {
            return;
        }

        $action = Tools::getValue('action');

        $this->context->smarty->assign('bootstrap', true);

        switch ($action) {
            case 'togglePaymentMethod':
                $this->togglePaymentMethod();
                break;
            case 'resendPaymentMail':
                $this->resendPaymentMail();
                break;
            case 'testApiKeys':
                $this->testApiKeys();
                break;
            case 'closeUpgradeNotice':
                $this->closeUpgradeNotice();
                break;
            case 'validateLogo':
                $this->validateLogo();
                break;
            case 'updateFixedPaymentFeePrice':
                $this->updateFixedPaymentFeePrice();
                break;
            case 'refundAll':
            case 'refund':
                $this->processRefund();
                break;
            case 'shipAll':
            case 'ship':
                $this->processShip();
                break;
            case 'captureAll':
            case 'capture':
                $this->processCapture();
                break;
            case 'cancelAll':
            case 'cancel':
                $this->processCancel();
                break;
            case 'retrieve':
                $this->retrieveOrderInfo();
                break;
            default:
                break;
        }
    }

    /**
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    private function togglePaymentMethod()
    {
        $paymentMethod = Tools::getValue('paymentMethod');
        $paymentStatus = Tools::getValue('status');

        /** @var PaymentMethodRepository $paymentMethodRepo */
        $paymentMethodRepo = $this->module->getService(PaymentMethodRepository::class);
        $environment = (int) Configuration::get(Mollie\Config\Config::MOLLIE_ENVIRONMENT);
        $methodId = $paymentMethodRepo->getPaymentMethodIdByMethodId($paymentMethod, $environment);
        $method = new MolPaymentMethod($methodId);
        switch ($paymentStatus) {
            case 'deactivate':
                $method->enabled = false;
                break;
            case 'activate':
                $method->enabled = true;
                break;
        }
        $method->update();

        $this->ajaxRender(json_encode(
            [
                'success' => true,
                'paymentStatus' => (int) $method->enabled,
            ]
        ));
    }

    /**
     * @throws PrestaShopException
     */
    private function resendPaymentMail()
    {
        $orderId = Tools::getValue('id_order');

        /** @var MolliePaymentMailService $molliePaymentMailService */
        $molliePaymentMailService = $this->module->getService(MolliePaymentMailService::class);

        $response = $molliePaymentMailService->sendSecondChanceMail($orderId);

        $this->ajaxRender(json_encode($response));
    }

    /**
     * @throws PrestaShopException
     * @throws SmartyException
     */
    private function testApiKeys()
    {
        $testKey = Tools::getValue('testKey');
        $liveKey = Tools::getValue('liveKey');

        /** @var ApiTestFeedbackBuilder $apiTestFeedbackBuilder */
        $apiTestFeedbackBuilder = $this->module->getService(ApiTestFeedbackBuilder::class);
        $apiTestFeedbackBuilder->setTestKey($testKey);
        $apiTestFeedbackBuilder->setLiveKey($liveKey);
        $apiKeysTestInfo = $apiTestFeedbackBuilder->buildParams();

        $this->context->smarty->assign($apiKeysTestInfo);
        $this->ajaxRender(json_encode(
            [
                'template' => $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/api_test_results.tpl'),
            ]
        ));
    }

    private function closeUpgradeNotice()
    {
        Configuration::updateValue(Config::MOLLIE_MODULE_UPGRADE_NOTICE_CLOSE_DATE, TimeUtility::getNowTs());
    }

    private function validateLogo()
    {
        /** @var CreditCardLogoProvider $creditCardLogoProvider */
        $creditCardLogoProvider = $this->module->getService(CreditCardLogoProvider::class);
        $target_file = $creditCardLogoProvider->getLocalLogoPath();
        $isUploaded = 1;
        $imageFileType = pathinfo($target_file, PATHINFO_EXTENSION);
        $returnText = '';
        if ('jpg' !== $imageFileType && 'png' !== $imageFileType) {
            $returnText = $this->module->l('Upload a .jpg or .png file.');
            $isUploaded = 0;
        }

        if (1 === $isUploaded) {
            if (move_uploaded_file($_FILES['fileToUpload']['tmp_name'], $target_file)) {
                $returnText = basename($_FILES['fileToUpload']['name']);
            } else {
                $isUploaded = 0;
                $returnText = $this->module->l('Something went wrong when uploading your logo.');
            }
        }

        echo json_encode(['status' => $isUploaded, 'message' => $returnText]);
    }

    private function updateFixedPaymentFeePrice(): void
    {
        $paymentFeeTaxIncl = (float) Tools::getValue('paymentFeeTaxIncl');
        $paymentFeeTaxExcl = (float) Tools::getValue('paymentFeeTaxExcl');
        $taxRulesGroupId = (int) Tools::getValue('taxRulesGroupId');

        if ($taxRulesGroupId < 1) {
            $this->ajaxRender(
                json_encode([
                    'error' => true,
                    'message' => $this->module->l('Missing tax rules group ID'),
                ])
            );

            return;
        }

        /** @var TaxCalculatorProvider $taxCalculatorProvider */
        $taxCalculatorProvider = $this->module->getService(TaxCalculatorProvider::class);

        /** @var Context $context */
        $context = $this->module->getService(Context::class);

        $taxCalculator = $taxCalculatorProvider->getTaxCalculator(
            $taxRulesGroupId,
            $context->getCountryId(),
            0
        );

        if ($paymentFeeTaxIncl == 0) {
            $paymentFeeTaxIncl = $taxCalculator->addTaxes($paymentFeeTaxExcl);
        }

        if ($paymentFeeTaxExcl == 0) {
            $paymentFeeTaxExcl = $taxCalculator->removeTaxes($paymentFeeTaxIncl);
        }

        $this->ajaxRender(
            json_encode([
                'error' => false,
                'paymentFeeTaxIncl' => NumberUtility::toPrecision(
                    $paymentFeeTaxIncl,
                    NumberUtility::FLOAT_PRECISION
                ),
                'paymentFeeTaxExcl' => NumberUtility::toPrecision(
                    $paymentFeeTaxExcl,
                    NumberUtility::FLOAT_PRECISION
                ),
            ])
        );
    }

    private function processRefund(): void
    {
        try {
            $transactionId = Tools::getValue('transactionId');
            $refundAmount = (float) Tools::getValue('refundAmount') ?: null;
            $orderLineId = Tools::getValue('orderline') ?: null;

            /** @var RefundService $refundService */
            $refundService = $this->module->getService(RefundService::class);

            $status = $refundService->handleRefund($transactionId, $refundAmount, $orderLineId);

            $this->ajaxRender(json_encode($status));
        } catch (\Throwable $e) {
            $this->ajaxRender(
                json_encode([
                    'success' => false,
                    'message' => $this->module->l('An error occurred while processing the request.'),
                    'error' => $e->getMessage(),
                ])
            );
        }
    }

    private function processShip(): void
    {
        $orderId = (int) Tools::getValue('orderId');
        $orderLines = Tools::getValue('orderLines') ?: [];
        $tracking = Tools::getValue('tracking');
        $orderlineId = Tools::getValue('orderline');

        try {
            $order = new Order($orderId);

            /** @var PaymentMethodRepositoryInterface $paymentMethodRepo */
            $paymentMethodRepo = $this->module->getService(PaymentMethodRepositoryInterface::class);
            $transactionId = $paymentMethodRepo->getPaymentBy('order_id', (string) $orderId)['transaction_id'];

            /** @var ShipService $shipService */
            $shipService = $this->module->getService(ShipService::class);
            $status = $shipService->handleShip($transactionId, $orderlineId, $tracking);

            $this->ajaxRender(json_encode($status));
        } catch (\Throwable $e) {
            $this->ajaxRender(
                json_encode([
                    'success' => false,
                    'message' => $this->module->l('An error occurred while processing the request.'),
                    'error' => $e->getMessage(),
                ])
            );
        }
    }

    private function processCapture(): void
    {
        $orderId = (int) Tools::getValue('orderId');
        $captureAmount = Tools::getValue('captureAmount') ?: null;

        try {
            $order = new Order($orderId);

            /** @var PaymentMethodRepositoryInterface $paymentMethodRepo */
            $paymentMethodRepo = $this->module->getService(PaymentMethodRepositoryInterface::class);
            $transactionId = $paymentMethodRepo->getPaymentBy('order_id', (string) $orderId)['transaction_id'];

            /** @var CaptureService $captureService */
            $captureService = $this->module->getService(CaptureService::class);

            $amount = $captureAmount ? (float) $captureAmount : $order->total_paid;
            $status = $captureService->handleCapture($transactionId, $amount);

            $this->ajaxRender(json_encode($status));
        } catch (\Throwable $e) {
            $this->ajaxRender(
                json_encode([
                    'success' => false,
                    'message' => $this->module->l('An error occurred while processing the request.'),
                    'error' => $e->getMessage(),
                ])
            );
        }
    }

    private function processCancel(): void
    {
        $orderId = (int) Tools::getValue('orderId');
        $orderlineId = Tools::getValue('orderline') ?: null;

        try {
            $order = new Order($orderId);

            /** @var PaymentMethodRepositoryInterface $paymentMethodRepo */
            $paymentMethodRepo = $this->module->getService(PaymentMethodRepositoryInterface::class);
            $transactionId = $paymentMethodRepo->getPaymentBy('order_id', (string) $orderId)['transaction_id'];

            /** @var CancelService $cancelService */
            $cancelService = $this->module->getService(CancelService::class);
            $status = $cancelService->handleCancel($transactionId, $orderlineId);

            $this->ajaxRender(json_encode($status));
        } catch (\Throwable $e) {
            $this->ajaxRender(
                json_encode([
                    'success' => false,
                    'message' => $this->module->l('An error occurred while processing the request.'),
                    'error' => $e->getMessage(),
                ])
            );
        }
    }

    private function retrieveOrderInfo(): void
    {
        $orderId = (int) Tools::getValue('orderId');

        try {
            $order = new Order($orderId);

            /** @var PaymentMethodRepositoryInterface $paymentMethodRepo */
            $paymentMethodRepo = $this->module->getService(PaymentMethodRepositoryInterface::class);
            $transaction = $paymentMethodRepo->getPaymentBy('order_id', (string) $orderId);

            if (!$transaction) {
                $this->ajaxRender(
                    json_encode([
                        'success' => false,
                        'message' => $this->module->l('No Mollie transaction found for this order.'),
                    ])
                );

                return;
            }

            $transactionId = $transaction['transaction_id'];
            $this->module->updateApiKey((int) $order->id_shop);

            if (!$this->module->getApiClient()) {
                $this->ajaxRender(
                    json_encode([
                        'success' => false,
                        'message' => $this->module->l('Unable to connect to Mollie API.'),
                    ])
                );

                return;
            }

            /** @var ApiService $apiService */
            $apiService = $this->module->getService(ApiService::class);

            $mollieApiType = TransactionUtility::isOrderTransaction($transactionId) ? 'orders' : 'payments';

            if ($mollieApiType === 'orders') {
                $orderInfo = $this->module->getApiClient()->orders->get($transactionId);
                $isShipping = $orderInfo->status === 'completed';
                $isCaptured = $orderInfo->isPaid();
                $isRefunded = $orderInfo->amountRefunded->value > 0;
                $isCanceled = $orderInfo->status === 'canceled';

                $response = [
                    'success' => true,
                    'isShipping' => $isShipping,
                    'isCaptured' => $isCaptured,
                    'isRefunded' => $isRefunded,
                    'isCanceled' => $isCanceled,
                    'orderStatus' => $orderInfo->status ?? null,
                ];
            } else {
                $paymentInfo = $this->module->getApiClient()->payments->get($transactionId);
                $isShipping = false;
                $isCaptured = false;

                $isCaptured = $paymentInfo->isPaid();
                $isRefunded = $paymentInfo->amountRefunded->value > 0;

                $response = [
                    'success' => true,
                    'isShipping' => $isShipping,
                    'isCaptured' => $isCaptured,
                    'isRefunded' => $isRefunded,
                    'orderStatus' => $paymentInfo->status ?? null,
                ];
            }

            $this->ajaxRender(json_encode($response));
        } catch (Exception $e) {
            $this->ajaxRender(
                json_encode([
                    'success' => false,
                    'message' => $this->module->l('An error occurred while retrieving order information.'),
                    'error' => $e->getMessage(),
                ])
            );
        }
    }
}
