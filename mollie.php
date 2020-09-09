<?php
/**
 * Copyright (c) 2012-2020, Mollie B.V.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * - Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR AND CONTRIBUTORS ``AS IS'' AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE AUTHOR OR CONTRIBUTORS BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
 * OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH
 * DAMAGE.
 *
 * @author     Mollie B.V. <info@mollie.nl>
 * @copyright  Mollie B.V.
 * @license    Berkeley Software Distribution License (BSD-License 2) http://www.opensource.org/licenses/bsd-license.php
 * @category   Mollie
 * @package    Mollie
 * @link       https://www.mollie.nl
 * @codingStandardsIgnoreStart
 */

if (!include_once(dirname(__FILE__) . '/vendor/autoload.php')) {
    return;
}
if (!include_once(dirname(__FILE__) . '/vendor/guzzlehttp/guzzle/src/functions_include.php')) {
    return;
}
if (!include_once(dirname(__FILE__) . '/vendor/guzzlehttp/promises/src/functions_include.php')) {
    return;
}
if (!include_once(dirname(__FILE__) . '/vendor/guzzlehttp/promises/src/functions_include.php')) {
    return;
}
if (!include_once(dirname(__FILE__) . '/vendor/guzzlehttp/psr7/src/functions_include.php')) {
    return;
}

/**
 * Class Mollie
 *
 * // Translation detection:
 * $this->l('Shipping);
 * $this->l('Gift wrapping');
 */
class Mollie extends PaymentModule
{
    /**
     * Symfony DI Container
     **/
    private $moduleContainer;

    const DISABLE_CACHE = true;

    /** @var _PhpScoper5eddef0da618a\Mollie\Api\MollieApiClient|null */
    public $api = null;

    /** @var string $currentOrderReference */
    public $currentOrderReference;

    /** @var string $selectedApi */
    public static $selectedApi;

    /** @var bool $cacheCleared Indicates whether the Smarty cache has been cleared during updates */
    public static $cacheCleared;

    // The Addons version does not include the GitHub updater
    const ADDONS = false;

    const SUPPORTED_PHP_VERSION = '5.6';

    const ADMIN_MOLLIE_CONTROLLER = 'AdminMollieModuleController';
    const ADMIN_MOLLIE_AJAX_CONTROLLER = 'AdminMollieAjaxController';

    /**
     * Mollie constructor.
     *
     * @throws ErrorException
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function __construct()
    {
        $this->name = 'mollie';
        $this->tab = 'payments_gateways';
        $this->version = '4.0.8';
        $this->author = 'Mollie B.V.';
        $this->need_instance = 1;
        $this->bootstrap = true;
        $this->module_key = 'a48b2f8918358bcbe6436414f48d8915';

        parent::__construct();
        $this->ps_versions_compliancy = ['min' => '1.6.1.0', 'max' => _PS_VERSION_];
        $this->displayName = $this->l('Mollie');
        $this->description = $this->l('Mollie Payments');

        if (version_compare(phpversion(), $this::SUPPORTED_PHP_VERSION) === -1) {
            return;
        }

        $this->compile();
        $this->setApiKey();
    }

    /**
     * Installs the Mollie Payments Module
     *
     * @return bool
     */
    public function install()
    {
        if (version_compare(phpversion(), Mollie\Config\Config::SUPPORTED_PHP_VERSION) === -1) {
            $this->_errors[] = $this->l('Dear customer, your PHP version is too low. Please upgrade your PHP version to use this module. Mollie module supports PHP 5.6 and higher versions.');

            return false;
        }

        if (!parent::install()) {
            $this->_errors[] = $this->l('Unable to install module');

            return false;
        }

        /** @var \Mollie\Install\Installer $installer */
        $installer = $this->getContainer(\Mollie\Install\Installer::class);
        if (!$installer->install()) {
            $this->_errors = array_merge($this->_errors, $installer->getErrors());

            return false;
        }


        return true;
    }

    /**
     * @return bool
     *
     */
    public function uninstall()
    {
        /** @var \Mollie\Install\Uninstall $uninstall */
        $uninstall = $this->getContainer(\Mollie\Install\Uninstall::class);
        if (!$uninstall->uninstall()) {
            $this->_errors[] = $uninstall->getErrors();

            return false;
        }

        return parent::uninstall();
    }

    private function compile()
    {
        $containerBuilder = new _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\ContainerBuilder();
        $locator = new _PhpScoper5eddef0da618a\Symfony\Component\Config\FileLocator($this->getLocalPath() . 'config');
        $loader = new _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Loader\YamlFileLoader($containerBuilder, $locator);
        $loader->load('config.yml');
        $containerBuilder->compile();

        $this->moduleContainer = $containerBuilder;
    }

    /**
     * @param bool $id
     * @return mixed
     */
    public function getContainer($id = false)
    {
        if ($id) {
            return $this->moduleContainer->get($id);
        }

        return $this->moduleContainer;
    }

    /**
     * @return mixed
     */
    public function getErrors()
    {
        return $this->_errors;
    }

    public function getTable()
    {
        return $this->table;
    }

