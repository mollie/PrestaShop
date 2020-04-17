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

if (!defined('_PS_VERSION_')) {
    return;
}

// Composer autoload, if failure, skip this module
if (!include_once(dirname(__FILE__) . '/vendor/autoload.php')) {
    return;
}

// PSR-7 standard autoload, if failure, skip this module
if (!function_exists('\\Hough\\Psr7\\str')) {
    if (!include_once(dirname(__FILE__) . '/vendor/ehough/psr7/src/functions.php')) {
        return;
    }
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
    /** @var \Mollie\Api\MollieApiClient|null */
    public $api = null;
    /** @var string $currentOrderReference */
    public $currentOrderReference;
    /** @var string $selectedApi */
    public static $selectedApi;
    /** @var bool $cacheCleared Indicates whether the Smarty cache has been cleared during updates */
    public static $cacheCleared;

    // The Addons version does not include the GitHub updater
    const ADDONS = false;

    /**
     * Hooks for this module
     *
     * @var array $hooks
     */
    public $hooks = [
        'displayPayment',
        'displayPaymentEU',
        'paymentOptions',
        'displayAdminOrder',
        'displayBackOfficeHeader',
        'displayOrderConfirmation',
        'actionFrontControllerSetMedia',
        'actionEmailSendBefore'
    ];

    public $extra_mail_vars = [];


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
        $this->version = '3.3.4';
        $this->author = 'Mollie B.V.';
        $this->need_instance = 1;
        $this->bootstrap = true;
        $this->module_key = 'a48b2f8918358bcbe6436414f48d8915';

        parent::__construct();

        $this->compile();
        $this->displayName = $this->l('Mollie');
        $this->description = $this->l('Mollie Payments');

        /** @var \Mollie\Service\ApiService $apiService */
        $apiService = $this->getContainer(\Mollie\Service\ApiService::class);
        try {
            $this->api = $apiService->setApiKey(Configuration::get(Mollie\Config\Config::MOLLIE_API_KEY), $this->version);
        } catch (\Mollie\Api\Exceptions\IncompatiblePlatform $e) {
            PrestaShopLogger::addLog(__METHOD__ . ' - System incompatible: ' . $e->getMessage(), Mollie\Config\Config::CRASH);
        } catch (\Mollie\Api\Exceptions\ApiException $e) {
            $this->warning = $this->l('Payment error:') . $e->getMessage();
            PrestaShopLogger::addLog(__METHOD__ . ' said: ' . $this->warning, Mollie\Config\Config::CRASH);
        }

        // Register json Smarty function when missing, happens on older 1.5; some 1.6 versions
        try {
            smartyRegisterFunction(Context::getContext()->smarty, 'modifier', 'json_encode', ['Tools', 'jsonEncode']);
            smartyRegisterFunction(Context::getContext()->smarty, 'modifier', 'json_decode', ['Tools', 'jsonDecode']);
        } catch (SmartyException $e) {
            // Already registered
        } catch (Exception $e) {
            // Already registered
        }

        if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')
            && version_compare(_PS_VERSION_, '1.7.0.5', '<')
        ) {
            // Bugfix generating invoices on 1.7.0.x => Register Admin/PDF displayPrice Smarty function when missing
            try {
                smartyRegisterFunction(Context::getContext()->smarty, 'function', 'displayPrice', ['Tools', 'displayPriceSmarty']);
            } catch (SmartyException $e) {
                // Already registered
            } catch (Exception $e) {
                // Already registered
            }
        }
    }

    /**
     * Installs the Mollie Payments Module
     *
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws Adapter_Exception
     */
    public function install()
    {
        if (!parent::install()) {
            $this->_errors[] = 'Unable to install module';

            return false;
        }

        /** @var \Mollie\Install\Installer $installer */
        $installer = $this->getContainer(\Mollie\Install\Installer::class);
        if (!$installer->install()) {
            $this->_errors[] = $installer->getErrors();
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
        $containerCache = $this->getLocalPath() . 'var/cache/container.php';
        $containerConfigCache = new \Symfony\Component\Config\ConfigCache($containerCache, self::DISABLE_CACHE);
        $containerClass = get_class($this) . 'Container';
        if (!$containerConfigCache->isFresh()) {
            $containerBuilder = new \Symfony\Component\DependencyInjection\ContainerBuilder();
            $locator = new \Symfony\Component\Config\FileLocator($this->getLocalPath() . 'config');
            $loader = new \Symfony\Component\DependencyInjection\Loader\YamlFileLoader($containerBuilder, $locator);
            $loader->load('config.yml');
            $containerBuilder->compile();
            $dumper = new Symfony\Component\DependencyInjection\Dumper\PhpDumper($containerBuilder);
            $containerConfigCache->write(
                $dumper->dump(['class' => $containerClass]),
                $containerBuilder->getResources()
            );
        }
        require_once $containerCache;
        $this->moduleContainer = new $containerClass();
    }

    /**
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
     *
     * @deprecated 3.4.0
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
     * @throws Adapter_Exception
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

        if ($module = $this->checkPaymentModuleOverride()) {
            $this->context->controller->warnings[] = sprintf(
                $this->l('The method %s is overridden by module %s. This can cause interference with payments.'),
                'PaymentModule::validateOrder',
                $module
            );
        }
        if ($this->checkTemplateCompilation()) {
            $this->context->smarty->assign([
                'settingKey' => version_compare(_PS_VERSION_, '1.7.3.0', '>=')
                    ? $this->trans('Template compilation', [], 'Admin.Advparameters.Feature')
                    : Translate::getAdminTranslation('Template compilation', 'AdminPerformance'),
                'settingValue' => version_compare(_PS_VERSION_, '1.7.3.0', '>=')
                    ? $this->trans('Never recompile template files', [], 'Admin.Advparameters.Feature')
                    : Translate::getAdminTranslation('Never recompile template files', 'AdminPerformance'),
                'settingsPage' => \Mollie\Utility\MenuLocationUtility::getMenuLocation('AdminPerformance'),
            ]);
            $this->context->controller->warnings[] = $this->display(__FILE__, 'smarty_warning.tpl');
        }
        if ($this->checkStaleSmartyCache()) {
            $this->context->smarty->assign([
                'settingKey' => version_compare(_PS_VERSION_, '1.7.3.0', '>=')
                    ? $this->trans('Clear cache', [], 'Admin.Advparameters.Feature')
                    : Translate::getAdminTranslation('Clear cache', 'AdminPerformance'),
                'settingValue' => version_compare(_PS_VERSION_, '1.7.3.0', '>=')
                    ? $this->trans('Never clear cache files', [], 'Admin.Advparameters.Feature')
                    : Translate::getAdminTranslation('Never clear cache files', 'AdminPerformance'),
                'settingsPage' => \Mollie\Utility\MenuLocationUtility::getMenuLocation('AdminPerformance'),
            ]);
            $this->context->controller->errors[] = $this->display(__FILE__, 'smarty_error.tpl');
        }
        if (\Mollie\Utility\CartPriceUtility::checkRoundingMode()) {
            $this->context->smarty->assign([
                'settingKey' => version_compare(_PS_VERSION_, '1.7.3.0', '>=')
                    ? $this->trans('Rounding mode', [], 'Admin.Shopparameters.Feature')
                    : Translate::getAdminTranslation('Rounding mode', 'AdminPreferences'),
                'settingValue' => version_compare(_PS_VERSION_, '1.7.3.0', '>=')
                    ? $this->trans('Round up away from zero, when it is half way there (recommended)', [], 'Admin.Shopparameters.Feature')
                    : Translate::getAdminTranslation('Round up away from zero, when it is half way there (recommended)', 'AdminPreferences'),
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
        if (!static::ADDONS) {
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
        if (Tools::isSubmit('submitNewAccount')) {
            $this->processNewAccount();
        }

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
        /** @var \Mollie\Utility\LanguageUtility $langUtility */
        $langUtility = $this->getContainer(\Mollie\Utility\LanguageUtility::class);
        $data = [
            'update_message' => $updateMessage,
            'title_status' => $this->l('%s statuses:'),
            'title_visual' => $this->l('Visual settings:'),
            'title_debug' => $this->l('Debug info:'),
            'msg_result' => $resultMessage,
            'msg_warning' => $warningMessage,
            'path' => $this->_path,
            'val_api_key' => Configuration::get(Mollie\Config\Config::MOLLIE_API_KEY),
            'payscreen_locale_value' => Configuration::get(Mollie\Config\Config::MOLLIE_PAYMENTSCREEN_LOCALE),
            'val_images' => Configuration::get(Mollie\Config\Config::MOLLIE_IMAGES),
            'val_issuers' => Configuration::get(Mollie\Config\Config::MOLLIE_ISSUERS),
            'val_css' => Configuration::get(Mollie\Config\Config::MOLLIE_CSS),
            'val_errors' => Configuration::get(Mollie\Config\Config::MOLLIE_DISPLAY_ERRORS),
            'val_qrenabled' => Configuration::get(Mollie\Config\Config::MOLLIE_QRENABLED),
            'val_logger' => Configuration::get(Mollie\Config\Config::MOLLIE_DEBUG_LOG),
            'val_save' => $this->l('Save'),
            'lang' => $langUtility->getLang(),
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
        ]);
        $this->context->controller->addJS($this->getPathUri() . 'views/js/method_countries.js');
        $this->context->controller->addJS($this->getPathUri() . 'views/js/validation.js');
        $this->context->controller->addJS($this->getPathUri() . 'views/js/admin/settings.js');
        $this->context->controller->addCSS($this->getPathUri() . 'views/css/mollie.css');
        $this->context->smarty->assign($data);

        $html = $this->display(__FILE__, 'views/templates/admin/logo.tpl');
        $html .= $this->getSettingsForm();

        return $html;
    }

    protected function getSettingsForm()
    {
        $isApiKeyProvided = Configuration::get(Mollie\Config\Config::MOLLIE_API_KEY);

        $inputs = $this->getAccountSettingsSection($isApiKeyProvided);

        if ($isApiKeyProvided) {
            $inputs = array_merge($inputs, $this->getAdvancedSettingsSection());
        }

        $fields = [
            'form' => [
                'tabs' => $this->getSettingTabs($isApiKeyProvided),
                'input' => $inputs,
                'submit' => [
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right',
                ],
            ],
        ];

        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitmollie';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . "&configure={$this->name}&tab_module={$this->tab}&module_name={$this->name}";
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        /** @var \Mollie\Service\ConfigFieldService $configFieldService */
        $configFieldService = $this->getContainer(\Mollie\Service\ConfigFieldService::class);
        $helper->tpl_vars = [
            'fields_value' => $configFieldService->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];

        return $helper->generateForm([$fields]);
    }

    protected function getSettingTabs($isApiKeyProvided)
    {
        $tabs = [
            'general_settings' => $this->l('General settings'),
        ];

        if ($isApiKeyProvided) {
            $tabs['advanced_settings'] = $this->l('Advanced settings');
        }

        return $tabs;
    }

    protected function getAccountSettingsSection($isApiKeyProvided)
    {
        /** @var \Mollie\Service\ApiService $apiService */
        $apiService = $this->getContainer(\Mollie\Service\ApiService::class);
        /** @var \Mollie\Service\CountryService $countryService */
        $countryService = $this->getContainer(\Mollie\Service\CountryService::class);
        
        $generalSettings = 'general_settings';
        if ($isApiKeyProvided) {
            $input = [
                [
                    'type' => 'text',
                    'label' => $this->l('API Key'),
                    'tab' => $generalSettings,
                    'desc' => \Mollie\Utility\TagsUtility::ppTags(
                        $this->l('You can find your API key in your [1]Mollie Profile[/1]; it starts with test or live.'),
                        [$this->display(__FILE__, 'views/templates/admin/profile.tpl')]
                    ),
                    'name' => Mollie\Config\Config::MOLLIE_API_KEY,
                    'required' => true,
                    'class' => 'fixed-width-xxl',
                ]
            ];
        } else {
            $input = [
                [
                    'type' => 'mollie-switch',
                    'label' => $this->l('Do you already have a Mollie account?'),
                    'name' => Mollie\Config\Config::MOLLIE_ACCOUNT_SWITCH,
                    'tab' => $generalSettings,
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'active_on',
                            'value' => true,
                            'label' => \Translate::getAdminTranslation('Enabled', 'AdminCarriers'),
                        ],
                        [
                            'id' => 'active_off',
                            'value' => false,
                            'label' => \Translate::getAdminTranslation('Disabled', 'AdminCarriers'),
                        ],
                    ],
                    'desc' => $this->context->smarty->fetch(
                        $this->getLocalPath() . 'views/templates/admin/create_new_account_link.tpl'
                    ),
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('API Key'),
                    'tab' => $generalSettings,
                    'desc' => \Mollie\Utility\TagsUtility::ppTags(
                        $this->l('You can find your API key in your [1]Mollie Profile[/1]; it starts with test or live.'),
                        [$this->display(__FILE__, 'views/templates/admin/profile.tpl')]
                    ),
                    'name' => Mollie\Config\Config::MOLLIE_API_KEY,
                    'required' => true,
                    'class' => 'fixed-width-xxl',
                ]
            ];
        }
        if ($isApiKeyProvided) {
            $input[] = [
                'type' => 'switch',
                'label' => $this->l('Use IFrame for credit card'),
                'tab' => $generalSettings,
                'name' => Mollie\Config\Config::MOLLIE_IFRAME,
                'is_bool' => true,
                'values' => [
                    [
                        'id' => 'active_on',
                        'value' => true,
                        'label' => \Translate::getAdminTranslation('Enabled', 'AdminCarriers'),
                    ],
                    [
                        'id' => 'active_off',
                        'value' => false,
                        'label' => \Translate::getAdminTranslation('Disabled', 'AdminCarriers'),
                    ],
                ],
            ];

            $input[] = [
                'type' => 'text',
                'label' => $this->l('Profile ID'),
                'tab' => $generalSettings,
                'desc' => \Mollie\Utility\TagsUtility::ppTags(
                    $this->l('You can find your API key in your [1]Mollie Profile[/1];'),
                    [$this->display(__FILE__, 'views/templates/admin/profile.tpl')]
                ),
                'name' => Mollie\Config\Config::MOLLIE_PROFILE_ID,
                'required' => true,
                'class' => 'fixed-width-xxl',
            ];

            $input = array_merge($input, [
                    [
                        'type' => 'mollie-h3',
                        'tab' => $generalSettings,
                        'name' => '',
                        'title' => $this->l('Orders API'),
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->l('Issuer list'),
                        'tab' => $generalSettings,
                        'desc' => $this->l('Some payment methods (eg. iDEAL) have an issuer list. This setting specifies where it is shown.'),
                        'name' => Mollie\Config\Config::MOLLIE_ISSUERS,
                        'options' => [
                            'query' => [
                                [
                                    'id' => Mollie\Config\Config::ISSUERS_ON_CLICK,
                                    'name' => $this->l('On click'),
                                ],
                                [
                                    'id' => Mollie\Config\Config::ISSUERS_PAYMENT_PAGE,
                                    'name' => $this->l('Payment page'),
                                ],
                            ],
                            'id' => 'id',
                            'name' => 'name',
                        ],
                    ],
                ]
            );
            $input[] = [
                'type' => 'mollie-h2',
                'tab' => $generalSettings,
                'name' => '',
                'title' => $this->l('Payment methods'),
            ];

            $input[] = [
                'type' => 'mollie-methods',
                'name' => Mollie\Config\Config::METHODS_CONFIG,
                'paymentMethods' => $apiService->getMethodsForConfig($this->api, $this->getPathUri()),
                'countries' => $countryService->getActiveCountriesList(),
                'tab' => $generalSettings,
            ];
        }

        return $input;
    }

    protected function getAdvancedSettingsSection()
    {
        $advancedSettings = 'advanced_settings';
        $input = [];
        $orderStatuses = [];
        $orderStatuses = array_merge($orderStatuses, OrderState::getOrderStates($this->context->language->id));
        $input[] = [
            'type' => 'select',
            'label' => $this->l('Send locale for payment screen'),
            'tab' => $advancedSettings,
            'desc' => \Mollie\Utility\TagsUtility::ppTags(
                $this->l('Should the plugin send the current webshop [1]locale[/1] to Mollie. Mollie payment screens will be in the same language as your webshop. Mollie can also detect the language based on the user\'s browser language.'),
                [$this->display(__FILE__, 'views/templates/admin/locale_wiki.tpl')]
            ),
            'name' => Mollie\Config\Config::MOLLIE_PAYMENTSCREEN_LOCALE,
            'options' => [
                'query' => [
                    [
                        'id' => Mollie\Config\Config::PAYMENTSCREEN_LOCALE_BROWSER_LOCALE,
                        'name' => $this->l('Do not send locale using browser language'),
                    ],
                    [
                        'id' => Mollie\Config\Config::PAYMENTSCREEN_LOCALE_SEND_WEBSITE_LOCALE,
                        'name' => $this->l('Send locale for payment screen'),
                    ],
                ],
                'id' => 'id',
                'name' => 'name',
            ],
        ];

        $lang = Context::getContext()->language->id;
        $messageStatus = $this->l('Status for %s payments');
        $descriptionStatus = $this->l('`%s` payments get status `%s`');
        $messageMail = $this->l('Send mails when %s');
        $descriptionMail = $this->l('Send mails when transaction status becomes %s?');
        $allStatuses = array_merge([['id_order_state' => 0, 'name' => $this->l('Skip this status'), 'color' => '#565656']], OrderState::getOrderStates($lang));
        $statuses = [];
        foreach (Mollie\Config\Config::getStatuses() as $name => $val) {
            if ($name === \Mollie\Api\Types\PaymentStatus::STATUS_AUTHORIZED) {
                continue;
            }

            $val = (int)$val;
            if ($val) {
                $desc = Tools::strtolower(
                    sprintf(
                        $descriptionStatus,
                        $this->lang($name),
                        Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
                            'SELECT `name`
                            FROM `' . _DB_PREFIX_ . 'order_state_lang`
                            WHERE `id_order_state` = ' . (int)$val . '
                            AND `id_lang` = ' . (int)$lang
                        )
                    )
                );
            } else {
                $desc = sprintf($this->l('`%s` payments do not get a status'), $this->lang($name));
            }
            $statuses[] = [
                'name' => $name,
                'key' => @constant('Mollie\Config\Config::MOLLIE_STATUS_' . Tools::strtoupper($name)),
                'value' => $val,
                'description' => $desc,
                'message' => sprintf($messageStatus, $this->lang($name)),
                'key_mail' => @constant('Mollie\Config\Config::MOLLIE_MAIL_WHEN_' . Tools::strtoupper($name)),
                'value_mail' => Configuration::get('MOLLIE_MAIL_WHEN_' . Tools::strtoupper($name)),
                'description_mail' => sprintf($descriptionMail, $this->lang($name)),
                'message_mail' => sprintf($messageMail, $this->lang($name)),
            ];
        }
        $input[] = [
            'type' => 'mollie-h2',
            'name' => '',
            'tab' => $advancedSettings,
            'title' => $this->l('Order statuses'),
        ];

        foreach (array_filter($statuses, function ($status) {
            return in_array($status['name'], [
                \Mollie\Api\Types\PaymentStatus::STATUS_PAID,
                \Mollie\Api\Types\PaymentStatus::STATUS_AUTHORIZED,
                \Mollie\Api\Types\PaymentStatus::STATUS_CANCELED,
                \Mollie\Api\Types\PaymentStatus::STATUS_EXPIRED,
                \Mollie\Api\Types\RefundStatus::STATUS_REFUNDED,
                \Mollie\Api\Types\PaymentStatus::STATUS_OPEN,
                'partial_refund',
            ]);
        }) as $status) {
            if (!in_array($status['name'], ['paid', 'partial_refund'])) {
                $input[] = [
                    'type' => 'switch',
                    'label' => $status['message_mail'],
                    'tab' => $advancedSettings,
                    'name' => $status['key_mail'],
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'active_on',
                            'value' => true,
                            'label' => \Translate::getAdminTranslation('Enabled', 'AdminCarriers'),
                        ],
                        [
                            'id' => 'active_off',
                            'value' => false,
                            'label' => \Translate::getAdminTranslation('Disabled', 'AdminCarriers'),
                        ],
                    ],
                ];
            }
            $input[] = [
                'type' => 'select',
                'label' => $status['message'],
                'tab' => $advancedSettings,
                'desc' => $status['description'],
                'name' => $status['key'],
                'options' => [
                    'query' => $allStatuses,
                    'id' => 'id_order_state',
                    'name' => 'name',
                ],
            ];
        }
        $input = array_merge($input, [
            [
                'type' => 'mollie-h2',
                'name' => '',
                'tab' => $advancedSettings,
                'title' => $this->l('Visual Settings'),
            ],
            [
                'type' => 'select',
                'label' => $this->l('Images'),
                'tab' => $advancedSettings,
                'desc' => $this->l('Show big, normal or no payment method logos on checkout.'),
                'name' => Mollie\Config\Config::MOLLIE_IMAGES,
                'options' => [
                    'query' => [
                        [
                            'id' => Mollie\Config\Config::LOGOS_HIDE,
                            'name' => $this->l('hide'),
                        ],
                        [
                            'id' => Mollie\Config\Config::LOGOS_NORMAL,
                            'name' => $this->l('normal'),
                        ],
                        [
                            'id' => Mollie\Config\Config::LOGOS_BIG,
                            'name' => $this->l('big'),
                        ],
                    ],
                    'id' => 'id',
                    'name' => 'name',
                ],
            ],

            [
                'type' => 'text',
                'label' => $this->l('CSS file'),
                'tab' => $advancedSettings,
                'desc' => \Mollie\Utility\TagsUtility::ppTags(
                    $this->l('Leave empty for default stylesheet. Should include file path when set. Hint: You can use [1]{BASE}[/1], [1]{THEME}[/1], [1]{CSS}[/1], [1]{MOBILE}[/1], [1]{MOBILE_CSS}[/1] and [1]{OVERRIDE}[/1] for easy folder mapping.'),
                    [$this->display(__FILE__, 'views/templates/front/kbd.tpl')]
                ),
                'name' => Mollie\Config\Config::MOLLIE_CSS,
                'class' => 'long-text',
            ],
        ]);
        $input[] = [
            'type' => 'mollie-carriers',
            'label' => $this->l('Shipment information'),
            'tab' => $advancedSettings,
            'name' => Mollie\Config\Config::MOLLIE_TRACKING_URLS,
            'depends' => Mollie\Config\Config::MOLLIE_API,
            'depends_value' => Mollie\Config\Config::MOLLIE_ORDERS_API,
        ];
        $input[] = [
            'type' => 'mollie-carrier-switch',
            'label' => $this->l('Automatically ship on marked statuses'),
            'tab' => $advancedSettings,
            'name' => Mollie\Config\Config::MOLLIE_AUTO_SHIP_MAIN,
            'desc' => $this->l('Enabling this feature will automatically send shipment information when an order gets marked status'),
            'is_bool' => true,
            'values' => [
                [
                    'id' => 'active_on',
                    'value' => true,
                    'label' => \Translate::getAdminTranslation('Enabled', 'AdminCarriers'),
                ],
                [
                    'id' => 'active_off',
                    'value' => false,
                    'label' => \Translate::getAdminTranslation('Disabled', 'AdminCarriers'),
                ],
            ],
            'depends' => Mollie\Config\Config::MOLLIE_API,
            'depends_value' => Mollie\Config\Config::MOLLIE_ORDERS_API,
        ];
        $input[] = [
            'type' => 'checkbox',
            'label' => $this->l('Automatically ship when one of these statuses is reached'),
            'tab' => $advancedSettings,
            'desc' =>
                $this->l('If an order reaches one of these statuses the module will automatically send shipment information'),
            'name' => Mollie\Config\Config::MOLLIE_AUTO_SHIP_STATUSES,
            'multiple' => true,
            'values' => [
                'query' => $orderStatuses,
                'id' => 'id_order_state',
                'name' => 'name',
            ],
            'expand' => (count($orderStatuses) > 10) ? [
                'print_total' => count($orderStatuses),
                'default' => 'show',
                'show' => ['text' => $this->l('Show'), 'icon' => 'plus-sign-alt'],
                'hide' => ['text' => $this->l('Hide'), 'icon' => 'minus-sign-alt'],
            ] : null,
            'depends' => Mollie\Config\Config::MOLLIE_API,
            'depends_value' => Mollie\Config\Config::MOLLIE_ORDERS_API,
        ];
        $orderStatuses = [
            [
                'name' => $this->l('Disable this status'),
                'id_order_state' => '0',
            ],
        ];
        $orderStatuses = array_merge($orderStatuses, OrderState::getOrderStates($this->context->language->id));

        for ($i = 0; $i < count($orderStatuses); $i++) {
            $orderStatuses[$i]['name'] = $orderStatuses[$i]['id_order_state'] . ' - ' . $orderStatuses[$i]['name'];
        }

        \Mollie\Utility\AssortUtility::aasort($orderStatuses, 'id_order_state');

        $this->context->smarty->assign([
            'logs' => $this->context->link->getAdminLink('AdminLogs')
        ]);
        $input = array_merge(
            $input,
            [
                [
                    'type' => 'mollie-h2',
                    'name' => '',
                    'title' => $this->l('Debug level'),
                    'tab' => $advancedSettings,
                ],
                [
                    'type' => 'switch',
                    'label' => $this->l('Display errors'),
                    'tab' => $advancedSettings,
                    'name' => Mollie\Config\Config::MOLLIE_DISPLAY_ERRORS,
                    'desc' => $this->l('Enabling this feature will display error messages (if any) on the front page. Use for debug purposes only!'),
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'active_on',
                            'value' => true,
                            'label' => \Translate::getAdminTranslation('Enabled', 'AdminCarriers'),
                        ],
                        [
                            'id' => 'active_off',
                            'value' => false,
                            'label' => \Translate::getAdminTranslation('Disabled', 'AdminCarriers'),
                        ],
                    ],
                ],
                [
                    'type' => 'select',
                    'label' => $this->l('Log level'),
                    'tab' => $advancedSettings,
                    'desc' => \Mollie\Utility\TagsUtility::ppTags(
                        $this->l('Recommended level: Errors. Set to Everything to monitor incoming webhook requests. [1]View logs.[/1]'),
                        [
                            $this->display(__FILE__, 'views/templates/admin/view_logs.tpl')
                        ]
                    ),
                    'name' => Mollie\Config\Config::MOLLIE_DEBUG_LOG,
                    'options' => [
                        'query' => [
                            [
                                'id' => Mollie\Config\Config::DEBUG_LOG_NONE,
                                'name' => $this->l('Nothing'),
                            ],
                            [
                                'id' => Mollie\Config\Config::DEBUG_LOG_ERRORS,
                                'name' => $this->l('Errors'),
                            ],
                            [
                                'id' => Mollie\Config\Config::DEBUG_LOG_ALL,
                                'name' => $this->l('Everything'),
                            ],
                        ],
                        'id' => 'id',
                        'name' => 'name',
                    ],
                ],
            ]
        );
        return $input;
    }

    /**
     * @return array
     * @throws PrestaShopException
     */
    protected function getConfigFieldsValues()
    {
        $configFields = [
            Mollie\Config\Config::MOLLIE_API_KEY => Configuration::get(Mollie\Config\Config::MOLLIE_API_KEY),
            Mollie\Config\Config::MOLLIE_PROFILE_ID => Configuration::get(Mollie\Config\Config::MOLLIE_PROFILE_ID),
            Mollie\Config\Config::MOLLIE_PAYMENTSCREEN_LOCALE => Configuration::get(Mollie\Config\Config::MOLLIE_PAYMENTSCREEN_LOCALE),
            Mollie\Config\Config::MOLLIE_IFRAME => Configuration::get(Mollie\Config\Config::MOLLIE_IFRAME),

            Mollie\Config\Config::MOLLIE_CSS => Configuration::get(Mollie\Config\Config::MOLLIE_CSS),
            Mollie\Config\Config::MOLLIE_IMAGES => Configuration::get(Mollie\Config\Config::MOLLIE_IMAGES),
            Mollie\Config\Config::MOLLIE_ISSUERS => Configuration::get(Mollie\Config\Config::MOLLIE_ISSUERS),

            Mollie\Config\Config::MOLLIE_QRENABLED => Configuration::get(Mollie\Config\Config::MOLLIE_QRENABLED),
            Mollie\Config\Config::MOLLIE_METHOD_COUNTRIES => Configuration::get(Mollie\Config\Config::MOLLIE_METHOD_COUNTRIES),
            Mollie\Config\Config::MOLLIE_METHOD_COUNTRIES_DISPLAY => Configuration::get(Mollie\Config\Config::MOLLIE_METHOD_COUNTRIES_DISPLAY),

            Mollie\Config\Config::MOLLIE_STATUS_OPEN => Configuration::get(Mollie\Config\Config::MOLLIE_STATUS_OPEN),
            Mollie\Config\Config::MOLLIE_STATUS_PAID => Configuration::get(Mollie\Config\Config::MOLLIE_STATUS_PAID),
            Mollie\Config\Config::MOLLIE_STATUS_CANCELED => Configuration::get(Mollie\Config\Config::MOLLIE_STATUS_CANCELED),
            Mollie\Config\Config::MOLLIE_STATUS_EXPIRED => Configuration::get(Mollie\Config\Config::MOLLIE_STATUS_EXPIRED),
            Mollie\Config\Config::MOLLIE_STATUS_PARTIAL_REFUND => Configuration::get(Mollie\Config\Config::MOLLIE_STATUS_PARTIAL_REFUND),
            Mollie\Config\Config::MOLLIE_STATUS_REFUNDED => Configuration::get(Mollie\Config\Config::MOLLIE_STATUS_REFUNDED),
            Mollie\Config\Config::MOLLIE_MAIL_WHEN_OPEN => Configuration::get(Mollie\Config\Config::MOLLIE_MAIL_WHEN_OPEN),
            Mollie\Config\Config::MOLLIE_MAIL_WHEN_PAID => Configuration::get(Mollie\Config\Config::MOLLIE_MAIL_WHEN_PAID),
            Mollie\Config\Config::MOLLIE_MAIL_WHEN_CANCELED => Configuration::get(Mollie\Config\Config::MOLLIE_MAIL_WHEN_CANCELED),
            Mollie\Config\Config::MOLLIE_MAIL_WHEN_EXPIRED => Configuration::get(Mollie\Config\Config::MOLLIE_MAIL_WHEN_EXPIRED),
            Mollie\Config\Config::MOLLIE_MAIL_WHEN_REFUNDED => Configuration::get(Mollie\Config\Config::MOLLIE_MAIL_WHEN_REFUNDED),
            Mollie\Config\Config::MOLLIE_ACCOUNT_SWITCH => Configuration::get(Mollie\Config\Config::MOLLIE_ACCOUNT_SWITCH),

            Mollie\Config\Config::MOLLIE_DISPLAY_ERRORS => Configuration::get(Mollie\Config\Config::MOLLIE_DISPLAY_ERRORS),
            Mollie\Config\Config::MOLLIE_DEBUG_LOG => Configuration::get(Mollie\Config\Config::MOLLIE_DEBUG_LOG),
            Mollie\Config\Config::MOLLIE_API => Configuration::get(Mollie\Config\Config::MOLLIE_API),

            Mollie\Config\Config::MOLLIE_AUTO_SHIP_MAIN => Configuration::get(Mollie\Config\Config::MOLLIE_AUTO_SHIP_MAIN),
        ];

        /** @var \Mollie\Service\ApiService $apiService */
        $apiService = $this->getContainer(\Mollie\Service\ApiService::class);
        /** @var \Mollie\Repository\CountryRepository $countryRepo */
        $countryRepo = $this->getContainer(\Mollie\Repository\CountryRepository::class);
        if (Configuration::get(Mollie\Config\Config::MOLLIE_API_KEY)) {
            foreach ($apiService->getMethodsForConfig($this->api, $this->getPathUri()) as $method) {
                $countryIds = $countryRepo->getMethodCountryIds($method['id']);
                if ($countryIds) {
                    $configFields = array_merge($configFields, [Mollie\Config\Config::MOLLIE_COUNTRIES . $method['id'] . '[]' => $countryIds]);
                    continue;
                }
                $configFields = array_merge($configFields, [Mollie\Config\Config::MOLLIE_COUNTRIES . $method['id'] . '[]' => []]);
            }
        }

        $checkStatuses = [];
        if (Configuration::get(Mollie\Config\Config::MOLLIE_AUTO_SHIP_STATUSES)) {
            $checkConfs = @json_decode(Configuration::get(Mollie\Config\Config::MOLLIE_AUTO_SHIP_STATUSES), true);
        }
        if (!isset($checkConfs) || !is_array($checkConfs)) {
            $checkConfs = [];
        }

        foreach ($checkConfs as $conf) {
            $checkStatuses[Mollie\Config\Config::MOLLIE_AUTO_SHIP_STATUSES . '_' . (int)$conf] = true;
        }

        $configFields = array_merge($configFields, $checkStatuses);

        return $configFields;
    }

    /**
     * @param string $str
     *
     * @return string
     */
    public function lang($str)
    {
        /** @var \Mollie\Utility\LanguageUtility $langUtility */
        $langUtility = $this->getContainer(\Mollie\Utility\LanguageUtility::class);
        $lang = $langUtility->getLang();
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
                        ]);
                        $updateMessage = 'updateAvailable';
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
        if ($this->context->controller instanceof OrderControllerCore) {

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
        $paymentMethodRepo = $this->getContainer(\Mollie\Repository\PaymentMethodRepository::class);
        /** @var \Mollie\Service\ShipmentService $shipmentService */
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

        $this->context->smarty->assign([
            'ajaxEndpoint' => $this->context->link->getAdminLink('AdminModules', true) . '&configure=mollie&ajax=1&action=MollieOrderInfo',
            'transactionId' => $transaction['transaction_id'],
            'currencies' => $currencies,
            'tracking' => $shipmentService->getShipmentInformation($params['id_order']),
            'publicPath' => __PS_BASE_URI__ . 'modules/' . basename(__FILE__, '.php') . '/views/js/dist/',
            'webPackChunks' => \Mollie\Utility\UrlPathUtility::getWebpackChunks('app'),
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
        $apiMethods = $this->getMethodsForCheckout();
        $issuerList = [];
        foreach ($apiMethods as $apiMethod) {
            if ($apiMethod['id'] === \Mollie\Api\Types\PaymentMethod::IDEAL) {
                $issuerList[\Mollie\Api\Types\PaymentMethod::IDEAL] = [];
                foreach ($apiMethod['issuers'] as $issuer) {
                    $issuer['href'] = $this->context->link->getModuleLink(
                        $this->name,
                        'payment',
                        ['method' => $apiMethod['id'], 'issuer' => $issuer['id'], 'rand' => time()],
                        true
                    );
                    $issuerList[\Mollie\Api\Types\PaymentMethod::IDEAL][$issuer['id']] = $issuer;
                }
            }
        }

        $isIFrameEnabled = Configuration::get(Mollie\Config\Config::MOLLIE_IFRAME);
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

        $methods = $this->getMethodsForCheckout();
        $issuerList = [];
        foreach ($methods as $apiMethod) {
            if ($apiMethod['id'] === \Mollie\Api\Types\PaymentMethod::IDEAL) {
                $issuerList[\Mollie\Api\Types\PaymentMethod::IDEAL] = [];
                foreach ($apiMethod['issuers'] as $issuer) {
                    $issuer['href'] = $this->context->link->getModuleLink(
                        $this->name,
                        'payment',
                        ['method' => $apiMethod['id'], 'issuer' => $issuer['id'], 'rand' => time()],
                        true
                    );
                    $issuerList[\Mollie\Api\Types\PaymentMethod::IDEAL][$issuer['id']] = $issuer;
                }
            }
        }

        $context = Context::getContext();
        $iso = Tools::strtolower($context->currency->iso_code);
        $paymentOptions = [];

        foreach ($methods as $method) {
            if (!isset(Mollie\Config\Config::$methodCurrencies[$method['id']])) {
                continue;
            }
            if (!in_array($iso, Mollie\Config\Config::$methodCurrencies[$method['id']])) {
                continue;
            }

            $paymentOptions[] = [
                'cta_text' => $this->lang($method['name']),
                'logo' => Configuration::get(Mollie\Config\Config::MOLLIE_IMAGES) === Mollie\Config\Config::LOGOS_NORMAL
                    ? $method['image']['size1x']
                    : $method['image']['size2x'],
                'action' => $this->context->link->getModuleLink(
                    'mollie',
                    'payment',
                    ['method' => $method['id'], 'rand' => time()],
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

        $methodIds = $this->getMethodsForCheckout();
        $issuerList = [];
        foreach ($methodIds as $methodId) {
            $methodObj = new MolPaymentMethod($methodId['id_payment_method']);
            if ($methodObj->id_method === \Mollie\Api\Types\PaymentMethod::IDEAL) {
                /** @var \Mollie\Repository\PaymentMethodRepository $paymentMethodRepo */
                $paymentMethodRepo = $this->getContainer(\Mollie\Repository\PaymentMethodRepository::class);
                $issuersJson = $paymentMethodRepo->getPaymentMethodIssuersByPaymentMethodId($methodObj->id);
                $issuers = json_decode($issuersJson, true);
                $issuerList[\Mollie\Api\Types\PaymentMethod::IDEAL] = [];
                foreach ($issuers as $issuer) {
                    $issuer['href'] = $this->context->link->getModuleLink(
                        $this->name,
                        'payment',
                        ['method' => $methodObj->id_method , 'issuer' => $issuer['id'], 'rand' => time()],
                        true
                    );
                    $issuerList[\Mollie\Api\Types\PaymentMethod::IDEAL][$issuer['id']] = $issuer;
                }
            }
        }

        $context = Context::getContext();
        $cart = $context->cart;

        $context->smarty->assign([
            'idealIssuers' => isset($issuerList[\Mollie\Api\Types\PaymentMethod::IDEAL])
                ? $issuerList[\Mollie\Api\Types\PaymentMethod::IDEAL]
                : [],
            'link' => $this->context->link,
            'qrCodeEnabled' => Configuration::get(Mollie\Config\Config::MOLLIE_QRENABLED),
            'qrAlign' => 'left',
            'cartAmount' => (int)($cart->getOrderTotal(true) * 100),
            'publicPath' => __PS_BASE_URI__ . 'modules/' . basename(__FILE__, '.php') . '/views/js/dist/',
        ]);

        $iso = Tools::strtolower($context->currency->iso_code);
        $paymentOptions = [];
        foreach ($methodIds as $methodId) {
            $methodObj = new MolPaymentMethod($methodId['id_payment_method']);
            $paymentFee = \Mollie\Utility\PaymentFeeUtility::getPaymentFee($methodObj, $cart->getOrderTotal());
            if (!isset(Mollie\Config\Config::$methodCurrencies[$methodObj->id_method])) {
                continue;
            }
            if (!in_array($iso, Mollie\Config\Config::$methodCurrencies[$methodObj->id_method])) {
                continue;
            }

            $imageConfig = Configuration::get(Mollie\Config\Config::MOLLIE_IMAGES);
            $image = json_decode($methodObj->images_json, true);

            if ($methodObj->id_method === \Mollie\Api\Types\PaymentMethod::IDEAL
                && Configuration::get(Mollie\Config\Config::MOLLIE_ISSUERS) === Mollie\Config\Config::ISSUERS_ON_CLICK
            ) {
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

                $imageConfig = Configuration::get(Mollie\Config\Config::MOLLIE_IMAGES);
                $image = json_decode($methodObj->images_json, true);
                if ($imageConfig === Mollie\Config\Config::LOGOS_NORMAL) {
                    $newOption->setLogo($image['svg']);
                } elseif ($imageConfig === Mollie\Config\Config::LOGOS_BIG) {
                    $newOption->setLogo($image['size2x']);
                }

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
                ($methodObj->id_method === Mollie\Api\Types\PaymentMethod::CREDITCARD || $methodObj->id_method === 'cartesbancaires') &&
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

                $imageConfig = Configuration::get(Mollie\Config\Config::MOLLIE_IMAGES);
                if ($imageConfig === Mollie\Config\Config::LOGOS_NORMAL) {
                    $newOption->setLogo($image['svg']);
                } elseif ($imageConfig === Mollie\Config\Config::LOGOS_BIG) {
                    $newOption->setLogo($image['size2x']);
                }

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

                $imageConfig = Configuration::get(Mollie\Config\Config::MOLLIE_IMAGES);
                if ($imageConfig === Mollie\Config\Config::LOGOS_NORMAL) {
                    $newOption->setLogo($image['svg']);
                } elseif ($imageConfig === Mollie\Config\Config::LOGOS_BIG) {
                    $newOption->setLogo($image['size2x']);
                }

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
        if ($payment && $payment['bank_status'] == \Mollie\Api\Types\PaymentStatus::STATUS_PAID) {
            $this->context->smarty->assign('okMessage', $this->l('Thank you. Your payment has been received.'));

            return $this->display(__FILE__, 'ok.tpl');
        }

        return '';
    }

    /**
     * @return bool
     */
    public function addCartIdChangePrimaryKey()
    {
        $sql = sprintf(
            '
			ALTER TABLE `%1$s` DROP PRIMARY KEY;
			ALTER TABLE `%1$s` ADD PRIMARY KEY (`transaction_id`),
				ADD COLUMN `cart_id` INT(64),
				ADD KEY (`cart_id`);',
            _DB_PREFIX_ . 'mollie_payments'
        );

        try {
            if (!Db::getInstance()->execute($sql)) {
                $this->_errors[] = 'Database error: ' . Db::getInstance()->getMsgError();

                return false;
            }
        } catch (PrestaShopException $e) {
            /** @var \Mollie\Repository\PaymentMethodRepository $paymentMethodRepo */
            $paymentMethodRepo = $this->getContainer(\Mollie\Repository\PaymentMethodRepository::class);
            $paymentMethodRepo->tryAddOrderReferenceColumn();
            $this->_errors[] = 'Database error: ' . Db::getInstance()->getMsgError();

            return false;
        }

        return true;
    }

    /**
     * @param string $mediaUri
     * @param string|null $cssMediaType
     *
     * @return array|bool|mixed|string
     *
     * @since   1.0.0
     * @version 1.0.0 Initial version
     */
    public static function getMediaPath($mediaUri, $cssMediaType = null)
    {
        if (is_array($mediaUri) || $mediaUri === null || empty($mediaUri)) {
            return false;
        }

        $urlData = parse_url($mediaUri);
        if (!is_array($urlData)) {
            return false;
        }

        if (!array_key_exists('host', $urlData)) {
            $mediaUri = '/' . ltrim(str_replace(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, _PS_ROOT_DIR_), __PS_BASE_URI__, $mediaUri), '/\\');
            // remove PS_BASE_URI on _PS_ROOT_DIR_ for the following
            $fileUri = _PS_ROOT_DIR_ . Tools::str_replace_once(__PS_BASE_URI__, DIRECTORY_SEPARATOR, $mediaUri);

            if (!@filemtime($fileUri) || @filesize($fileUri) === 0) {
                return false;
            }

            $mediaUri = str_replace('//', '/', $mediaUri);
        }

        if ($cssMediaType) {
            return [$mediaUri => $cssMediaType];
        }

        return $mediaUri;
    }

    /**
     * Get payment data
     *
     * @param float|string $amount
     * @param              $currency
     * @param string $method
     * @param string|null $issuer
     * @param int|Cart $cartId
     * @param string $secureKey
     * @param bool $qrCode
     * @param string $orderReference
     *
     * @return array
     * @throws PrestaShopException
     * @throws Adapter_Exception
     * @throws CoreException
     *
     * @since 3.3.0 Order reference
     */
    public static function getPaymentData(
        $amount,
        $currency,
        $method,
        $issuer,
        $cartId,
        $secureKey,
        MolPaymentMethod $molPaymentMethod,
        $qrCode = false,
        $orderReference = ''
    ) {
        if (!$orderReference) {
            /** @var Mollie $module */
            $module = Module::getInstanceByName('mollie');
            $module->currentOrderReference = $orderReference = Order::generateReference();
        }
        $description = static::generateDescriptionFromCart($molPaymentMethod->description, $cartId, $orderReference);
        $context = Context::getContext();
        $cart = new Cart($cartId);
        $customer = new Customer($cart->id_customer);

        $paymentFee = \Mollie\Utility\PaymentFeeUtility::getPaymentFee($molPaymentMethod, $amount);
        $totalAmount = (number_format(str_replace(',', '.', $amount), 2, '.', ''));
        $totalAmount += $paymentFee;

        $paymentData = [
            'amount' => [
                'currency' => (string)($currency ? Tools::strtoupper($currency) : 'EUR'),
                'value' => (string)number_format($totalAmount, 2, '.', ''),
            ],
            'method' => $method,
            'redirectUrl' => ($qrCode
                ? $context->link->getModuleLink(
                    'mollie',
                    'qrcode',
                    ['cart_id' => $cartId, 'done' => 1, 'rand' => time()],
                    true
                )
                : $context->link->getModuleLink(
                    'mollie',
                    'return',
                    ['cart_id' => $cartId, 'utm_nooverride' => 1, 'rand' => time()],
                    true
                )
            ),
        ];
        if (!\Mollie\Utility\EnvironmentUtility::isLocalEnvironment()) {
            $paymentData['webhookUrl'] = $context->link->getModuleLink(
                'mollie',
                'webhook',
                [],
                true
            );
        }

        $paymentData['metadata'] = [
            'cart_id' => $cartId,
            'order_reference' => $orderReference,
            'secure_key' => Tools::encrypt($secureKey),
        ];

        // Send webshop locale
        if (($molPaymentMethod->method === Mollie\Config\Config::MOLLIE_PAYMENTS_API
                && Configuration::get(Mollie\Config\Config::MOLLIE_PAYMENTSCREEN_LOCALE) === Mollie\Config\Config::PAYMENTSCREEN_LOCALE_SEND_WEBSITE_LOCALE)
            || $molPaymentMethod->method === Mollie\Config\Config::MOLLIE_ORDERS_API
        ) {
            $locale = static::getWebshopLocale();
            if (preg_match(
                '/^[a-z]{2}(?:[\-_][A-Z]{2})?$/iu',
                $locale
            )) {
                $paymentData['locale'] = $locale;
            }
        }

        if ($molPaymentMethod->method === Mollie\Config\Config::MOLLIE_PAYMENTS_API) {
            $paymentData['description'] = str_ireplace(
                ['%'],
                [$cartId],
                $description
            );
            $paymentData['issuer'] = $issuer;

            if (isset($context->cart) && Tools::getValue('method') === 'paypal') {
                if (isset($context->cart->id_customer)) {
                    $buyer = new Customer($context->cart->id_customer);
                    $paymentData['billingEmail'] = (string)$buyer->email;
                }
                if (isset($context->cart->id_address_invoice)) {
                    $billing = new Address((int)$context->cart->id_address_invoice);
                    $paymentData['billingAddress'] = [
                        'streetAndNumber' => (string)$billing->address1 . ' ' . $billing->address2,
                        'city' => (string)$billing->city,
                        'region' => (string)State::getNameById($billing->id_state),
                        'country' => (string)Country::getIsoById($billing->id_country),
                    ];
                    $paymentData['billingAddress']['postalCode'] = (string)$billing->postcode ?: '-';
                }
                if (isset($context->cart->id_address_delivery)) {
                    $shipping = new Address((int)$context->cart->id_address_delivery);
                    $paymentData['shippingAddress'] = [
                        'streetAndNumber' => (string)$shipping->address1 . ' ' . $shipping->address2,
                        'city' => (string)$shipping->city,
                        'region' => (string)State::getNameById($shipping->id_state),
                        'country' => (string)Country::getIsoById($shipping->id_country),
                    ];
                    $paymentData['shippingAddress']['postalCode'] = (string)$shipping->postcode ?: '-';
                }
            }

            switch ($method) {
                case \Mollie\Api\Types\PaymentMethod::BANKTRANSFER:
                    $paymentData['billingEmail'] = $customer->email;
                    $paymentData['locale'] = static::getWebshopLocale();
                    break;
                case \Mollie\Api\Types\PaymentMethod::BITCOIN:
                    $paymentData['billingEmail'] = $customer->email;
                    break;
            }
        } elseif ($molPaymentMethod->method === Mollie\Config\Config::MOLLIE_ORDERS_API) {
            if (isset($cart->id_address_invoice)) {
                $billing = new Address((int)$cart->id_address_invoice);
                $paymentData['billingAddress'] = [
                    'givenName' => (string)$customer->firstname,
                    'familyName' => (string)$customer->lastname,
                    'email' => (string)$customer->email,
                    'streetAndNumber' => (string)$billing->address1 . ' ' . $billing->address2,
                    'city' => (string)$billing->city,
                    'region' => (string)State::getNameById($billing->id_state),
                    'country' => (string)Country::getIsoById($billing->id_country),
                ];
                $paymentData['billingAddress']['postalCode'] = (string)$billing->postcode ?: '-';
            }
            if (isset($cart->id_address_delivery)) {
                $shipping = new Address((int)$cart->id_address_delivery);
                $paymentData['shippingAddress'] = [
                    'givenName' => (string)$customer->firstname,
                    'familyName' => (string)$customer->lastname,
                    'email' => (string)$customer->email,
                    'streetAndNumber' => (string)$shipping->address1 . ' ' . $shipping->address2,
                    'city' => (string)$shipping->city,
                    'region' => (string)State::getNameById($shipping->id_state),
                    'country' => (string)Country::getIsoById($shipping->id_country),
                ];
                $paymentData['shippingAddress']['postalCode'] = (string)$shipping->postcode ?: '-';
            }
            $paymentData['orderNumber'] = $orderReference;
            $paymentData['lines'] = static::getCartLines($amount, $paymentFee);
            $paymentData['payment'] = [];
            if (!\Mollie\Utility\EnvironmentUtility::isLocalEnvironment()) {
                $paymentData['payment']['webhookUrl'] = $context->link->getModuleLink(
                    'mollie',
                    'webhook',
                    [],
                    true
                );
            }
            if ($issuer) {
                $paymentData['payment']['issuer'] = $issuer;
            }
        }

        return $paymentData;
    }

    /**
     * @param float $amount
     *
     * @return array
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public static function getCartLines($amount, $paymentFee)
    {
        /** @var Cart $cart */
        $cart = Context::getContext()->cart;
        /** @var static $mollie */
        $mollie = Module::getInstanceByName('mollie');
        $oCurrency = new Currency($cart->id_currency);
        $apiRoundingPrecision = Mollie\Config\Config::API_ROUNDING_PRECISION; // PHP 5.3, closures and static access, not a good combo :(

        $remaining = round($amount, $apiRoundingPrecision);
        $shipping = round($cart->getTotalShippingCost(null, true), $apiRoundingPrecision);
        $cartSummary = $cart->getSummaryDetails();
        $cartItems = $cart->getProducts();
        $wrapping = Configuration::get('PS_GIFT_WRAPPING') ? round($cartSummary['total_wrapping'], $apiRoundingPrecision) : 0;
        $remaining = round($remaining - $shipping - $wrapping, $apiRoundingPrecision);
        $totalDiscounts = isset($cartSummary['total_discounts']) ? round($cartSummary['total_discounts'], $apiRoundingPrecision) : 0;

        $aItems = [];
        /* Item */
        foreach ($cartItems as $cartItem) {
            // Get the rounded total w/ tax
            $roundedTotalWithTax = round($cartItem['total_wt'], $apiRoundingPrecision);

            // Skip if no qty
            $quantity = (int)$cartItem['cart_quantity'];
            if ($quantity <= 0 || $cartItem['price_wt'] <= 0) {
                continue;
            }

            // Generate the product hash
            $idProduct = number_format($cartItem['id_product']);
            $idProductAttribute = number_format($cartItem['id_product_attribute']);
            $idCustomization = number_format($cartItem['id_customization']);

            $productHash = "{$idProduct}¤{$idProductAttribute}¤{$idCustomization}";
            $aItems[$productHash] = [];

            // Try to spread this product evenly and account for rounding differences on the order line
            foreach (\Mollie\Utility\CartPriceUtility::spreadAmountEvenly($roundedTotalWithTax, $quantity) as $unitPrice => $qty) {
                $aItems[$productHash][] = [
                    'name' => $cartItem['name'],
                    'sku' => $productHash,
                    'targetVat' => (float)$cartItem['rate'],
                    'quantity' => $qty,
                    'unitPrice' => $unitPrice,
                    'totalAmount' => (float)$unitPrice * $qty,
                ];
                $remaining -= round((float)$unitPrice * $qty, $apiRoundingPrecision);
            }
        }

        // Add discount if applicable
        if ($totalDiscounts >= 0.01) {
            $totalDiscountsNoTax = round($cartSummary['total_discounts_tax_exc'], $apiRoundingPrecision);
            $vatRate = round((($totalDiscounts - $totalDiscountsNoTax) / $totalDiscountsNoTax) * 100, $apiRoundingPrecision);

            $aItems['discount'] = [
                [
                    'name' => 'Discount',
                    'type' => 'discount',
                    'quantity' => 1,
                    'unitPrice' => -round($totalDiscounts, $apiRoundingPrecision),
                    'totalAmount' => -round($totalDiscounts, $apiRoundingPrecision),
                    'targetVat' => $vatRate,
                ],
            ];
            $remaining += $totalDiscounts;
        }

        // Compensate for order total rounding inaccuracies
        $remaining = round($remaining, $apiRoundingPrecision);
        if ($remaining < 0) {
            foreach (array_reverse($aItems) as $hash => $items) {
                // Grab the line group's total amount
                $totalAmount = array_sum(array_column($items, 'totalAmount'));

                // Remove when total is lower than remaining
                if ($totalAmount <= $remaining) {
                    // The line total is less than remaining, we should remove this line group and continue
                    $remaining = $remaining - $totalAmount;
                    unset($items);
                    continue;
                }

                // Otherwise spread the cart line again with the updated total
                $aItems[$hash] = static::spreadCartLineGroup($items, $totalAmount - $remaining);
                break;
            }
        } elseif ($remaining > 0) {
            foreach (array_reverse($aItems) as $hash => $items) {
                // Grab the line group's total amount
                $totalAmount = array_sum(array_column($items, 'totalAmount'));
                // Otherwise spread the cart line again with the updated total
                $aItems[$hash] = static::spreadCartLineGroup($items, $totalAmount + $remaining);
                break;
            }
        }

        // Fill the order lines with the rest of the data (tax, total amount, etc.)
        foreach ($aItems as $productHash => $aItem) {
            $aItems[$productHash] = array_map(function ($line) use ($apiRoundingPrecision, $oCurrency) {
                $quantity = (int)$line['quantity'];
                $targetVat = $line['targetVat'];
                $unitPrice = $line['unitPrice'];
                $unitPriceNoTax = round($line['unitPrice'] / (1 + ($targetVat / 100)), $apiRoundingPrecision);

                // Calculate VAT
                $totalAmount = round($unitPrice * $quantity, $apiRoundingPrecision);
                $actualVatRate = round(($unitPrice * $quantity - $unitPriceNoTax * $quantity) / ($unitPriceNoTax * $quantity) * 100, $apiRoundingPrecision);
                $vatAmount = $totalAmount * ($actualVatRate / ($actualVatRate + 100));

                $newItem = [
                    'name' => $line['name'],
                    'quantity' => (int)$quantity,
                    'unitPrice' => round($unitPrice, $apiRoundingPrecision),
                    'totalAmount' => round($totalAmount, $apiRoundingPrecision),
                    'vatRate' => round($actualVatRate, $apiRoundingPrecision),
                    'vatAmount' => round($vatAmount, $apiRoundingPrecision),
                ];
                if (isset($line['sku'])) {
                    $newItem['sku'] = $line['sku'];
                }

                return $newItem;
            }, $aItem);
        }

        // Add shipping
        if (round($shipping, 2) > 0) {
            $shippingVatRate = round(($cartSummary['total_shipping'] - $cartSummary['total_shipping_tax_exc']) / $cartSummary['total_shipping_tax_exc'] * 100, $apiRoundingPrecision);

            $aItems['shipping'] = [
                [
                    'name' => $mollie->l('Shipping'),
                    'quantity' => 1,
                    'unitPrice' => round($shipping, $apiRoundingPrecision),
                    'totalAmount' => round($shipping, $apiRoundingPrecision),
                    'vatAmount' => round($shipping * $shippingVatRate / ($shippingVatRate + 100), $apiRoundingPrecision),
                    'vatRate' => $shippingVatRate,
                ],
            ];
        }

        // Add wrapping
        if (round($wrapping, 2) > 0) {
            $wrappingVatRate = round(($cartSummary['total_wrapping'] - $cartSummary['total_wrapping_tax_exc']) / $cartSummary['total_wrapping_tax_exc'] * 100, $apiRoundingPrecision);

            $aItems['wrapping'] = [
                [
                    'name' => $mollie->l('Gift wrapping'),
                    'quantity' => 1,
                    'unitPrice' => round($wrapping, $apiRoundingPrecision),
                    'totalAmount' => round($wrapping, $apiRoundingPrecision),
                    'vatAmount' => round($wrapping * $wrappingVatRate / ($wrappingVatRate + 100), $apiRoundingPrecision),
                    'vatRate' => $wrappingVatRate,
                ],
            ];
        }

        // Add fee
        if ($paymentFee) {
            $aItems['surcharge'] = [
                [
                    'name' => $mollie->l('Payment Fee'),
                    'quantity' => 1,
                    'unitPrice' => round($paymentFee, $apiRoundingPrecision),
                    'totalAmount' => round($paymentFee, $apiRoundingPrecision),
                    'vatAmount' => 0,
                    'vatRate' => 0,
                ],
            ];
        }

        // Ungroup all the cart lines, just one level
        $newItems = [];
        foreach ($aItems as &$items) {
            foreach ($items as &$item) {
                $newItems[] = $item;
            }
        }

        // Convert floats to strings for the Mollie API and add additional info
        foreach ($newItems as $index => $item) {
            $newItems[$index] = [
                'name' => (string)$item['name'],
                'quantity' => (int)$item['quantity'],
                'sku' => (string)(isset($item['sku']) ? $item['sku'] : ''),
                'unitPrice' => [
                    'currency' => Tools::strtoupper($oCurrency->iso_code),
                    'value' => number_format($item['unitPrice'], $apiRoundingPrecision, '.', ''),
                ],
                'totalAmount' => [
                    'currency' => Tools::strtoupper($oCurrency->iso_code),
                    'value' => number_format($item['totalAmount'], $apiRoundingPrecision, '.', ''),
                ],
                'vatAmount' => [
                    'currency' => Tools::strtoupper($oCurrency->iso_code),
                    'value' => number_format($item['vatAmount'], $apiRoundingPrecision, '.', ''),
                ],
                'vatRate' => number_format($item['vatRate'], $apiRoundingPrecision, '.', ''),
            ];
        }

        return $newItems;
    }


    /**
     * Spread the cart line amount evenly
     *
     * Optionally split into multiple lines in case of rounding inaccuracies
     *
     * @param array[] $cartLineGroup Cart Line Group WITHOUT VAT details (except target VAT rate)
     * @param float $newTotal
     *
     * @return array[]
     *
     * @since 3.2.2
     * @since 3.3.3 Omits VAT details
     */
    public static function spreadCartLineGroup($cartLineGroup, $newTotal)
    {
        $apiRoundingPrecision = Mollie\Config\Config::API_ROUNDING_PRECISION;
        $newTotal = round($newTotal, $apiRoundingPrecision);
        $quantity = array_sum(array_column($cartLineGroup, 'quantity'));
        $newCartLineGroup = [];
        $spread = \Mollie\Utility\CartPriceUtility::spreadAmountEvenly($newTotal, $quantity);
        foreach ($spread as $unitPrice => $qty) {
            $newCartLineGroup[] = [
                'name' => $cartLineGroup[0]['name'],
                'quantity' => $qty,
                'unitPrice' => (float)$unitPrice,
                'totalAmount' => (float)$unitPrice * $qty,
                'sku' => $cartLineGroup[0]['sku'],
                'targetVat' => $cartLineGroup[0]['targetVat'],
            ];
        }

        return $newCartLineGroup;
    }

    /**
     * Generate a description from the Cart
     *
     * @param Cart|int $cartId Cart or Cart ID
     * @param string $orderReference Order reference
     *
     * @return string Description
     *
     * @throws PrestaShopException
     * @throws CoreException
     * @since 3.0.0
     */
    public static function generateDescriptionFromCart($methodDescription, $cartId, $orderReference = '')
    {
        if ($cartId instanceof Cart) {
            $cart = $cartId;
        } else {
            $cart = new Cart($cartId);
        }

        $buyer = null;
        if ($cart->id_customer) {
            $buyer = new Customer($cart->id_customer);
        }

        $filters = [
            '%' => $cartId,
            '{cart.id}' => $cartId,
            '{order.reference}' => $orderReference,
            '{customer.firstname}' => $buyer == null ? '' : $buyer->firstname,
            '{customer.lastname}' => $buyer == null ? '' : $buyer->lastname,
            '{customer.company}' => $buyer == null ? '' : $buyer->company,
        ];

        $content = str_ireplace(
            array_keys($filters),
            array_values($filters),
            $methodDescription
        );

        return $content;
    }

    /**
     * Get webshop locale
     *
     * @return string
     *
     * @throws PrestaShopException
     *
     * @since 3.0.0
     */
    public static function getWebshopLocale()
    {
        // Current language
        if (Context::getContext()->language instanceof Language) {
            $language = Context::getContext()->language->iso_code;
        } else {
            $language = 'en';
        }
        $supportedLanguages = [
            'de',
            'en',
            'es',
            'fr',
            'nl',
            'ca',
            'pt',
            'it',
            'no',
            'sv',
            'fi',
            'da',
            'is',
            'hu',
            'pl',
            'lv',
            'lt',
        ];

        $supportedLocales = [
            'en_US',
            'de_AT',
            'de_CH',
            'de_DE',
            'es_ES',
            'fr_BE',
            'fr_FR',
            'nl_BE',
            'nl_NL',
            'ca_ES',
            'pt_PT',
            'it_IT',
            'nb_NO',
            'sv_SE',
            'fi_FI',
            'da_DK',
            'is_IS',
            'hu_HU',
            'pl_PL',
            'lv_LV',
            'lt_LT',
        ];

        $langIso = Tools::strtolower($language);
        if (!in_array($langIso, $supportedLanguages)) {
            $langIso = 'en';
        }
        $countryIso = Tools::strtoupper(Configuration::get('PS_LOCALE_COUNTRY'));
        if (!in_array("{$langIso}_{$countryIso}", $supportedLocales)) {
            switch ($langIso) {
                case 'de':
                    $countryIso = 'DE';
                    break;
                case 'es':
                    $countryIso = 'ES';
                    break;
                case 'fr':
                    $countryIso = 'FR';
                    break;
                case 'nl':
                    $countryIso = 'NL';
                    break;
                case 'ca':
                    $countryIso = 'ES';
                    break;
                case 'pt':
                    $countryIso = 'PT';
                    break;
                case 'it':
                    $countryIso = 'IT';
                    break;
                case 'nn':
                    $langIso = 'nb';
                    $countryIso = 'NO';
                    break;
                case 'no':
                    $langIso = 'nb';
                    $countryIso = 'NO';
                    break;
                case 'sv':
                    $countryIso = 'SE';
                    break;
                case 'fi':
                    $countryIso = 'FI';
                    break;
                case 'da':
                    $countryIso = 'DK';
                    break;
                case 'is':
                    $countryIso = 'IS';
                    break;
                case 'hu':
                    $countryIso = 'hu';
                    break;
                case 'pl':
                    $countryIso = 'PL';
                    break;
                case 'lv':
                    $countryIso = 'LV';
                    break;
                case 'lt':
                    $countryIso = 'LT';
                    break;
                default:
                    $countryIso = 'US';
            }
        }

        return "{$langIso}_{$countryIso}";
    }

    /**
     * Ajax process download module update
     *
     * @throws ErrorException
     * @since 3.0.0
     */
    public function ajaxProcessDownloadUpdate()
    {
        header('Content-Type: application/json;charset=UTF-8');
        try {
            $latestVersion = $this->getLatestVersion();
        } catch (PrestaShopException $e) {
            die(json_encode([
                'success' => false,
                'message' => $this->l('Unable to retieve info about the latest version'),
            ]));
        } catch (SmartyException $e) {
            die(json_encode([
                'success' => false,
                'message' => $this->l('Unable to retieve info about the latest version'),
            ]));
        }
        if (version_compare(
            Tools::substr($latestVersion['version'], 1, Tools::strlen($latestVersion['version']) - 1),
            $this->version,
            '>'
        )) {
            // Then update
            die(json_encode([
                'success' => $this->downloadModuleFromLocation($this->name, $latestVersion['download']),
            ]));
        } else {
            die(json_encode([
                'success' => false,
                'message' => $this->l('You are already running the latest version!'),
            ]));
        }
    }

    /**
     * Download the latest module from the given location
     *
     * @param string $moduleName
     * @param string $location
     *
     * @return bool
     * @throws ErrorException
     */
    protected function downloadModuleFromLocation($moduleName, $location)
    {
        $zipLocation = _PS_MODULE_DIR_ . $moduleName . '.zip';
        if (@!file_exists($zipLocation)) {
            $curl = new Curl\Curl();
            $curl->setOpt(CURLOPT_ENCODING, '');
            $curl->setOpt(CURLOPT_FOLLOWLOCATION, 1);
            if (!$curl->download($location, _PS_MODULE_DIR_ . 'mollie-update.zip')) {
                return false;
            }
        }

        return true;
    }

    /**
     * Extracts a module archive to the `modules` folder
     *
     * @param string $moduleName Module name
     * @param string $file File source location
     *
     * @return bool
     *
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @since 3.0.0
     */
    protected function extractModuleArchive($moduleName, $file)
    {
        $zipFolders = [];
        $tmpFolder = _PS_MODULE_DIR_ . $moduleName . md5(time());

        if (@!file_exists($file)) {
            $this->context->controller->errors[] = $this->l('Module archive could not be downloaded');

            return false;
        }

        $success = false;
        if (Tools::substr($file, -4) === '.zip') {
            if (Tools::ZipExtract($file, $tmpFolder) && file_exists($tmpFolder . DIRECTORY_SEPARATOR . $moduleName)) {
                if (file_exists(_PS_MODULE_DIR_ . $moduleName)) {
                    $report = '';
                    if (!\Mollie\Utility\UrlPathUtility::testDir(_PS_MODULE_DIR_ . $moduleName, true, $report, true)) {
                        $this->recursiveDeleteOnDisk($tmpFolder);
                        @unlink(_PS_MODULE_DIR_ . $moduleName . '.zip');

                        return false;
                    }
                    $this->recursiveDeleteOnDisk(_PS_MODULE_DIR_ . $moduleName);
                }
                if (@rename($tmpFolder . DIRECTORY_SEPARATOR . $moduleName, _PS_MODULE_DIR_ . $moduleName)) {
                    $success = true;
                }
            }
        }

        if (!$success) {
            $this->context->controller->errors[] =
                $this->l('There was an error while extracting the module file (file may be corrupted).');
            // Force a new check
        } else {
            //check if it's a real module
            foreach ($zipFolders as $folder) {
                if (!in_array($folder, ['.', '..', '.svn', '.git', '__MACOSX']) && !Module::getInstanceByName($folder)) {
                    $this->recursiveDeleteOnDisk(_PS_MODULE_DIR_ . $folder);
                }
            }
        }

        @unlink($file);
        @unlink(_PS_MODULE_DIR_ . $moduleName . 'backup');
        $this->recursiveDeleteOnDisk($tmpFolder);

        die(json_encode([
            'success' => $success,
        ]));
    }

    /**
     * Delete folder recursively
     *
     * @param string $dir Directory
     *
     * @since 3.0.0
     */
    protected function recursiveDeleteOnDisk($dir)
    {
        if (strpos(realpath($dir), realpath(_PS_MODULE_DIR_)) === false) {
            return;
        }

        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object !== '.' && $object !== '..') {
                    if (filetype($dir . '/' . $object) === 'dir') {
                        $this->recursiveDeleteOnDisk($dir . '/' . $object);
                    } else {
                        @unlink($dir . '/' . $object);
                    }
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }

    /**
     * Get payment methods to show on the checkout
     *
     * @return array
     *
     * @throws PrestaShopException
     *
     * @since 3.0.0
     * @since 3.4.0 public
     *
     * @public ✓ This method is part of the public API
     */
    public function getMethodsForCheckout()
    {
        if (!Configuration::get(Mollie\Config\Config::MOLLIE_API_KEY)) {
            return [];
        }

        $iso = Tools::strtolower($this->context->currency->iso_code);
        $methodIds = $this->getMethodIdsForCheckout();
        if (empty($methodIds)) {
            $methodIds = [];
        }
        $countryCode = Tools::strtolower($this->context->country->iso_code);
        $unavailableMethods = [];
        foreach (Mollie\Config\Config::$defaultMethodAvailability as $methodName => $countries) {
            if (!in_array($methodName, ['klarnapaylater', 'klarnasliceit'])
                || empty($countries)
            ) {
                continue;
            }
            if (!in_array($countryCode, $countries)) {
                $unavailableMethods[] = $methodName;
            }
        }

        foreach ($methodIds as $index => $methodId) {
            $methodObj = new MolPaymentMethod($methodId['id_payment_method']);
            if (!isset(Mollie\Config\Config::$methodCurrencies[$methodObj->id_method])
                || !in_array($iso, Mollie\Config\Config::$methodCurrencies[$methodObj->id_method])
                || !$methodObj->enabled
                || in_array($methodObj->id_method, $unavailableMethods)
            ) {
                unset($methodIds[$index]);
            }
            if ($methodObj->id_method === Mollie\Config\Config::APPLEPAY) {
                if (!Configuration::get('PS_SSL_ENABLED_EVERYWHERE')) {
                    unset($methodIds[$index]);
                } elseif ($_COOKIE['isApplePayMethod'] === '0') {
                    unset($methodIds[$index]);
                }
            }
        }

        if (version_compare(_PS_VERSION_, '1.6.0.9', '>')) {
            foreach ($methodIds as $index => $methodId) {
                $methodObj = new MolPaymentMethod($methodId['id_payment_method']);
                if (!$methodObj->is_countries_applicable) {
                    if (!$this->checkIfMethodIsAvailableInCountry($methodObj->id_method, $countryCode)) {
                        unset($methodIds[$index]);
                    }
                }
            }
        }

        return $methodIds;
    }

    public function getMethodIdsForCheckout()
    {
        $sql = new DbQuery();
        $sql->select('`id_payment_method`');
        $sql->from('mol_payment_method');

        return Db::getInstance()->executeS($sql);
    }

    public function checkIfMethodIsAvailableInCountry($methodId, $countryISO)
    {
        $country = Country::getByIso($countryISO);
        $sql = new DbQuery();
        $sql->select('`id_mol_country`');
        $sql->from('mol_country');
        $sql->where('`id_method` = "' . pSQL($methodId) . '" AND ( id_country = ' . (int)$country . ' OR all_countries = 1)');

        return Db::getInstance()->getValue($sql);
    }

    /**
     * Process a submitted account
     *
     * @since 3.2.0
     */
    protected function processNewAccount()
    {
        try {
            if ($this->createMollieAccount(
                Tools::getValue('mollie_new_email'),
                Tools::getValue('mollie_new_name'),
                Tools::getValue('mollie_new_company'),
                Tools::getValue('mollie_new_address'),
                Tools::getValue('mollie_new_zipcode'),
                Tools::getValue('mollie_new_city'),
                Tools::getValue('mollie_new_country')
            )) {
                $this->context->controller->confirmations[] = $this->l('Successfully created your new Mollie account. Please check your inbox for more information.');
            } else {
                $this->context->controller->errors[] = $this->l('An unknown error occurred while trying to create your Mollie account');
            }
        } catch (Mollie_Exception $e) {
            $this->context->controller->errors[] = $e->getMessage();
        }
    }

    /**
     * Create a Mollie account using the legacy Reseller API
     *
     * @param string $name
     * @param string $company
     * @param string $address
     * @param string $zipcode
     * @param string $city
     * @param string $country
     * @param string $email
     *
     * @return bool
     *
     * @throws Mollie_Exception
     *
     * @since 3.2.0
     */
    protected function createMollieAccount($email, $name, $company, $address, $zipcode, $city, $country)
    {
        $mollie = new Mollie_Reseller(
            Mollie\Config\Config::MOLLIE_RESELLER_PARTNER_ID,
            Mollie\Config\Config::MOLLIE_RESELLER_PROFILE_KEY,
            Mollie\Config\Config::MOLLIE_RESELLER_APP_SECRET
        );
        $simplexml = $mollie->accountCreate(
            $email,
            [
                'name' => $name,
                'company_name' => $company,
                'address' => $address,
                'zipcode' => $zipcode,
                'city' => $city,
                'country' => $country,
                'email' => $email,
            ]
        );

        if (empty($simplexml->success) && isset($simplexml->resultmessage) && isset($simplexml->resultcode)) {
            throw new Mollie_Exception($simplexml->resultmessage, $simplexml->resultcode);
        }

        return !empty($simplexml->success);
    }

    /**
     * Check if the method PaymentModule::validateOrder is overridden
     * This can cause interference with this module
     *
     * @return false|string Returns the module name if overridden, otherwise false
     *
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws ReflectionException
     *
     * @since 3.3.0
     */
    protected function checkPaymentModuleOverride()
    {
        foreach ($this->findOverrides() as $override) {
            if ($override['override'] === 'PaymentModule::validateOrder') {
                return $override['module_name'];
            }
        }

        return false;
    }

    /**
     * Check if template compilation has been set to "never recompile".
     * This is known to cause issues.
     *
     * @return bool
     *
     * @since 3.3.2
     */
    protected function checkTemplateCompilation()
    {
        return !Configuration::get('PS_SMARTY_FORCE_COMPILE');
    }

    /**
     * Check if the Smarty cache has been enabled and revalidates.
     * If it does not, there's a chance it will serve a stale payment method list.
     *
     * @return bool
     *
     * @since 3.3.2
     */
    protected function checkStaleSmartyCache()
    {
        return Configuration::get('PS_SMARTY_CACHE') && Configuration::get('PS_SMARTY_CLEAR_CACHE') === 'never';
    }

    /**
     * Check if the rounding mode is supported by the Orders API
     *
     * @return bool
     *
     * @since 3.3.2
     */
    protected function checkRoundingMode()
    {
        return (int)Configuration::get('PS_PRICE_ROUND_MODE') !== 2;
    }

    /**
     * Find overrides
     *
     * @return array Overrides
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws ReflectionException
     *
     * @since 3.3.0
     */
    protected function findOverrides()
    {
        $overrides = [];

        $overriddenClasses = array_keys($this->findOverriddenClasses());

        foreach ($overriddenClasses as $overriddenClass) {
            $reflectionClass = new ReflectionClass($overriddenClass);
            $reflectionMethods = array_filter($reflectionClass->getMethods(), function ($reflectionMethod) use ($overriddenClass) {
                return $reflectionMethod->class == $overriddenClass;
            });

            if (!file_exists($reflectionClass->getFileName())) {
                continue;
            }
            $overrideFile = file($reflectionClass->getFileName());
            if (is_array($overrideFile)) {
                $overrideFile = array_diff($overrideFile, ["\n"]);
            } else {
                $overrideFile = [];
            }
            foreach ($reflectionMethods as $reflectionMethod) {
                /** @var ReflectionMethod $reflectionMethod */
                $idOverride = Tools::substr(sha1($reflectionMethod->class . '::' . $reflectionMethod->name), 0, 10);
                $overriddenMethod = [
                    'id_override' => $idOverride,
                    'override' => $reflectionMethod->class . '::' . $reflectionMethod->name,
                    'module_code' => $this->l('Unknown'),
                    'module_name' => $this->l('Unknown'),
                    'date' => $this->l('Unknown'),
                    'version' => $this->l('Unknown'),
                    'deleted' => (Tools::isSubmit('deletemodule') && Tools::getValue('id_override') === $idOverride)
                        || (Tools::isSubmit('overrideBox') && in_array($idOverride, Tools::getValue('overrideBox'))),
                ];
                if (isset($overrideFile[$reflectionMethod->getStartLine() - 5])
                    && preg_match('/module: (.*)/ism', $overrideFile[$reflectionMethod->getStartLine() - 5], $module)
                    && preg_match('/date: (.*)/ism', $overrideFile[$reflectionMethod->getStartLine() - 4], $date)
                    && preg_match('/version: ([0-9.]+)/ism', $overrideFile[$reflectionMethod->getStartLine() - 3], $version)) {
                    $overriddenMethod['module_code'] = trim($module[1]);
                    $module = Module::getInstanceByName(trim($module[1]));
                    if (Validate::isLoadedObject($module)) {
                        $overriddenMethod['module_name'] = $module->displayName;
                    }
                    $overriddenMethod['date'] = trim($date[1]);
                    $overriddenMethod['version'] = trim($version[1]);
                }
                $overrides[$idOverride] = $overriddenMethod;
            }
        }

        return $overrides;
    }

    /**
     * Find all override classes
     *
     * @return array Overridden classes
     *
     * @since 3.3.0
     */
    protected function findOverriddenClasses()
    {
        return $this->getClassesFromDir('override/classes/') + $this->getClassesFromDir('override/controllers/');
    }

    /**
     * Retrieve recursively all classes in a directory and its subdirectories
     *
     * @param string $path Relative path from root to the directory
     *
     * @return array
     *
     * @since 3.3.0
     */
    protected function getClassesFromDir($path)
    {
        $classes = [];
        $rootDir = $this->normalizeDirectory(_PS_ROOT_DIR_);

        foreach (scandir($rootDir . $path) as $file) {
            if ($file[0] != '.') {
                if (is_dir($rootDir . $path . $file)) {
                    $classes = array_merge($classes, $this->getClassesFromDir($path . $file . '/'));
                } elseif (Tools::substr($file, -4) == '.php') {
                    $content = Tools::file_get_contents($rootDir . $path . $file);

                    $namespacePattern = '[\\a-z0-9_]*[\\]';
                    $pattern = '#\W((abstract\s+)?class|interface)\s+(?P' . $this->display(__FILE__, 'views/templates/front/classname.tpl') . basename($file, '.php') . '(?:Core)?)' . '(?:\s+extends\s+' . $namespacePattern . '[a-z][a-z0-9_]*)?(?:\s+implements\s+' . $namespacePattern . '[a-z][\\a-z0-9_]*(?:\s*,\s*' . $namespacePattern . '[a-z][\\a-z0-9_]*)*)?\s*\{#i';

                    if (preg_match($pattern, $content, $m)) {
                        $classes[$m['classname']] = [
                            'path' => $path . $file,
                            'type' => trim($m[1]),
                            'override' => true,
                        ];

                        if (Tools::substr($m['classname'], -4) == 'Core') {
                            $classes[Tools::substr($m['classname'], 0, -4)] = [
                                'path' => '',
                                'type' => $classes[$m['classname']]['type'],
                                'override' => true,
                            ];
                        }
                    }
                }
            }
        }

        return $classes;
    }

    /**
     * Normalize directory
     *
     * @param string $directory
     *
     * @return string
     *
     * @since 3.3.0
     */
    protected function normalizeDirectory($directory)
    {
        return rtrim($directory, '/\\') . DIRECTORY_SEPARATOR;
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
        } catch (\Mollie\Api\Exceptions\ApiException $e) {
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
        return $orderInfoService->displayMollieOrderInfo($input,  $adminOrdersController->id);
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
        $checkStatuses = [];
        if (Configuration::get(Mollie\Config\Config::MOLLIE_AUTO_SHIP_STATUSES)) {
            $checkStatuses = @json_decode(Configuration::get(Mollie\Config\Config::MOLLIE_AUTO_SHIP_STATUSES));
        }
        if (!is_array($checkStatuses)) {
            $checkStatuses = [];
        }

        /** @var \Mollie\Service\ShipmentService $shipmentService */
        $shipmentService = $this->getContainer(\Mollie\Service\ShipmentService::class);
        $shipmentInfo = $shipmentService->getShipmentInformation($idOrder);

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

        $length = Tools::strlen(Mollie\Api\Endpoints\OrderEndpoint::RESOURCE_ID_PREFIX);
        if (Tools::substr($dbPayment['transaction_id'], 0, $length) !== Mollie\Api\Endpoints\OrderEndpoint::RESOURCE_ID_PREFIX
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
        } catch (\Mollie\Api\Exceptions\ApiException $e) {
            PrestaShopLogger::addLog("Mollie module error: {$e->getMessage()}");
            return;
        } catch (Exception $e) {
            PrestaShopLogger::addLog("Mollie module error: {$e->getMessage()}");
            return;
        }
    }

    public function hookActionEmailSendBefore($params)
    {
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
            if (!isset($params['cart']->id)) {
                return;
            }
            $order = Order::getByCartId($params['cart']->id);
            $orderFee = new MolOrderFee($order->id);
            if ($orderFee->order_fee) {
                $params['templateVars']['{payment_fee}'] = Tools::displayPrice($orderFee->order_fee);
            } else {
                $params['templateVars']['{payment_fee}'] = Tools::displayPrice(0);
            }
        }
    }
}
