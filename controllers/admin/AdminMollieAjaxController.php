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

use Mollie\Builder\ApiTestFeedbackBuilder;
use Mollie\Config\Config;
use Mollie\Provider\CreditCardLogoProvider;
use Mollie\Repository\PaymentMethodRepository;
use Mollie\Repository\TaxRuleRepositoryInterface;
use Mollie\Service\MolliePaymentMailService;
use Mollie\Utility\TaxUtility;
use Mollie\Utility\TimeUtility;

class AdminMollieAjaxController extends ModuleAdminController
{
    /** @var Mollie */
    public $module;

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
            case 'validateLogo':
                $this->validateLogo();
                break;
            case 'updateFixedPaymentFeePrice':
                $this->updateFixedPaymentFeePrice();
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

        $this->ajaxDie(json_encode(
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
        $apiTestFeedbackBuilder = $this->module->getService(ApiTestFeedbackBuilder::class);
        $apiTestFeedbackBuilder->setTestKey($testKey);
        $apiTestFeedbackBuilder->setLiveKey($liveKey);
        $apiKeysTestInfo = $apiTestFeedbackBuilder->buildParams();

        $this->context->smarty->assign($apiKeysTestInfo);
        $this->ajaxDie(json_encode(
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
        // Check image format
        if ('jpg' !== $imageFileType && 'png' !== $imageFileType) {
            $returnText = $this->l('Upload a .jpg or .png file.');
            $isUploaded = 0;
        }

        if (1 === $isUploaded) {
            //  if everything is ok, try to upload file
            if (move_uploaded_file($_FILES['fileToUpload']['tmp_name'], $target_file)) {
                $returnText = basename($_FILES['fileToUpload']['name']);
            } else {
                $isUploaded = 0;
                $returnText = $this->l('Something went wrong when uploading your logo.');
            }
        }

        echo json_encode(['status' => $isUploaded, 'message' => $returnText]);
    }

    private function updateFixedPaymentFeePrice(): void
    {
        $paymentFeeTaxIncl = (float) Tools::getValue('paymentFeeTaxIncl');
        $paymentFeeTaxExcl = (float) Tools::getValue('paymentFeeTaxExcl');

        $taxRuleId = (int) Tools::getValue('taxRuleId');

        if ($paymentFeeTaxIncl === 0.00 && $paymentFeeTaxExcl === 0.00) {
            $this->ajaxRender(
                json_encode([
                    'error' => true,
                    'message' => $this->module->l('No fee was submitted.'),
                ])
            );

            return;
        }

        if ($paymentFeeTaxIncl < 0.00 || $paymentFeeTaxExcl < 0.00) {
            $this->ajaxRender(
                json_encode([
                    'error' => true,
                    'message' => $this->module->l('Invalid fee'),
                ])
            );

            return;
        }

        if ($taxRuleId < 1) {
            $this->ajaxRender(
                json_encode([
                    'error' => true,
                    'message' => $this->module->l('Missing tax rule ID'),
                ])
            );

            return;
        }

        /** @var TaxRuleRepositoryInterface $taxRuleRepository */
        $taxRuleRepository = $this->module->getService(TaxRuleRepositoryInterface::class);

        /** @var TaxRule|null $taxRule */
        $taxRule = $taxRuleRepository->findOneBy([
            'id_tax_rules_group' => $taxRuleId,
            'id_country' => $this->context->country->id,
        ]);

        if (!$taxRule || !$taxRule->id) {
            $this->ajaxRender(
                json_encode([
                    'error' => true,
                    'message' => $this->module->l('Failed to find tax rule'),
                ])
            );

            return;
        }

        $tax = new Tax($taxRule->id_tax);

        if (!$tax->id) {
            $this->ajaxRender(
                json_encode([
                    'error' => true,
                    'message' => $this->module->l('Failed to find tax'),
                ])
            );

            return;
        }

        /** @var TaxUtility $taxUtility */
        $taxUtility = $this->module->getService(TaxUtility::class);

        if ($paymentFeeTaxIncl === 0.00) {
            $paymentFeeTaxIncl = $taxUtility->addTax($paymentFeeTaxExcl, $tax);
        }

        if ($paymentFeeTaxExcl === 0.00) {
            $paymentFeeTaxExcl = $taxUtility->removeTax($paymentFeeTaxIncl, $tax);
        }

        $this->ajaxRender(
            json_encode([
                'error' => false,
                'paymentFeeTaxIncl' => $paymentFeeTaxIncl,
                'paymentFeeTaxExcl' => $paymentFeeTaxExcl,
            ])
        );
    }
}