    public function getContext()
    {
        return $this->context;
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @return string
     *
     * @throws Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function getContent()
    {
        if (Tools::getValue('ajax')) {
            header('Content-Type: application/json;charset=UTF-8');

            if (!method_exists($this, 'displayAjax' . Tools::ucfirst(Tools::getValue('action')))) {
                die(json_encode([
                    'success' => false,
                ]));
            }
            die(json_encode($this->{'displayAjax' . Tools::ucfirst(Tools::getValue('action'))}()));
        }
        /** @var \Mollie\Repository\ModuleRepository $moduleRepository */
        $moduleRepository = $this->getContainer(\Mollie\Repository\ModuleRepository::class);
        $moduleDatabaseVersion = $moduleRepository->getModuleDatabaseVersion($this->name);
        if ($moduleDatabaseVersion < $this->version) {
            $this->context->controller->errors[] = $this->l('Please upgrade Mollie module.');

            return;
        }
        /** @var \Mollie\Builder\FormBuilder $settingsFormBuilder */
        $settingsFormBuilder = $this->getContainer(\Mollie\Builder\FormBuilder::class);
        if (!Configuration::get('PS_SMARTY_FORCE_COMPILE')) {
            $this->context->smarty->assign([
                'settingKey' => $this->l('Template compilation'),
                'settingValue' => $this->l('Never recompile template files'),
                'settingsPage' => \Mollie\Utility\MenuLocationUtility::getMenuLocation('AdminPerformance'),
            ]);
            $this->context->controller->warnings[] = $this->display(__FILE__, 'smarty_warning.tpl');
        }
        if (Configuration::get('PS_SMARTY_CACHE') && Configuration::get('PS_SMARTY_CLEAR_CACHE') === 'never') {
            $this->context->smarty->assign([
                'settingKey' => $this->l('Clear cache'),
                'settingValue' => $this->l('Never clear cache files'),
                'settingsPage' => \Mollie\Utility\MenuLocationUtility::getMenuLocation('AdminPerformance'),
            ]);
            $this->context->controller->errors[] = $this->display(__FILE__, 'smarty_error.tpl');
        }
        if (\Mollie\Utility\CartPriceUtility::checkRoundingMode()) {
            $this->context->smarty->assign([
                'settingKey' => $this->l('Rounding mode'),
                'settingValue' => $this->l('Round up away from zero, when it is half way there (recommended)'),
                'settingsPage' => \Mollie\Utility\MenuLocationUtility::getMenuLocation('AdminPreferences'),
            ]);
            $this->context->controller->errors[] = $this->display(__FILE__, 'rounding_error.tpl');
        }

        $this->context->smarty->assign([
            'link' => Context::getContext()->link,
            'module_dir' => __PS_BASE_URI__ . 'modules/' . basename(__FILE__, '.php') . '/',
            'publicPath' => __PS_BASE_URI__ . 'modules/' . basename(__FILE__, '.php') . '/views/js/dist/',
        ]);

        $updateMessage = '';
        /** @var \Mollie\Service\UpgradeNoticeService $upgradeNoticeService */
        $upgradeNoticeService = $this->getContainer(\Mollie\Service\UpgradeNoticeService::class);
        $noticeCloseTimeStamp = \Configuration::get(Mollie\Config\Config::MOLLIE_MODULE_UPGRADE_NOTICE_CLOSE_DATE);
        if (!static::ADDONS && !$upgradeNoticeService->isUpgradeNoticeClosed(\Mollie\Utility\TimeUtility::getNowTs(), $noticeCloseTimeStamp)) {
            $updateMessage = defined('_TB_VERSION_')
                ? $this->getUpdateMessage('https://github.com/mollie/thirtybees')
                : $this->getUpdateMessage('https://github.com/mollie/PrestaShop');
            if ($updateMessage === 'updateAvailable') {
                $updateMessage = $this->display(__FILE__, 'views/templates/admin/download_update.tpl');
            }
        }
        $resultMessage = '';
        $warningMessage = '';

        $errors = [];

        if (Tools::isSubmit("submit{$this->name}")) {
            /** @var \Mollie\Service\SettingsSaveService $saveSettingsService */
            $saveSettingsService = $this->getContainer(\Mollie\Service\SettingsSaveService::class);
            $resultMessage = $saveSettingsService->saveSettings($errors);
            if (!empty($errors)) {
                $this->context->controller->errors = $resultMessage;
            } else {
                $this->context->controller->confirmations[] = $resultMessage;
            }
        }
        /** @var Mollie\Service\LanguageService $langService */
        $langService = $this->getContainer(Mollie\Service\LanguageService::class);
        $data = [
            'update_message' => $updateMessage,
            'title_status' => $this->l('%s statuses:'),
            'title_visual' => $this->l('Visual settings:'),
            'title_debug' => $this->l('Debug info:'),
            'msg_result' => $resultMessage,
            'msg_warning' => $warningMessage,
            'path' => $this->_path,
            'payscreen_locale_value' => Configuration::get(Mollie\Config\Config::MOLLIE_PAYMENTSCREEN_LOCALE),
            'val_images' => Configuration::get(Mollie\Config\Config::MOLLIE_IMAGES),
            'val_issuers' => Configuration::get(Mollie\Config\Config::MOLLIE_ISSUERS),
            'val_css' => Configuration::get(Mollie\Config\Config::MOLLIE_CSS),
            'val_errors' => Configuration::get(Mollie\Config\Config::MOLLIE_DISPLAY_ERRORS),
            'val_qrenabled' => Configuration::get(Mollie\Config\Config::MOLLIE_QRENABLED),
            'val_logger' => Configuration::get(Mollie\Config\Config::MOLLIE_DEBUG_LOG),
            'val_save' => $this->l('Save'),
            'lang' => $langService->getLang(),
            'logo_url' => $this->getPathUri() . 'views/img/mollie_logo.png',
            'webpack_urls' => \Mollie\Utility\UrlPathUtility::getWebpackChunks('app'),
            'description_message' => $this->l('Description cannot be empty'),
            'Profile_id_message' => $this->l('Wrong profile ID')
        ];

        Media::addJsDef([
            'description_message' => $this->l('Description cannot be empty'),
            'profile_id_message' => $this->l('Wrong profile ID'),
            'profile_id_message_empty' => $this->l('Profile ID cannot be empty'),
            'payment_api' => Mollie\Config\Config::MOLLIE_PAYMENTS_API,
            'ajaxUrl' => $this->context->link->getAdminLink('AdminMollieAjax'),
        ]);

        /** Custom logo JS vars*/
        Media::addJsDef([
            'image_size_message' => $this->l('Image size must be %s%x%s1%'),
            'not_valid_file_message' => $this->l('not a valid file: %s%'),
        ]);

        $this->context->controller->addJS($this->getPathUri() . 'views/js/method_countries.js');
        $this->context->controller->addJS($this->getPathUri() . 'views/js/validation.js');
        $this->context->controller->addJS($this->getPathUri() . 'views/js/admin/settings.js');
        $this->context->controller->addJS($this->getPathUri() . 'views/js/admin/custom_logo.js');
        $this->context->controller->addJS($this->getPathUri() . 'views/js/admin/upgrade_notice.js');
        $this->context->controller->addJS($this->getPathUri() . 'views/js/admin/api_key_test.js');
        $this->context->controller->addJS($this->getPathUri() . 'views/js/admin/init_mollie_account.js');
        $this->context->controller->addCSS($this->getPathUri() . 'views/css/mollie.css');
        $this->context->controller->addCSS($this->getPathUri() . 'views/css/admin/logo_input.css');
        $this->context->smarty->assign($data);

        $html = '';
        $html .= $this->display(__FILE__, 'views/templates/admin/logo.tpl');
        $html .= $updateMessage;

        try {
            $html .= $settingsFormBuilder->buildSettingsForm();
        } catch (PrestaShopDatabaseException $e) {
            $this->context->controller->errors[] = $this->l('You are missing database tables. Try resetting module.');
        }

        return $html;
    }

    /**
     * @param string $str
     * @return string
     * @deprecated
     *
     */
    public function lang($str)
    {
        /** @var Mollie\Service\LanguageService $langService */
        $langService = $this->getContainer(Mollie\Service\LanguageService::class);
        $lang = $langService->getLang();
        if (array_key_exists($str, $lang)) {
            return $lang[$str];
        }

        return $str;
    }

    /**
     * @param string $url
     *
     * @return string|true
     * @throws Exception
     * @throws PrestaShopException
     * @throws SmartyException
     */
    protected function getUpdateMessage($url)
    {
        $updateMessage = '';
        $updateXml = $this->getUpdateXML($url);
        if ($updateXml === false) {
            $updateMessage = $this->l('Warning: Could not retrieve update xml file from github.');
        } else {
            try {
                /** @var SimpleXMLElement $tags */
                @$tags = new SimpleXMLElement($updateXml);
                if (!empty($tags) && isset($tags->entry, $tags->entry[0], $tags->entry[0]->id)) {
                    $title = $tags->entry[0]->id;
                    $latestVersion = preg_replace("/[^0-9,.]/", '', Tools::substr($title, strrpos($title, '/')));
                    if (!version_compare($this->version, $latestVersion, '>=')) {
                        $this->context->smarty->assign([
                            'this_version' => $this->version,
                            'release_version' => $latestVersion,
                            'github_url' => \Mollie\Utility\TagsUtility::ppTags(
                                sprintf(
                                    $this->l('You are currently using version \'%s\' of this plugin. The latest version is \'%s\'. We advice you to [1]update[/1] to enjoy the latest features. '),
                                    $this->version,
                                    $latestVersion
                                ),
                                [
                                    $this->display($this->getPathUri(), 'views/templates/admin/github_redirect.tpl')
                                ]
                            )
                        ]);
                        $updateMessage = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'mollie/views/templates/admin/new_release.tpl');
                    }
                } else {
                    $updateMessage = $this->l('Warning: Update xml file from github follows an unexpected format.');
                }
            } catch (Exception $e) {
                $updateMessage = $this->l('Warning: Update xml file from github follows an unexpected format.');
            }
        }

        return $updateMessage;
    }

