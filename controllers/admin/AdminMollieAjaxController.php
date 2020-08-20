<?php

use Mollie\Builder\ApiTestFeedbackBuilder;
use Mollie\Config\Config;
use Mollie\Repository\PaymentMethodRepository;
use Mollie\Service\MolliePaymentMailService;

class AdminMollieAjaxController extends ModuleAdminController
{
    public function postProcess()
    {
        $action = Tools::getValue('action');
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
        $paymentMethodRepo = $this->module->getContainer(PaymentMethodRepository::class);
        $environment = Configuration::get(Mollie\Config\Config::MOLLIE_ENVIRONMENT);
        $methodId = $paymentMethodRepo->getPaymentMethodIdByMethodId($paymentMethod, $environment);
        $method = new MolPaymentMethod($methodId);
        switch ($paymentStatus) {
            case 'deactivate':
                $method->enabled = 0;
                break;
            case 'activate':
                $method->enabled = 1;
                break;
        }
        $method->update();

        $this->ajaxDie(json_encode(
            [
                'success' => true,
                'paymentStatus' => $method->enabled
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
        $molliePaymentMailService = $this->module->getContainer(MolliePaymentMailService::class);

        $response = $molliePaymentMailService->sendSecondChanceMail($orderId);

        $this->ajaxDie(json_encode($response));
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
        $apiTestFeedbackBuilder = $this->module->getContainer(ApiTestFeedbackBuilder::class);
        $apiTestFeedbackBuilder->setTestKey($testKey);
        $apiTestFeedbackBuilder->setLiveKey($liveKey);
        $apiKeysTestInfo = $apiTestFeedbackBuilder->buildParams();

        $this->context->smarty->assign($apiKeysTestInfo);
        $this->ajaxDie(json_encode(
            [
                'template' => $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/api_test_results.tpl')

            ]
        ));
    }

    private function closeUpgradeNotice()
    {
        Configuration::updateValue(Config::MODULE_UPGRADE_NOTICE_CLOSE_DATE, time());
    }
}
