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

        /** @var \Mollie\Service\OverrideService $overrideService */
        $overrideService = $this->getContainer(\Mollie\Service\OverrideService::class);
        if ($module = $overrideService->checkPaymentModuleOverride()) {
            $this->context->controller->warnings[] = sprintf(
                $this->l('The method %s is overridden by module %s. This can cause interference with payments.'),
                'PaymentModule::validateOrder',
                $module
            );
        }
        if (!Configuration::get('PS_SMARTY_FORCE_COMPILE')) {
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
        if (Configuration::get('PS_SMARTY_CACHE') && Configuration::get('PS_SMARTY_CLEAR_CACHE') === 'never') {
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
        /** @var \Mollie\Service\PaymentMethodService $paymentMethodService */
        $paymentMethodService = $this->getContainer(\Mollie\Service\PaymentMethodService::class);
        $apiMethods = $paymentMethodService->getMethodsForCheckout();
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
        /** @var \Mollie\Service\PaymentMethodService $paymentMethodService */
        $paymentMethodService = $this->getContainer(\Mollie\Service\PaymentMethodService::class);
        $methods = $paymentMethodService->getMethodsForCheckout();
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
        /** @var \Mollie\Service\PaymentMethodService $paymentMethodService */
        /** @var \Mollie\Service\IssuerService $issuerService */
        $paymentMethodService = $this->getContainer(\Mollie\Service\PaymentMethodService::class);
        $issuerService = $this->getContainer(\Mollie\Service\IssuerService::class);

        $methodIds = $paymentMethodService->getMethodsForCheckout();
        $issuerList = [];
        foreach ($methodIds as $methodId) {
            $methodObj = new MolPaymentMethod($methodId['id_payment_method']);
            if ($methodObj->id_method === \Mollie\Api\Types\PaymentMethod::IDEAL) {
                $issuerList = $issuerService->getIdealIssuers();
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
            if (!isset(Mollie\Config\Config::$methodCurrencies[$methodObj->id_method])) {
                continue;
            }
            if (!in_array($iso, Mollie\Config\Config::$methodCurrencies[$methodObj->id_method])) {
                continue;
            }

            $methodObj = new MolPaymentMethod($methodId['id_payment_method']);
            $paymentFee = \Mollie\Utility\PaymentFeeUtility::getPaymentFee($methodObj, $cart->getOrderTotal());

            $isIdealMethod = $methodObj->id_method === \Mollie\Api\Types\PaymentMethod::IDEAL;
            $isIssuersOnClick = Configuration::get(Mollie\Config\Config::MOLLIE_ISSUERS) === Mollie\Config\Config::ISSUERS_ON_CLICK;
            $isCreditCardMethod = $methodObj->id_method === Mollie\Api\Types\PaymentMethod::CREDITCARD;

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

                $imageConfig = Configuration::get(Mollie\Config\Config::MOLLIE_IMAGES);
                $image = json_decode($methodObj->images_json, true);
                $image = \Mollie\Utility\ImageUtility::setOptionImage($image, $imageConfig);
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
                ($isCreditCardMethod || $methodObj->id_method === 'cartesbancaires') &&
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
                $image = json_decode($methodObj->images_json, true);
                $image = \Mollie\Utility\ImageUtility::setOptionImage($image, $imageConfig);
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

                $imageConfig = Configuration::get(Mollie\Config\Config::MOLLIE_IMAGES);
                $image = json_decode($methodObj->images_json, true);
                $image = \Mollie\Utility\ImageUtility::setOptionImage($image, $imageConfig);
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
        if ($payment && $payment['bank_status'] == \Mollie\Api\Types\PaymentStatus::STATUS_PAID) {
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