    /**
     * @param string $url
     *
     * @return string
     */
    protected function getUpdateXML($url)
    {
        if (static::ADDONS) {
            return '';
        }

        return @Tools::file_get_contents($url . '/releases.atom');
    }

    /**
     * @throws PrestaShopException
     */
    public function hookActionFrontControllerSetMedia()
    {
        /** @var \Mollie\Service\ErrorDisplayService $errorDisplayService */
        $errorDisplayService = $this->getContainer()->get(\Mollie\Service\ErrorDisplayService::class);

        $isOrderController = $this->context->controller instanceof OrderControllerCore;
        $isOPCController = $this->context->controller instanceof OrderOpcControllerCore;
        $isCartController = $this->context->controller instanceof CartControllerCore;
        if ($isOrderController || $isOPCController) {
            $errorDisplayService->showCookieError('mollie_payment_canceled_error');

            Media::addJsDef([
                'profileId' => Configuration::get(Mollie\Config\Config::MOLLIE_PROFILE_ID),
                'isoCode' => $this->context->language->language_code,
                'isTestMode' => \Mollie\Config\Config::isTestMode()
            ]);
            if (\Mollie\Config\Config::isVersion17()) {
                $this->context->controller->registerJavascript(
                    'mollie_iframe_js',
                    'https://js.mollie.com/v1/mollie.js',
                    ['server' => 'remote', 'position' => 'bottom', 'priority' => 150]
                );
                $this->context->controller->addJS("{$this->_path}views/js/front/mollie_iframe.js");
            } else {
                $this->context->controller->addMedia('https://js.mollie.com/v1/mollie.js', null, null, false, false);
                $this->context->controller->addJS("{$this->_path}views/js/front/mollie_iframe_16.js");
            }
            Media::addJsDef([
                'ajaxUrl' => $this->context->link->getModuleLink('mollie', 'ajax'),
                'isPS17' => \Mollie\Config\Config::isVersion17(),
            ]);
            $this->context->controller->addJS("{$this->_path}views/js/front/mollie_error_handle.js");
            $this->context->controller->addCSS("{$this->_path}views/css/mollie_iframe.css");
            if (Configuration::get('PS_SSL_ENABLED_EVERYWHERE')) {
                $this->context->controller->addJS($this->getPathUri() . 'views/js/apple_payment.js');
            }
            $this->context->smarty->assign([
                'custom_css' => Configuration::get(Mollie\Config\Config::MOLLIE_CSS),
            ]);

            $this->context->controller->addJS("{$this->_path}views/js/front/payment_fee.js");

            return $this->display(__FILE__, 'views/templates/front/custom_css.tpl');
        }

        if ($isCartController) {
            $errorDisplayService->showCookieError('mollie_payment_canceled_error');
        }
    }

    /**
     * Add custom JS && CSS to admin controllers
     */
    public function hookActionAdminControllerSetMedia()
    {
        $this->context->controller->addCSS($this->getPathUri() . 'views/css/admin/menu.css');

        $currentController = Tools::getValue('controller');

        if ('AdminOrders' === $currentController) {
            Media::addJsDef([
                'mollieHookAjaxUrl' => $this->context->link->getAdminLink('AdminMollieAjax'),
            ]);
            $this->context->controller->addCSS($this->getPathUri() . 'views/css/admin/order-list.css');
            $this->context->controller->addJS($this->getPathUri() . 'views/js/admin/order_list.js');

            if (Tools::isSubmit('addorder')) {
                Media::addJsDef([
                    'molliePendingStatus' => Configuration::get(\Mollie\Config\Config::STATUS_MOLLIE_AWAITING),
                ]);
                $this->context->controller->addJS($this->getPathUri() . 'views/js/admin/order_add.js');
            }
        }
    }

