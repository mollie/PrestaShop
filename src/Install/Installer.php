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

namespace Mollie\Install;

use Configuration;
use Db;
use DbQuery;
use Exception;
use Feature;
use FeatureValue;
use Language;
use Mollie;
use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Config\Config;
use Mollie\Handler\ErrorHandler\ErrorHandler;
use Mollie\Service\OrderStateImageService;
use Mollie\Tracker\Segment;
use Mollie\Utility\MultiLangUtility;
use OrderState;
use PrestaShopDatabaseException;
use PrestaShopException;
use Tab;
use Tools;
use Validate;

class Installer implements InstallerInterface
{
    const FILE_NAME = 'Installer';

    /**
     * @var array
     */
    private $errors = [];

    /**
     * @var Mollie
     */
    private $module;

    /**
     * @var OrderStateImageService
     */
    private $imageService;

    /**
     * @var InstallerInterface
     */
    private $databaseTableInstaller;

    /**
     * @var Segment
     */
    private $segment;

    /**
     * @var ConfigurationAdapter
     */
    private $configurationAdapter;

    public function __construct(
        Mollie $module,
        OrderStateImageService $imageService,
        InstallerInterface $databaseTableInstaller,
        Segment $segment,
        ConfigurationAdapter $configurationAdapter
    ) {
        $this->module = $module;
        $this->imageService = $imageService;
        $this->databaseTableInstaller = $databaseTableInstaller;
        $this->segment = $segment;
        $this->configurationAdapter = $configurationAdapter;
    }

    public function install()
    {
        $this->segment->setMessage('Mollie installed');
        $this->segment->track();
        $errorHandler = ErrorHandler::getInstance();

        foreach (self::getHooks() as $hook) {
            if (version_compare(_PS_VERSION_, '1.7.0.0', '>=') && 'displayPaymentEU' === $hook) {
                continue;
            }

            $this->module->registerHook($hook);
        }

        try {
            $this->createMollieStatuses();
        } catch (Exception $e) {
            $errorHandler->handle($e, $e->getCode(), false);
            $this->errors[] = $this->module->l('Unable to install Mollie statuses', self::FILE_NAME);

            return false;
        }

        try {
            $this->initConfig();
        } catch (Exception $e) {
            $errorHandler->handle($e, $e->getCode(), false);
            $this->errors[] = $this->module->l('Unable to install config', self::FILE_NAME);

            return false;
        }
        try {
            $this->setDefaultCarrierStatuses();
        } catch (Exception $e) {
            $errorHandler->handle($e, $e->getCode(), false);
            $this->errors[] = $this->module->l('Unable to install default carrier statuses', self::FILE_NAME);

            return false;
        }

        $this->installSpecificTabs();

        try {
            $this->installVoucherFeatures();
        } catch (Exception $e) {
            $errorHandler->handle($e, $e->getCode(), false);
            $this->errors[] = $this->module->l('Unable to install voucher attributes', self::FILE_NAME);

            return false;
        }

        $this->copyEmailTemplates();

        return $this->databaseTableInstaller->install();
    }

