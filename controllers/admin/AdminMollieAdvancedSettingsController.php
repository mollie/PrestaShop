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

declare(strict_types=1);

use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Adapter\Language;
use Mollie\Adapter\ToolsAdapter;
use Mollie\Config\Config;
use Mollie\Service\MolCarrierInformationService;
use OrderState;

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminMollieAdvancedSettingsController extends ModuleAdminController
{
    const FILE_NAME = 'AdminMollieAdvancedSettingsController';

    /** @var Mollie */
    public $module;

    /** @var ToolsAdapter */
    private $tools;

    /** @var ConfigurationAdapter */
    private $configuration;

    /** @var Language */
    private $language;

    /** @var MolCarrierInformationService */
    private $carrierInformationService;

    public function __construct()
    {
        parent::__construct();
        $this->bootstrap = true;
        $this->context = Context::getContext();
        $this->tools = $this->module->getService(ToolsAdapter::class);
        $this->configuration = $this->module->getService(ConfigurationAdapter::class);
        $this->language = $this->module->getService(Language::class);
        $this->carrierInformationService = $this->module->getService(MolCarrierInformationService::class);
    }

    public function init(): void
    {
        parent::init();

        $version = time();

        $this->context->controller->addCSS(
            $this->module->getPathUri() . 'views/js/admin/library/dist/assets/globals.css?v=' . $version,
            'all',
            null,
            false
        );

        $this->context->controller->addCSS(
            $this->module->getPathUri() . 'views/js/admin/library/dist/assets/mollie-advanced-settings.css?v=' . $version,
            'all',
            null,
            false
        );

        $jsUrl = $this->module->getPathUri() . 'views/js/admin/library/dist/assets/mollie-advanced-settings.js?v=' . $version;
        $this->context->smarty->assign('mollieAdvancedSettingsJsUrl', $jsUrl);

        Media::addJsDef([
            'mollieAdvancedSettingsAjaxUrl' => $this->context->link->getAdminLink('AdminMollieAdvancedSettings'),
        ]);

        Media::addJsDef([
            'mollieAdvancedSettingsTranslations' => $this->getTranslations(),
        ]);

        $this->content = $this->context->smarty->fetch(
            $this->module->getLocalPath() . 'views/templates/admin/advanced-settings/advanced-settings.tpl'
        );
    }

    private function getTranslations(): array
    {
        return [
            'advancedSettings' => $this->module->l('Advanced Settings', self::FILE_NAME),
            'orderSettings' => $this->module->l('Order Settings', self::FILE_NAME),
            'shippingSettings' => $this->module->l('Shipping Settings', self::FILE_NAME),
            'errorDebugging' => $this->module->l('Error Debugging', self::FILE_NAME),
            'visualSettings' => $this->module->l('Visual Settings', self::FILE_NAME),
            'orderStatusMapping' => $this->module->l('Order Status Mapping', self::FILE_NAME),
            'orderStatusEmails' => $this->module->l('Order Status Emails', self::FILE_NAME),
            'invoiceOption' => $this->module->l('Select when to create the order invoice', self::FILE_NAME),
            'confirmationEmail' => $this->module->l('Send order confirmation email', self::FILE_NAME),
            'autoShip' => $this->module->l('Automatically ship on marked statuses', self::FILE_NAME),
            'debugMode' => $this->module->l('Display errors', self::FILE_NAME),
            'logLevel' => $this->module->l('Log level', self::FILE_NAME),
            'logoDisplay' => $this->module->l('Payment Method Logo Display', self::FILE_NAME),
            'translateMollie' => $this->module->l('Use selected locale in webshop', self::FILE_NAME),
            'cssPath' => $this->module->l('CSS file', self::FILE_NAME),
            'saveSuccess' => $this->module->l('Settings saved successfully', self::FILE_NAME),
            'saveError' => $this->module->l('Failed to save settings', self::FILE_NAME),
        ];
    }

    public function displayAjax(): void
    {
        if (!$this->tools->isSubmit('ajax')) {
            return;
        }

        $action = $this->tools->getValue('action');

        switch ($action) {
            case 'getSettings':
                $this->ajaxGetSettings();
                break;
            case 'saveSettings':
                $this->ajaxSaveSettings();
                break;
            default:
                $this->ajaxRender(json_encode([
                    'success' => false,
                    'message' => 'Invalid action',
                ]));
                break;
        }
    }

    private function ajaxGetSettings(): void
    {
        try {
            // Check if API is configured
            $testApiKey = $this->configuration->get(Config::MOLLIE_API_KEY_TEST);
            $liveApiKey = $this->configuration->get(Config::MOLLIE_API_KEY);
            $environment = (int) $this->configuration->get(Config::MOLLIE_ENVIRONMENT);

            $currentApiKey = $environment ? $liveApiKey : $testApiKey;

            if (empty($currentApiKey)) {
                $this->ajaxRender(json_encode([
                    'success' => false,
                    'message' => $this->module->l('API not configured. Please configure API keys first.', self::FILE_NAME),
                    'not_configured' => true,
                ]));

                return;
            }

            $invoiceOptionRaw = $this->configuration->get(Config::MOLLIE_AUTHORIZABLE_PAYMENT_INVOICE_ON_STATUS);
            $confirmationEmailRaw = $this->configuration->get(Config::MOLLIE_SEND_ORDER_CONFIRMATION);
            $logLevelRaw = $this->configuration->get(Config::MOLLIE_DEBUG_LOG);
            $logoDisplayRaw = $this->configuration->get(Config::MOLLIE_IMAGES);
            $translateMollieRaw = $this->configuration->get(Config::MOLLIE_PAYMENTSCREEN_LOCALE);

            if ($translateMollieRaw === 'send_locale') {
                $translateMollieRaw = Config::PAYMENTSCREEN_LOCALE_SEND_WEBSITE_LOCALE;
            }

            $settings = [
                'invoiceOption' => ($invoiceOptionRaw !== false && $invoiceOptionRaw !== '' && $invoiceOptionRaw !== null)
                    ? (string) $invoiceOptionRaw
                    : (string) Config::MOLLIE_AUTHORIZABLE_PAYMENT_STATUS_DEFAULT,
                'confirmationEmail' => ($confirmationEmailRaw !== false && $confirmationEmailRaw !== '' && $confirmationEmailRaw !== null)
                    ? (string) $confirmationEmailRaw
                    : (string) Config::ORDER_CONF_MAIL_SEND_ON_PAID,

                'autoShip' => (bool) $this->configuration->get(Config::MOLLIE_AUTO_SHIP_MAIN),
                'autoShipStatuses' => array_map('strval', json_decode($this->configuration->get(Config::MOLLIE_AUTO_SHIP_STATUSES) ?: '[]', true)),
                'carriers' => $this->getCarriersData(),

                'debugMode' => (bool) $this->configuration->get(Config::MOLLIE_DISPLAY_ERRORS),
                'logLevel' => ($logLevelRaw !== false && $logLevelRaw !== '' && $logLevelRaw !== null)
                    ? (string) $logLevelRaw
                    : (string) Config::DEBUG_LOG_ERRORS,

                'logoDisplay' => ($logoDisplayRaw !== false && $logoDisplayRaw !== '' && $logoDisplayRaw !== null)
                    ? (string) $logoDisplayRaw
                    : (string) Config::LOGOS_NORMAL,
                'cssPath' => $this->configuration->get(Config::MOLLIE_CSS),
                'translateMollie' => ($translateMollieRaw !== false && $translateMollieRaw !== '' && $translateMollieRaw !== null)
                    ? (string) $translateMollieRaw
                    : (string) Config::PAYMENTSCREEN_LOCALE_BROWSER_LOCALE,

                'statusMappings' => $this->getStatusMappings(),

                'emailStatuses' => $this->getEmailStatuses(),

                'options' => [
                    'orderStatuses' => $this->getOrderStatuses(),
                    'invoiceOptions' => $this->getInvoiceOptions(),
                    'confirmationEmailOptions' => $this->getConfirmationEmailOptions(),
                    'logLevelOptions' => $this->getLogLevelOptions(),
                    'logoDisplayOptions' => $this->getLogoDisplayOptions(),
                    'translateMollieOptions' => $this->getTranslateMollieOptions(),
                ],
            ];

            $this->ajaxRender(json_encode([
                'success' => true,
                'data' => $settings,
            ]));
        } catch (Exception $e) {
            $this->ajaxRender(json_encode([
                'success' => false,
                'message' => $this->module->l('Failed to load settings', self::FILE_NAME),
                'error' => $e->getMessage(),
            ]));
        }
    }

    private function ajaxSaveSettings(): void
    {
        try {
            $data = json_decode($this->tools->getValue('data'), true);

            if (!$data) {
                throw new Exception('Invalid data');
            }

            if (isset($data['invoiceOption'])) {
                $this->configuration->updateValue(Config::MOLLIE_AUTHORIZABLE_PAYMENT_INVOICE_ON_STATUS, $data['invoiceOption']);
            }
            if (isset($data['confirmationEmail'])) {
                $this->configuration->updateValue(Config::MOLLIE_SEND_ORDER_CONFIRMATION, $data['confirmationEmail']);
            }

            if (isset($data['autoShip'])) {
                $this->configuration->updateValue(Config::MOLLIE_AUTO_SHIP_MAIN, (bool) $data['autoShip']);
            }
            if (isset($data['autoShipStatuses'])) {
                $this->configuration->updateValue(Config::MOLLIE_AUTO_SHIP_STATUSES, json_encode($data['autoShipStatuses']));
            }
            if (isset($data['carriers'])) {
                $this->saveCarriersData($data['carriers']);
            }

            if (isset($data['debugMode'])) {
                $this->configuration->updateValue(Config::MOLLIE_DISPLAY_ERRORS, (bool) $data['debugMode']);
            }
            if (isset($data['logLevel'])) {
                $this->configuration->updateValue(Config::MOLLIE_DEBUG_LOG, $data['logLevel']);
            }

            if (isset($data['logoDisplay'])) {
                $this->configuration->updateValue(Config::MOLLIE_IMAGES, $data['logoDisplay']);
            }
            if (isset($data['cssPath'])) {
                $this->configuration->updateValue(Config::MOLLIE_CSS, $data['cssPath']);
            }
            if (isset($data['translateMollie'])) {
                $this->configuration->updateValue(Config::MOLLIE_PAYMENTSCREEN_LOCALE, $data['translateMollie']);
            }

            if (isset($data['statusMappings'])) {
                $this->saveStatusMappings($data['statusMappings']);
            }

            if (isset($data['emailStatuses'])) {
                $this->saveEmailStatuses($data['emailStatuses']);
            }

            $this->ajaxRender(json_encode([
                'success' => true,
                'message' => $this->module->l('Settings saved successfully', self::FILE_NAME),
            ]));
        } catch (Exception $e) {
            $this->ajaxRender(json_encode([
                'success' => false,
                'message' => $this->module->l('Failed to save settings', self::FILE_NAME),
                'error' => $e->getMessage(),
            ]));
        }
    }

    private function getCarriersData(): array
    {
        $carriers = $this->carrierInformationService->getAllCarriersInformation($this->language->getDefaultLanguageId());
        $result = [];

        foreach ($carriers as $carrier) {
            $result[] = [
                'id' => (string) $carrier['id_carrier'],
                'name' => $carrier['name'],
                'urlSource' => $carrier['source'] ?? '',
                'customUrl' => $carrier['custom_url'] ?? '',
            ];
        }

        return $result;
    }

    private function saveCarriersData(array $carriers): void
    {
        foreach ($carriers as $carrier) {
            if (isset($carrier['id'], $carrier['urlSource'])) {
                $carrierId = (int) $carrier['id'];
                $urlSource = $carrier['urlSource'];
                $customUrl = $carrier['customUrl'] ?? '';

                $this->carrierInformationService->saveMolCarrierInfo(
                    $carrierId,
                    $urlSource,
                    $customUrl
                );
            }
        }
    }

    private function getStatusMappings(): array
    {
        $statuses = [];

        $statusKeys = [
            Config::MOLLIE_STATUS_AWAITING => 'Awaiting',
            Config::MOLLIE_STATUS_OPEN => 'Open',
            Config::MOLLIE_STATUS_PAID => 'Paid',
            Config::MOLLIE_STATUS_COMPLETED => 'Completed',
            Config::MOLLIE_STATUS_CANCELED => 'Canceled',
            Config::MOLLIE_STATUS_EXPIRED => 'Expired',
            Config::MOLLIE_STATUS_REFUNDED => 'Refunded',
            Config::MOLLIE_STATUS_PARTIAL_REFUND => 'Partially refunded',
            Config::MOLLIE_STATUS_SHIPPING => 'Shipping',
            Config::MOLLIE_STATUS_CHARGEBACK => 'Chargeback',
        ];

        foreach ($statusKeys as $key => $name) {
            $prestashopStatusId = (int) $this->configuration->get($key);
            $statuses[] = [
                'mollieStatus' => $name,
                'prestashopStatus' => (string) $prestashopStatusId,
                'configKey' => $key,
            ];
        }

        return $statuses;
    }

    private function saveStatusMappings(array $mappings): void
    {
        foreach ($mappings as $mapping) {
            if (isset($mapping['configKey'], $mapping['prestashopStatus'])) {
                $this->configuration->updateValue($mapping['configKey'], (int) $mapping['prestashopStatus']);
            }
        }
    }

    private function getEmailStatuses(): array
    {
        $emailKeys = [
            Config::MOLLIE_MAIL_WHEN_PAID => 'Paid',
            Config::MOLLIE_MAIL_WHEN_COMPLETED => 'Completed',
            Config::MOLLIE_MAIL_WHEN_CANCELED => 'Canceled',
            Config::MOLLIE_MAIL_WHEN_EXPIRED => 'Expired',
            Config::MOLLIE_MAIL_WHEN_REFUNDED => 'Refunded',
            Config::MOLLIE_MAIL_WHEN_CHARGEBACK => 'Chargeback',
            Config::MOLLIE_MAIL_WHEN_FAILED => 'Failed',
            Config::MOLLIE_MAIL_WHEN_SHIPPING => 'Shipping',
        ];

        $statuses = [];
        foreach ($emailKeys as $key => $name) {
            $statuses[] = [
                'status' => $name,
                'enabled' => (bool) $this->configuration->get($key),
                'configKey' => $key,
            ];
        }

        return $statuses;
    }

    private function saveEmailStatuses(array $statuses): void
    {
        foreach ($statuses as $status) {
            if (isset($status['configKey'], $status['enabled'])) {
                $this->configuration->updateValue($status['configKey'], (bool) $status['enabled']);
            }
        }
    }

    private function getOrderStatuses(): array
    {
        $orderStatuses = OrderState::getOrderStates($this->language->getDefaultLanguageId());
        $result = [];

        foreach ($orderStatuses as $status) {
            $result[] = [
                'id' => (string) $status['id_order_state'],
                'name' => $status['name'],
            ];
        }

        return $result;
    }

    private function getInvoiceOptions(): array
    {
        return [
            ['id' => (string) Config::MOLLIE_AUTHORIZABLE_PAYMENT_STATUS_DEFAULT, 'name' => 'Default'],
            ['id' => (string) Config::MOLLIE_AUTHORIZABLE_PAYMENT_STATUS_AUTHORIZED, 'name' => 'Authorized'],
            ['id' => (string) Config::MOLLIE_AUTHORIZABLE_PAYMENT_STATUS_SHIPPED, 'name' => 'On Shipment'],
        ];
    }

    private function getConfirmationEmailOptions(): array
    {
        return [
            ['id' => (string) Config::ORDER_CONF_MAIL_SEND_ON_PAID, 'name' => 'When the order is paid'],
            ['id' => (string) Config::ORDER_CONF_MAIL_SEND_ON_NEVER, 'name' => 'Never'],
        ];
    }

    private function getLogLevelOptions(): array
    {
        return [
            ['id' => (string) Config::DEBUG_LOG_NONE, 'name' => 'Nothing'],
            ['id' => (string) Config::DEBUG_LOG_ERRORS, 'name' => 'Errors'],
            ['id' => (string) Config::DEBUG_LOG_ALL, 'name' => 'Everything'],
        ];
    }

    private function getLogoDisplayOptions(): array
    {
        return [
            ['id' => (string) Config::LOGOS_HIDE, 'name' => 'Hide'],
            ['id' => (string) Config::LOGOS_NORMAL, 'name' => 'Normal'],
            ['id' => (string) Config::LOGOS_BIG, 'name' => 'Big'],
        ];
    }

    private function getTranslateMollieOptions(): array
    {
        return [
            ['id' => (string) Config::PAYMENTSCREEN_LOCALE_BROWSER_LOCALE, 'name' => 'Use browser locale'],
            ['id' => (string) Config::PAYMENTSCREEN_LOCALE_SEND_WEBSITE_LOCALE, 'name' => 'Use webshop locale'],
        ];
    }
}