    /**
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function hookDisplayBackOfficeHeader()
    {
        $html = '';

        if ($this->context->controller instanceof AdminOrdersController) {
            $this->context->smarty->assign([
                'mollieProcessUrl' => $this->context->link->getAdminLink('AdminModules', true) . '&configure=mollie&ajax=1',
                'mollieCheckMethods' => time() > ((int)Configuration::get(Mollie\Config\Config::MOLLIE_METHODS_LAST_CHECK) + Mollie\Config\Config::MOLLIE_METHODS_CHECK_INTERVAL),
            ]);
            $html .= $this->display(__FILE__, 'views/templates/admin/ordergrid.tpl');
            if (Tools::isSubmit('addorder')) {
                $html .= $this->display($this->getPathUri(), 'views/templates/admin/email_checkbox.tpl');
            }
        }

        return $html;
    }

    /**
     * @param array $params Hook parameters
     *
     * @return string Hook HTML
     *
     * @throws Adapter_Exception
     * @throws Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function hookDisplayAdminOrder($params)
    {
        /** @var \Mollie\Repository\PaymentMethodRepository $paymentMethodRepo */
        /** @var \Mollie\Service\ShipmentService $shipmentService */
        $paymentMethodRepo = $this->getContainer(\Mollie\Repository\PaymentMethodRepository::class);
        $shipmentService = $this->getContainer(\Mollie\Service\ShipmentService::class);

        $cartId = Cart::getCartIdByOrderId((int)$params['id_order']);
        $transaction = $paymentMethodRepo->getPaymentBy('cart_id', (int)$cartId);
        if (empty($transaction)) {
            return false;
        }
        $currencies = [];
        foreach (Currency::getCurrencies() as $currency) {
            $currencies[Tools::strtoupper($currency['iso_code'])] = [
                'name' => $currency['name'],
                'iso_code' => Tools::strtoupper($currency['iso_code']),
                'sign' => $currency['sign'],
                'blank' => (bool)isset($currency['blank']) ? $currency['blank'] : true,
                'format' => (int)$currency['format'],
                'decimals' => (bool)isset($currency['decimals']) ? $currency['decimals'] : true,
            ];
        }

        $order = new Order($params['id_order']);
        $this->context->smarty->assign([
            'ajaxEndpoint' => $this->context->link->getAdminLink('AdminModules', true) . '&configure=mollie&ajax=1&action=MollieOrderInfo',
            'transactionId' => $transaction['transaction_id'],
            'currencies' => $currencies,
            'tracking' => $shipmentService->getShipmentInformation($order->reference),
            'publicPath' => __PS_BASE_URI__ . 'modules/' . basename(__FILE__, '.php') . '/views/js/dist/',
            'webPackChunks' => \Mollie\Utility\UrlPathUtility::getWebpackChunks('app'),
            'errorDisplay' => Configuration::get(Mollie\Config\Config::MOLLIE_DISPLAY_ERRORS)
        ]);