    public function installSpecificTabs(): void
    {
        $this->installTab('AdminMollieModule_MTR', 'IMPROVE', 'Mollie', true, 'mollie');
        $this->installTab('AdminMollieModule', 'AdminMollieModule_MTR', 'Settings', true, 'mollie');
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public static function getHooks()
    {
        return [
            'paymentOptions',
            'displayAdminOrder',
            'displayBackOfficeHeader',
            'displayOrderConfirmation',
            'actionFrontControllerSetMedia',
            'actionEmailSendBefore',
            'actionOrderStatusUpdate',
            'displayPDFInvoice',
            'actionAdminOrdersListingFieldsModifier',
            'actionAdminControllerSetMedia',
            'actionValidateOrder',
            'actionOrderGridDefinitionModifier',
            'actionOrderGridQueryBuilderModifier',
            'displayHeader',
            'displayProductActions',
            'displayExpressCheckout',
            'actionObjectOrderPaymentAddAfter',
            'displayProductAdditionalInfo',
            'displayCustomerAccount',
        ];
    }

    /**
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    private function createPartialRefundOrderState()
    {
        if ($this->isStatusCreated(Config::MOLLIE_STATUS_PARTIAL_REFUND)) {
            return true;
        }
        $orderState = new OrderState();
        $orderState->send_email = false;
        $orderState->color = '#6F8C9F';
        $orderState->hidden = false;
        $orderState->delivery = false;
        $orderState->logable = false;
        $orderState->invoice = false;
        $orderState->module_name = $this->module->name;
        $orderState->name = MultiLangUtility::createMultiLangField('Partially refunded by Mollie');
        if ($orderState->add()) {
            $this->imageService->createOrderStateLogo($orderState->id);
        }
        $this->configurationAdapter->updateValue(Config::MOLLIE_STATUS_PARTIAL_REFUND, (int) $orderState->id);

        return true;
    }

    /**
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function createPartialShippedOrderState()
    {
        if ($this->isStatusCreated(Config::MOLLIE_STATUS_PARTIALLY_SHIPPED)) {
            return true;
        }
        $orderState = new OrderState();
        $orderState->send_email = false;
        $orderState->color = '#8A2BE2';
        $orderState->hidden = false;
        $orderState->delivery = false;
        $orderState->logable = false;
        $orderState->invoice = false;
        $orderState->module_name = $this->module->name;
        $orderState->name = MultiLangUtility::createMultiLangField('Partially shipped');

        if ($orderState->add()) {
            $this->imageService->createOrderStateLogo($orderState->id);
        }
        $this->configurationAdapter->updateValue(Config::MOLLIE_STATUS_PARTIALLY_SHIPPED, (int) $orderState->id);

        return true;
    }

    public function createMollieStatuses()
    {
        if (!$this->createPartialRefundOrderState()) {
            return false;
        }
        if (!$this->createAwaitingMollieOrderState()) {
            return false;
        }
        if (!$this->createPartialShippedOrderState()) {
            return false;
        }
        if (!$this->createOrderCompletedOrderState()) {
            return false;
        }
        if (!$this->klarnaPaymentAuthorizedState()) {
            return false;
        }
        if (!$this->klarnaPaymentShippedState()) {
            return false;
        }
        if (!$this->createChargedbackState()) {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function createAwaitingMollieOrderState()
    {
        if ($this->isStatusCreated(Config::MOLLIE_STATUS_AWAITING)) {
            return true;
        }
        $orderState = new OrderState();
        $orderState->send_email = false;
        $orderState->color = '#4169E1';
        $orderState->hidden = false;
        $orderState->delivery = false;
        $orderState->logable = false;
        $orderState->invoice = false;
        $orderState->module_name = $this->module->name;
        $orderState->name = MultiLangUtility::createMultiLangField('Awaiting Mollie payment');

        if ($orderState->add()) {
            $this->imageService->createOrderStateLogo($orderState->id);
        }
        $this->configurationAdapter->updateValue(Config::MOLLIE_STATUS_AWAITING, (int) $orderState->id);

        return true;
    }

    /**
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function createOrderCompletedOrderState()
    {
        if ($this->isStatusCreated(Config::MOLLIE_STATUS_ORDER_COMPLETED)) {
            return true;
        }
        $orderState = new OrderState();
        $orderState->send_email = false;
        $orderState->color = '#3d7d1c';
        $orderState->hidden = false;
        $orderState->delivery = false;
        $orderState->logable = false;
        $orderState->invoice = false;
        $orderState->send_email = true;
        $orderState->module_name = $this->module->name;
        $orderState->name = MultiLangUtility::createMultiLangField('Completed');

        if ($orderState->add()) {
            $this->imageService->createOrderStateLogo($orderState->id);
        }
        $this->configurationAdapter->updateValue(Config::MOLLIE_STATUS_ORDER_COMPLETED, (int) $orderState->id);

        return true;
    }

    /**
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function klarnaPaymentAuthorizedState()
    {
        if ($this->isStatusCreated(Config::MOLLIE_STATUS_KLARNA_AUTHORIZED)) {
            return true;
        }
        $orderState = new OrderState();
        $orderState->send_email = true;
        $orderState->color = '#8A2BE2';
        $orderState->hidden = false;
        $orderState->delivery = false;
        $orderState->logable = true;
        $orderState->invoice = true;
        $orderState->pdf_invoice = true;
        $orderState->paid = true;
        $orderState->send_email = true;
        $orderState->template = 'payment';
        $orderState->module_name = $this->module->name;
        $orderState->name = MultiLangUtility::createMultiLangField('Klarna payment authorized');

        if ($orderState->add()) {
            $this->imageService->createOrderStateLogo($orderState->id);
        }
        $this->configurationAdapter->updateValue(Config::MOLLIE_STATUS_KLARNA_AUTHORIZED, (int) $orderState->id);
        $this->configurationAdapter->updateValue(Config::MOLLIE_KLARNA_INVOICE_ON, Config::MOLLIE_STATUS_KLARNA_AUTHORIZED);

        return true;
    }

    /**
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function klarnaPaymentShippedState()
    {
        if ($this->isStatusCreated(Config::MOLLIE_STATUS_KLARNA_SHIPPED)) {
            return true;
        }
        $orderState = new OrderState();
        $orderState->send_email = true;
        $orderState->color = '#8A2BE2';
        $orderState->hidden = false;
        $orderState->delivery = false;
        $orderState->logable = true;
        $orderState->invoice = false;
        $orderState->shipped = true;
        $orderState->paid = true;
        $orderState->delivery = true;
        $orderState->template = 'shipped';
        $orderState->pdf_invoice = true;
        $orderState->module_name = $this->module->name;
        $orderState->name = MultiLangUtility::createMultiLangField('Klarna payment shipped');

        if ($orderState->add()) {
            $this->imageService->createOrderStateLogo($orderState->id);
        }
        $this->configurationAdapter->updateValue(Config::MOLLIE_STATUS_KLARNA_SHIPPED, (int) $orderState->id);

        return true;
    }

    /**
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function createChargedbackState()
    {
        if ($this->isStatusCreated(Config::MOLLIE_STATUS_CHARGEBACK)) {
            return true;
        }
        $orderState = new OrderState();
        $orderState->send_email = false;
        $orderState->color = '#E74C3C';
        $orderState->hidden = false;
        $orderState->delivery = false;
        $orderState->logable = false;
        $orderState->invoice = false;
        $orderState->module_name = $this->module->name;
        $orderState->name = MultiLangUtility::createMultiLangField('Mollie Chargeback');
        if ($orderState->add()) {
            $this->imageService->createOrderStateLogo($orderState->id);
        }
        $this->configurationAdapter->updateValue(Config::MOLLIE_STATUS_CHARGEBACK, (int) $orderState->id);

        return true;
    }

    /**
     * @return void
     */
    protected function initConfig()
    {
        $this->configurationAdapter->updateValue(Config::MOLLIE_API_KEY, '');
        $this->configurationAdapter->updateValue(Config::MOLLIE_API_KEY_TEST, '');
        $this->configurationAdapter->updateValue(Config::MOLLIE_ENVIRONMENT, Config::ENVIRONMENT_TEST);
        $this->configurationAdapter->updateValue(Config::MOLLIE_SEND_ORDER_CONFIRMATION, 0);
        $this->configurationAdapter->updateValue(Config::MOLLIE_PAYMENTSCREEN_LOCALE, Config::PAYMENTSCREEN_LOCALE_BROWSER_LOCALE);
        $this->configurationAdapter->updateValue(Config::MOLLIE_IFRAME, true);
        $this->configurationAdapter->updateValue(Config::MOLLIE_IMAGES, Config::LOGOS_NORMAL);
        $this->configurationAdapter->updateValue(Config::MOLLIE_ISSUERS, Config::ISSUERS_ON_CLICK);
        $this->configurationAdapter->updateValue(Config::MOLLIE_CSS, '');
        $this->configurationAdapter->updateValue(Config::MOLLIE_TRACKING_URLS, '');
        $this->configurationAdapter->updateValue(Config::MOLLIE_DEBUG_LOG, Config::DEBUG_LOG_ERRORS);
        $this->configurationAdapter->updateValue(Config::MOLLIE_METHOD_COUNTRIES, 0);
        $this->configurationAdapter->updateValue(Config::MOLLIE_METHOD_COUNTRIES_DISPLAY, 0);
        $this->configurationAdapter->updateValue(Config::MOLLIE_DISPLAY_ERRORS, false);
        $this->configurationAdapter->updateValue(Config::MOLLIE_STATUS_OPEN, $this->configurationAdapter->get(Config::MOLLIE_STATUS_AWAITING));
        $this->configurationAdapter->updateValue(Config::MOLLIE_STATUS_PAID, Configuration::get('PS_OS_PAYMENT'));
        $this->configurationAdapter->updateValue(Config::MOLLIE_STATUS_COMPLETED, $this->configurationAdapter->get(Config::MOLLIE_STATUS_ORDER_COMPLETED));
        $this->configurationAdapter->updateValue(Config::MOLLIE_STATUS_CANCELED, Configuration::get('PS_OS_CANCELED'));
        $this->configurationAdapter->updateValue(Config::MOLLIE_STATUS_EXPIRED, Configuration::get('PS_OS_CANCELED'));
        $this->configurationAdapter->updateValue(Config::MOLLIE_STATUS_REFUNDED, Configuration::get('PS_OS_REFUND'));
        $this->configurationAdapter->updateValue(Config::MOLLIE_STATUS_SHIPPING, $this->configurationAdapter->get(Config::MOLLIE_STATUS_PARTIALLY_SHIPPED));
        $this->configurationAdapter->updateValue(Config::MOLLIE_MAIL_WHEN_SHIPPING, true);
        $this->configurationAdapter->updateValue(Config::MOLLIE_MAIL_WHEN_PAID, true);
        $this->configurationAdapter->updateValue(Config::MOLLIE_MAIL_WHEN_COMPLETED, true);
        $this->configurationAdapter->updateValue(Config::MOLLIE_MAIL_WHEN_CANCELED, true);
        $this->configurationAdapter->updateValue(Config::MOLLIE_MAIL_WHEN_EXPIRED, true);
        $this->configurationAdapter->updateValue(Config::MOLLIE_MAIL_WHEN_REFUNDED, true);
        $this->configurationAdapter->updateValue(Config::MOLLIE_MAIL_WHEN_CHARGEBACK, true);
        $this->configurationAdapter->updateValue(Config::MOLLIE_ACCOUNT_SWITCH, false);
        $this->configurationAdapter->updateValue(Config::MOLLIE_CSS, '');

        $this->configurationAdapter->updateValue(Config::MOLLIE_API, Config::MOLLIE_ORDERS_API);
        $this->configurationAdapter->updateValue(Config::MOLLIE_APPLE_PAY_DIRECT_STYLE, 0);
        $this->configurationAdapter->updateValue(Config::MOLLIE_BANCONTACT_QR_CODE_ENABLED, 0);
    }

    public function setDefaultCarrierStatuses()
    {
        $sql = new DbQuery();
        $sql->select('`' . bqSQL(OrderState::$definition['primary']) . '`');
        $sql->from(bqSQL(OrderState::$definition['table']));
        $sql->where('`shipped` = 1');

        $defaultStatuses = Db::getInstance()->executeS($sql);
        if (!is_array($defaultStatuses)) {
            return;
        }
        $defaultStatuses = array_map('intval', array_column($defaultStatuses, OrderState::$definition['primary']));
        $this->configurationAdapter->updateValue(Config::MOLLIE_AUTO_SHIP_STATUSES, json_encode($defaultStatuses));
    }

    public function installTab($className, $parent, $name, $active = true, $icon = '')
    {
        $idParent = is_int($parent) ? $parent : Tab::getIdFromClassName($parent);

        $moduleTab = new Tab();
        $moduleTab->class_name = $className;
        $moduleTab->id_parent = $idParent;
        $moduleTab->module = $this->module->name;
        $moduleTab->active = $active;
        $moduleTab->icon = $icon; /** @phpstan-ignore-line */
        $languages = Language::getLanguages(true);
        foreach ($languages as $language) {
            $moduleTab->name[$language['id_lang']] = $name;
        }

        if (!$moduleTab->save()) {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    public function copyEmailTemplates()
    {
        $languages = Language::getLanguages(false);

        foreach ($languages as $language) {
            if (Config::DEFAULT_EMAIL_LANGUAGE_ISO_CODE === $language['iso_code']) {
                continue;
            }

            if (file_exists($this->module->getLocalPath() . 'mails/' . $language['iso_code'])) {
                continue;
            }

            try {
                Tools::recurseCopy(
                    $this->module->getLocalPath() . 'mails/' . Config::DEFAULT_EMAIL_LANGUAGE_ISO_CODE,
                    $this->module->getLocalPath() . 'mails/' . $language['iso_code']
                );
            } catch (PrestaShopException $e) {
                $this->errors[] = $this->module->l('Could not copy email templates:', self::FILE_NAME) . ' ' . $e->getMessage();

                return false;
            }
        }

        return true;
    }

    public function installVoucherFeatures()
    {
        $mollieVoucherId = Configuration::get(Config::MOLLIE_VOUCHER_FEATURE_ID);
        if ($mollieVoucherId) {
            $mollieFeature = new Feature((int) $mollieVoucherId);
            $doesFeatureExist = Validate::isLoadedObject($mollieFeature);
            if ($doesFeatureExist) {
                return;
            }
        }

        $feature = new Feature();
        $feature->name = MultiLangUtility::createMultiLangField('Voucher');
        $feature->add();

        foreach (Config::MOLLIE_VOUCHER_CATEGORIES as $key => $categoryName) {
            $featureValue = new FeatureValue();
            $featureValue->id_feature = $feature->id;
            $featureValue->value = MultiLangUtility::createMultiLangField($categoryName);
            $featureValue->add();
            $this->configurationAdapter->updateValue(Config::MOLLIE_VOUCHER_FEATURE . $key, $featureValue->id);
        }

        $this->configurationAdapter->updateValue(Config::MOLLIE_VOUCHER_FEATURE_ID, $feature->id);
    }

    private function isStatusCreated($statusName)
    {
        $status = new OrderState((int) $this->configurationAdapter->get($statusName));
        if (Validate::isLoadedObject($status)) {
            $this->enableStatus($status);

            return true;
        }

        return false;
    }

    /**
     * @param OrderState $orderState
     */
    private function enableStatus($orderState)
    {
        if ((bool) $orderState->deleted) {
            $orderState->deleted = false;
            $orderState->save();
        }
    }
}