        return $this->display(__FILE__, 'order_info.tpl');
    }

    /**
     * @return string
     * @throws Exception
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function hookDisplayPayment()
    {
        $smarty = $this->context->smarty;
        $issuerSetting = Configuration::get(Mollie\Config\Config::MOLLIE_ISSUERS);

        /** @var \Mollie\Service\PaymentMethodService $paymentMethodService */
        /** @var \Mollie\Service\IssuerService $issuerService */
        /** @var \Mollie\Service\OrderFeeService $orderFeeService */
        $paymentMethodService = $this->getContainer(\Mollie\Service\PaymentMethodService::class);
        $issuerService = $this->getContainer(\Mollie\Service\IssuerService::class);
        $orderFeeService = $this->getContainer(\Mollie\Service\OrderFeeService::class);

        $apiMethods = $paymentMethodService->getMethodsForCheckout();
        $issuerList = [];
        foreach ($apiMethods as $apiMethod) {
            if ($apiMethod['id_payment_method'] === _PhpScoper5eddef0da618a\Mollie\Api\Types\PaymentMethod::IDEAL) {
                $issuerList = $issuerService->getIdealIssuers();
            }
        }
        $apiMethods = $orderFeeService->getPaymentFees($apiMethods, $this->context->cart->getOrderTotal());

        $isIFrameEnabled = Configuration::get(Mollie\Config\Config::MOLLIE_IFRAME);
        /** @var Cart $cart */
        $cart = Context::getContext()->cart;
        $smarty->assign([
            'mollieIframe' => $isIFrameEnabled,
            'link' => $this->context->link,
            'cartAmount' => (int)($cart->getOrderTotal(true) * 100),
            'methods' => $apiMethods,
            'issuers' => $issuerList,
            'issuer_setting' => $issuerSetting,
            'images' => Configuration::get(Mollie\Config\Config::MOLLIE_IMAGES),
            'warning' => $this->warning,
            'msg_pay_with' => $this->l('Pay with %s'),
            'msg_bankselect' => $this->l('Select your bank:'),
            'module' => $this,
            'publicPath' => __PS_BASE_URI__ . 'modules/' . basename(__FILE__, '.php') . '/views/js/dist/',
            'IsQREnabled' => Mollie\Config\Config::MOLLIE_QRENABLED,
            'CARTES_BANCAIRES' => Mollie\Config\Config::CARTES_BANCAIRES,
            'ISSUERS_ON_CLICK' => Mollie\Config\Config::ISSUERS_ON_CLICK,
            'web_pack_chunks' => Mollie\Utility\UrlPathUtility::getWebpackChunks('app'),
            'display_errors' => Mollie\Config\Config::MOLLIE_DISPLAY_ERRORS,
            'mollie_translations' => [
                'chooseYourBank' => $this->l('Choose your bank'),
                'orPayByIdealQr' => $this->l('or pay by iDEAL QR'),
                'choose' => $this->l('Choose'),
                'cancel' => $this->l('Cancel'),
            ],
        ]);

        $iframeDisplay = '';
        if (!\Mollie\Config\Config::isVersion17() && $isIFrameEnabled) {
            $iframeDisplay = $this->display(__FILE__, 'mollie_iframe_16.tpl');
        }

        return $this->display(__FILE__, 'addjsdef.tpl') . $this->display(__FILE__, 'payment.tpl') . $iframeDisplay;
    }

    /**
     * EU Advanced Compliance module (PrestaShop module) Advanced Checkout option enabled
     *
     * @return array|null
     *
     * @throws PrestaShopException
     */
    public function hookDisplayPaymentEU()
    {
        // Please update your one page checkout module if it depends on `displayPaymentEU`
        // Mollie does no longer support this hook on PresaShop v1.7 or higher
        // due to the problems caused by mixing the hooks `paymentOptions` and `displayPaymentEU`
        // Only uncomment the following three lines if you have no other choice:
        if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            return [];
        }
        /** @var \Mollie\Service\PaymentMethodService $paymentMethodService */
        /** @var \Mollie\Service\IssuerService $issuerService */
        $paymentMethodService = $this->getContainer(\Mollie\Service\PaymentMethodService::class);
        $issuerService = $this->getContainer(\Mollie\Service\IssuerService::class);

        $methods = $paymentMethodService->getMethodsForCheckout();
        $issuerList = [];
        foreach ($methods as $apiMethod) {
            if ($apiMethod['id'] === _PhpScoper5eddef0da618a\Mollie\Api\Types\PaymentMethod::IDEAL) {
                $issuerList = $issuerService->getIdealIssuers();
            }
        }

        $context = Context::getContext();
        $iso = Tools::strtolower($context->currency->iso_code);
        $paymentOptions = [];

        foreach ($methods as $method) {
            if (!isset(Mollie\Config\Config::$methodCurrencies[$method['id_method']])) {
                continue;
            }
            if (!in_array($iso, Mollie\Config\Config::$methodCurrencies[$method['id_method']])) {
                continue;
            }
            $images = json_decode($method['images_json'], true);
            $paymentOptions[] = [
                'cta_text' => $this->lang($method['method_name']),
                'logo' => Configuration::get(Mollie\Config\Config::MOLLIE_IMAGES) === Mollie\Config\Config::LOGOS_NORMAL
                    ? $images['size1x']
                    : $images['size2x'],
                'action' => $this->context->link->getModuleLink(
                    'mollie',
                    'payment',
                    ['method' => $method['id_method'], 'rand' => time()],
                    true
                ),
            ];
        }

        return $paymentOptions;
    }

    /**
     * @param array $params
     *
     * @return array|null
     * @throws Exception
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function hookPaymentOptions()
    {
        if (version_compare(_PS_VERSION_, '1.7.0.0', '<')) {
            return [];
        }
        /** @var \Mollie\Service\PaymentMethodService $paymentMethodService */
        /** @var \Mollie\Service\IssuerService $issuerService */
        /** @var \Mollie\Provider\CreditCardLogoProvider $creditCardProvider */
        $paymentMethodService = $this->getContainer(\Mollie\Service\PaymentMethodService::class);
        $issuerService = $this->getContainer(\Mollie\Service\IssuerService::class);
        $creditCardProvider = $this->getContainer(\Mollie\Provider\CreditCardLogoProvider::class);

        $methods = $paymentMethodService->getMethodsForCheckout();
        $issuerList = [];
        foreach ($methods as $method) {
            $methodObj = new MolPaymentMethod($method['id_payment_method']);
            if ($methodObj->id_method === _PhpScoper5eddef0da618a\Mollie\Api\Types\PaymentMethod::IDEAL) {
                $issuerList = $issuerService->getIdealIssuers();
            }
        }

        $context = Context::getContext();
        $cart = $context->cart;

        $context->smarty->assign([
            'idealIssuers' => isset($issuerList[_PhpScoper5eddef0da618a\Mollie\Api\Types\PaymentMethod::IDEAL])
                ? $issuerList[_PhpScoper5eddef0da618a\Mollie\Api\Types\PaymentMethod::IDEAL]
                : [],
            'link' => $this->context->link,
            'qrCodeEnabled' => Configuration::get(Mollie\Config\Config::MOLLIE_QRENABLED),
            'qrAlign' => 'left',
            'cartAmount' => (int)($cart->getOrderTotal(true) * 100),
            'publicPath' => __PS_BASE_URI__ . 'modules/' . basename(__FILE__, '.php') . '/views/js/dist/',
        ]);

        $iso = Tools::strtolower($context->currency->iso_code);
        $paymentOptions = [];
        foreach ($methods as $method) {
            if (!isset(Mollie\Config\Config::$methodCurrencies[$methodObj->id_method])) {
                continue;
            }
            if (!in_array($iso, Mollie\Config\Config::$methodCurrencies[$methodObj->id_method])) {
                continue;
            }

            $methodObj = new MolPaymentMethod($method['id_payment_method']);
            $paymentFee = \Mollie\Utility\PaymentFeeUtility::getPaymentFee($methodObj, $cart->getOrderTotal());

            $isIdealMethod = $methodObj->id_method === _PhpScoper5eddef0da618a\Mollie\Api\Types\PaymentMethod::IDEAL;
            $isIssuersOnClick = Configuration::get(Mollie\Config\Config::MOLLIE_ISSUERS) === Mollie\Config\Config::ISSUERS_ON_CLICK;
            $isCreditCardMethod = $methodObj->id_method === _PhpScoper5eddef0da618a\Mollie\Api\Types\PaymentMethod::CREDITCARD;

            if ($isIdealMethod && $isIssuersOnClick) {
                $newOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();
                $newOption
                    ->setCallToActionText($this->lang($methodObj->method_name))
                    ->setModuleName($this->name)
                    ->setAction(Context::getContext()->link->getModuleLink(
                        $this->name,
                        'payment',
                        ['method' => $methodObj->id_method, 'rand' => time()],
                        true
                    ))
                    ->setInputs([
                        'token' => [
                            'name' => 'issuer',
                            'type' => 'hidden',
                            'value' => '',
                        ],
                    ])
                    ->setAdditionalInformation($this->display(__FILE__, 'ideal_dropdown.tpl'));

                $image = $creditCardProvider->getMethodOptionLogo($methodObj);
                $newOption->setLogo($image);

                if ($paymentFee) {
                    $newOption->setInputs(
                        [
                            [
                                'type' => 'hidden',
                                'name' => "payment-fee-price",
                                'value' => $paymentFee
                            ],
                            [
                                'type' => 'hidden',
                                'name' => "payment-fee-price-display",
                                'value' => sprintf($this->l('Payment Fee: %1s'), Tools::displayPrice($paymentFee))
                            ],
                        ]
                    );
                }

                $paymentOptions[] = $newOption;
            } elseif (
                ($isCreditCardMethod || $methodObj->id_method === \Mollie\Config\Config::CARTES_BANCAIRES) &&
                Configuration::get(Mollie\Config\Config::MOLLIE_IFRAME)
            ) {
                $this->context->smarty->assign([
                    'mollieIFrameJS' => 'https://js.mollie.com/v1/mollie.js',
                    'price' => $this->context->cart->getOrderTotal(),
                    'priceSign' => $this->context->currency->getSign(),
                    'methodId' => $methodObj->id_method
                ]);
                $newOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();
                $newOption
                    ->setCallToActionText($this->lang($methodObj->method_name))
                    ->setModuleName($this->name)
                    ->setAdditionalInformation($this->display(__FILE__, 'mollie_iframe.tpl'))
                    ->setInputs(
                        [
                            [
                                'type' => 'hidden',
                                'name' => "mollieCardToken{$methodObj->id_method}",
                                'value' => ''
                            ]
                        ]
                    )
                    ->setAction(Context::getContext()->link->getModuleLink(
                        'mollie',
                        'payScreen',
                        ['method' => $methodObj->id_method, 'rand' => time(), 'cardToken' => ''],
                        true
                    ));

                $image = $creditCardProvider->getMethodOptionLogo($methodObj);
                $newOption->setLogo($image);

                if ($paymentFee) {
                    $newOption->setInputs(
                        [
                            [
                                'type' => 'hidden',
                                'name' => "mollieCardToken{$methodObj->id_method}",
                                'value' => ''
                            ],
                            [
                                'type' => 'hidden',
                                'name' => "payment-fee-price",
                                'value' => $paymentFee
                            ],
                            [
                                'type' => 'hidden',
                                'name' => "payment-fee-price-display",
                                'value' => sprintf($this->l('Payment Fee: %1s'), Tools::displayPrice($paymentFee))
                            ],
                        ]
                    );
                } else {
                    $newOption->setInputs(
                        [
                            [
                                'type' => 'hidden',
                                'name' => "mollieCardToken{$methodObj->id_method}",
                                'value' => ''
                            ],
                        ]
                    );
                }

                $paymentOptions[] = $newOption;
            } else {
                $newOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();
                $newOption
                    ->setCallToActionText($this->lang($methodObj->method_name))
                    ->setModuleName($this->name)
                    ->setAction(Context::getContext()->link->getModuleLink(
                        'mollie',
                        'payment',
                        ['method' => $methodObj->id_method, 'rand' => time()],
                        true
                    ));

                $image = $creditCardProvider->getMethodOptionLogo($methodObj);
                $newOption->setLogo($image);

                if ($paymentFee) {
                    $newOption->setInputs(
                        [
                            [
                                'type' => 'hidden',
                                'name' => "payment-fee-price",
                                'value' => $paymentFee
                            ],
                            [
                                'type' => 'hidden',
                                'name' => "payment-fee-price-display",
                                'value' => sprintf($this->l('Payment Fee: %1s'), Tools::displayPrice($paymentFee))
                            ],
                        ]
                    );
                }

                $paymentOptions[] = $newOption;
            }
        }

        return $paymentOptions;
    }

    /**
     * @return string
     *
     * @throws Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function hookDisplayOrderConfirmation()
    {
        /** @var \Mollie\Repository\PaymentMethodRepository $paymentMethodRepo */
        $paymentMethodRepo = $this->getContainer(\Mollie\Repository\PaymentMethodRepository::class);
        $payment = $paymentMethodRepo->getPaymentBy('cart_id', (int)Tools::getValue('id_cart'));
        if ($payment && $payment['bank_status'] == _PhpScoper5eddef0da618a\Mollie\Api\Types\PaymentStatus::STATUS_PAID) {
            $this->context->smarty->assign('okMessage', $this->l('Thank you. Your payment has been received.'));

            return $this->display(__FILE__, 'ok.tpl');
        }

        return '';
    }

    /**
     * @return array
     * @throws PrestaShopException
     *
     * @since 3.3.0
     */
    public function displayAjaxMollieMethodConfig()
    {
        header('Content-Type: application/json;charset=UTF-8');
        /** @var \Mollie\Service\ApiService $apiService */
        $apiService = $this->getContainer(\Mollie\Service\ApiService::class);
        /** @var \Mollie\Service\CountryService $countryService */
        $countryService = $this->getContainer(\Mollie\Service\CountryService::class);
        try {
            $methodsForConfig = $apiService->getMethodsForConfig($this->api, $this->getPathUri());
        } catch (_PhpScoper5eddef0da618a\Mollie\Api\Exceptions\ApiException $e) {
            return [
                'success' => false,
                'methods' => null,
                'message' => $e->getMessage(),
            ];
        } catch (PrestaShopException $e) {
            return [
                'success' => false,
                'methods' => null,
                'message' => $e->getMessage(),
            ];
        }
        Configuration::updateValue(Mollie\Config\Config::MOLLIE_METHODS_LAST_CHECK, time());
        if (!is_array($methodsForConfig)) {
            return [
                'success' => false,
                'methods' => null,
                'message' => $this->l('No payment methods found'),
            ];
        }

        $dbMethods = @json_decode(Configuration::get(Mollie\Config\Config::METHODS_CONFIG), true);

        // Auto update images and issuers
        $shouldSave = false;
        if (is_array($dbMethods)) {
            foreach ($dbMethods as $index => &$dbMethod) {
                $found = false;
                foreach ($methodsForConfig as $methodForConfig) {
                    if ($dbMethod['id'] === $methodForConfig['id']) {
                        $found = true;
                        foreach (['issuers', 'image', 'name', 'available'] as $prop) {
                            if (isset($methodForConfig[$prop])) {
                                $dbMethod[$prop] = $methodForConfig[$prop];
                                $shouldSave = true;
                            }
                        }
                        break;
                    }
                }
                if (!$found) {
                    unset($dbMethods[$index]);
                    $shouldSave = true;
                }
            }
        } else {
            $shouldSave = true;
            $dbMethods = [];
            foreach ($methodsForConfig as $index => $method) {
                $dbMethods[] = array_merge(
                    $method,
                    [
                        'position' => $index,
                    ]
                );
            }
        }

        if ($shouldSave && !empty($dbMethods)) {
            Configuration::updateValue(Mollie\Config\Config::METHODS_CONFIG, json_encode($dbMethods));
        }

        return [
            'success' => true,
            'methods' => $methodsForConfig,
            'countries' => $countryService->getActiveCountriesList(),
        ];
    }

    /**
     * @return array
     * @throws PrestaShopException
     *
     * @since 3.3.0
     */
    public function displayAjaxMollieCarrierConfig()
    {
        header('Content-Type: application/json;charset=UTF-8');
        /** @var \Mollie\Service\CarrierService $carrierService */
        $carrierService = $this->getContainer(\Mollie\Service\CarrierService::class);
        $dbConfig = @json_decode(Configuration::get(Mollie\Config\Config::MOLLIE_TRACKING_URLS), true);

        return ['success' => true, 'carriers' => $carrierService->carrierConfig($dbConfig)];
    }

    /**
     * @return array
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @since 3.3.0
     */
    public function displayAjaxMollieOrderInfo()
    {
        header('Content-Type: application/json;charset=UTF-8');

        /** @var \Mollie\Service\MollieOrderInfoService $orderInfoService */
        $orderInfoService = $this->getContainer(\Mollie\Service\MollieOrderInfoService::class);

        $input = @json_decode(Tools::file_get_contents('php://input'), true);
        $adminOrdersController = new AdminOrdersController();

        return $orderInfoService->displayMollieOrderInfo($input, $adminOrdersController->id);
    }

    /**
     * actionOrderStatusUpdate hook
     *
     * @param array $params
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws Adapter_Exception
     *
     * @since 3.3.0
     */
    public function hookActionOrderStatusUpdate($params = [])
    {
        if (!isset($params['newOrderStatus']) || !isset($params['id_order'])) {
            return;
        }

        if ($params['newOrderStatus'] instanceof OrderState) {
            $orderStatus = $params['newOrderStatus'];
        } elseif (is_int($params['newOrderStatus']) || is_string($params['newOrderStatus'])) {
            $orderStatus = new OrderState($params['newOrderStatus']);
        }
        if (isset($orderStatus)
            && $orderStatus instanceof OrderState
            && Validate::isLoadedObject($orderStatus)
        ) {
            $orderStatusNumber = $orderStatus->id;
        } else {
            return;
        }

        $idOrder = $params['id_order'];
        $order = new Order($idOrder);
        $checkStatuses = [];
        if (Configuration::get(Mollie\Config\Config::MOLLIE_AUTO_SHIP_STATUSES)) {
            $checkStatuses = @json_decode(Configuration::get(Mollie\Config\Config::MOLLIE_AUTO_SHIP_STATUSES));
        }
        if (!is_array($checkStatuses)) {
            $checkStatuses = [];
        }

        /** @var \Mollie\Service\ShipmentService $shipmentService */
        $shipmentService = $this->getContainer(\Mollie\Service\ShipmentService::class);
        $shipmentInfo = $shipmentService->getShipmentInformation($order->reference);

        if (!(Configuration::get(Mollie\Config\Config::MOLLIE_AUTO_SHIP_MAIN) && in_array($orderStatusNumber, $checkStatuses)
            ) || $shipmentInfo === null
        ) {
            return;
        }

        try {
            /** @var \Mollie\Repository\PaymentMethodRepository $paymentMethodRepo */
            $paymentMethodRepo = $this->getContainer(\Mollie\Repository\PaymentMethodRepository::class);
            $dbPayment = $paymentMethodRepo->getPaymentBy('order_id', (int)$idOrder);
        } catch (PrestaShopDatabaseException $e) {
            PrestaShopLogger::addLog("Mollie module error: {$e->getMessage()}");

            return;
        } catch (PrestaShopException $e) {
            PrestaShopLogger::addLog("Mollie module error: {$e->getMessage()}");

            return;
        }
        if (empty($dbPayment) || !isset($dbPayment['transaction_id'])) {
            // No transaction found
            return;
        }

        $length = Tools::strlen(_PhpScoper5eddef0da618a\Mollie\Api\Endpoints\OrderEndpoint::RESOURCE_ID_PREFIX);
        if (Tools::substr($dbPayment['transaction_id'], 0, $length) !== _PhpScoper5eddef0da618a\Mollie\Api\Endpoints\OrderEndpoint::RESOURCE_ID_PREFIX
        ) {
            // No need to check regular payments
            return;
        }

        try {
            $apiOrder = $this->api->orders->get($dbPayment['transaction_id']);
            $shippableItems = 0;
            foreach ($apiOrder->lines as $line) {
                $shippableItems += $line->shippableQuantity;
            }
            if ($shippableItems <= 0) {
                return;
            }

            $apiOrder->shipAll($shipmentInfo);
        } catch (\_PhpScoper5eddef0da618a\Mollie\Api\Exceptions\ApiException $e) {
            PrestaShopLogger::addLog("Mollie module error: {$e->getMessage()}");

            return;
        } catch (Exception $e) {
            PrestaShopLogger::addLog("Mollie module error: {$e->getMessage()}");

            return;
        }
    }

    public function hookActionEmailSendBefore($params)
    {
        if (!isset($params['cart']->id)) {
            return true;
        }

        $cart = new Cart($params['cart']->id);
        $orderId = Order::getOrderByCartId($cart->id);
        $order = new Order($orderId);
        if ($order === null || $order->module !== $this->name) {
            return true;
        }
        /** @var \Mollie\Validator\OrderConfMailValidator $orderConfMailValidator */
        /** @var \Mollie\Validator\NewOrderMailValidator $newOrderMailValidator */
        $orderConfMailValidator = $this->getContainer(\Mollie\Validator\OrderConfMailValidator::class);
        $newOrderMailValidator = $this->getContainer(\Mollie\Validator\NewOrderMailValidator::class);

        if ($params['template'] === 'order_conf') {
            return $orderConfMailValidator->validate((int)$order->current_state);
        }

        if ($params['template'] === 'new_order') {
            return $newOrderMailValidator->validate((int)$order->current_state);
        }

        if ($params['template'] === 'order_conf' ||
            $params['template'] === 'account' ||
            $params['template'] === 'backoffice_order' ||
            $params['template'] === 'contact_form' ||
            $params['template'] === 'credit_slip' ||
            $params['template'] === 'in_transit' ||
            $params['template'] === 'order_changed' ||
            $params['template'] === 'order_merchant_comment' ||
            $params['template'] === 'order_return_state' ||
            $params['template'] === 'cheque' ||
            $params['template'] === 'payment' ||
            $params['template'] === 'preparation' ||
            $params['template'] === 'shipped' ||
            $params['template'] === 'order_canceled' ||
            $params['template'] === 'payment_error' ||
            $params['template'] === 'outofstock' ||
            $params['template'] === 'bankwire' ||
            $params['template'] === 'refund') {
            $orderId = Order::getOrderByCartId($cart->id);
            $order = new Order($orderId);
            if (!$order) {
                return true;
            }
            try {
                $orderFee = new MolOrderFee($order->id);
            } catch (Exception $e) {
                PrestaShopLogger::addLog(__METHOD__ . ' said: ' . $e->getMessage(), Mollie\Config\Config::CRASH);

                return true;
            }
            if ($orderFee->order_fee) {
                $params['templateVars']['{payment_fee}'] = Tools::displayPrice($orderFee->order_fee);
            } else {
                $params['templateVars']['{payment_fee}'] = Tools::displayPrice(0);
            }
        }
    }

    public function hookDisplayPDFInvoice($params)
    {
        if ($params['object'] instanceof OrderInvoice) {
            $order = $params['object']->getOrder();
            /** @var \Mollie\Repository\OrderFeeRepository $orderFeeRepo */
            $orderFeeRepo = $this->getContainer(\Mollie\Repository\OrderFeeRepository::class);
            $orderFeeId = $orderFeeRepo->getOrderFeeIdByCartId(Cart::getCartIdByOrderId($order->id));

            $orderFee = new MolOrderFee($orderFeeId);

            if (!$orderFee->order_fee) {
                return;
            }

            $this->context->smarty->assign(
                [
                    'order_fee' => Tools::displayPrice($orderFee->order_fee)
                ]
            );

            return $this->context->smarty->fetch(
                $this->getLocalPath() . 'views/templates/admin/invoice_fee.tpl'
            );
        }

    }

    /**
     * @return array
     */
    public function getTabs()
    {
        return [
            [
                'name' => $this->name,
                'class_name' => self::ADMIN_MOLLIE_CONTROLLER,
                'ParentClassName' => 'AdminParentShipping',
                'parent' => 'AdminParentShipping'
            ],
            [
                'name' => $this->l('AJAX', __CLASS__),
                'class_name' => self::ADMIN_MOLLIE_AJAX_CONTROLLER,
                'ParentClassName' => self::ADMIN_MOLLIE_CONTROLLER,
                'parent' => self::ADMIN_MOLLIE_CONTROLLER,
                'module_tab' => true,
                'visible' => false,
            ],
        ];
    }

    public function hookActionAdminOrdersListingFieldsModifier($params)
    {
        if (isset($params['select'])) {
            $params['select'] .= ' ,mol.`transaction_id`';
        }
        if (isset($params['join'])) {
            $params['join'] .= ' LEFT JOIN `' . _DB_PREFIX_ . 'mollie_payments` mol ON mol.`order_reference` = a.`reference`';
        }
        $params['fields']['order_id'] = [
            'title' => $this->l('Resend payment link'),
            'align' => 'text-center',
            'class' => 'fixed-width-xs',
            'orderby' => false,
            'search' => false,
            'remove_onclick' => true,
            'callback_object' => 'mollie',
            'callback' => 'resendOrderPaymentLink'
        ];
    }

    public function hookActionValidateOrder($params)
    {
        if ($this->context->controller instanceof AdminOrdersControllerCore &&
            $params["order"]->module === $this->name
        ) {
            $cartId = $params["cart"]->id;
            $totalPaid = strval($params["order"]->total_paid);
            $currency = $params["currency"]->iso_code;
            $customerKey = $params["customer"]->secure_key;
            $orderReference = $params["order"]->reference;
            $orderPayment = $params["order"]->payment;
            $orderId = $params["order"]->id;

            /** @var \Mollie\Service\PaymentMethodService $paymentMethodService */
            $paymentMethodService = $this->getContainer(\Mollie\Service\PaymentMethodService::class);
            $paymentMethodObj = new MolPaymentMethod();
            $paymentData = $paymentMethodService->getPaymentData(
                $totalPaid,
                $currency,
                '',
                null,
                $cartId,
                $customerKey,
                $paymentMethodObj,
                false,
                $orderReference
            );

            $newPayment = $this->api->payments->create($paymentData);

            /** @var \Mollie\Repository\PaymentMethodRepository $paymentMethodRepository */
            $paymentMethodRepository = $this->getContainer(\Mollie\Repository\PaymentMethodRepository::class);
            $paymentMethodRepository->addOpenStatusPayment(
                $cartId,
                $orderPayment,
                $newPayment->id,
                $orderId,
                $orderReference
            );

            $sendMolliePaymentMail = Tools::getValue('mollie-email-send');
            if ($sendMolliePaymentMail === 'on') {
                /** @var \Mollie\Service\MolliePaymentMailService $molliePaymentMailService */
                $molliePaymentMailService = $this->getContainer(\Mollie\Service\MolliePaymentMailService::class);
                $molliePaymentMailService->sendSecondChanceMail($orderId);
            }
        }
    }

    /**
     * @param $idOrder
     * @return string
     * @throws Exception
     */
    public static function resendOrderPaymentLink($orderId)
    {
        $module = Module::getInstanceByName('mollie');
        /** @var \Mollie\Repository\PaymentMethodRepository $molliePaymentRepo */
        $molliePaymentRepo = $module->getContainer(\Mollie\Repository\PaymentMethodRepository::class);
        $molPayment = $molliePaymentRepo->getPaymentBy('order_id', $orderId);
        if (\Mollie\Utility\MollieStatusUtility::isPaymentFinished($molPayment['bank_status'])) {
            return false;
        }

        $mollie = Module::getInstanceByName('mollie');

        /** @var \Mollie\Presenter\OrderListActionBuilder $orderListActionBuilder */
        $orderListActionBuilder = $mollie->getContainer(\Mollie\Presenter\OrderListActionBuilder::class);

        return $orderListActionBuilder->buildOrderPaymentResendButton($mollie->smarty, $orderId);
    }

    private function setApiKey()
    {
        if ($this->api) {
            return;
        }
        /** @var \Mollie\Service\ApiService $apiService */
        $apiService = $this->getContainer(\Mollie\Service\ApiService::class);

        $environment = Configuration::get(Mollie\Config\Config::MOLLIE_ENVIRONMENT);
        $apiKeyConfig = (int)$environment === \Mollie\Config\Config::ENVIRONMENT_LIVE ?
            Mollie\Config\Config::MOLLIE_API_KEY : Mollie\Config\Config::MOLLIE_API_KEY_TEST;

        try {
            $this->api = $apiService->setApiKey(Configuration::get($apiKeyConfig), $this->version);
        } catch (_PhpScoper5eddef0da618a\Mollie\Api\Exceptions\IncompatiblePlatform $e) {
            PrestaShopLogger::addLog(__METHOD__ . ' - System incompatible: ' . $e->getMessage(), Mollie\Config\Config::CRASH);
        } catch (_PhpScoper5eddef0da618a\Mollie\Api\Exceptions\ApiException $e) {
            $this->warning = $this->l('Payment error:') . $e->getMessage();
            PrestaShopLogger::addLog(__METHOD__ . ' said: ' . $this->warning, Mollie\Config\Config::CRASH);
        }
    }
}
