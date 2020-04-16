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
        $this->version = '3.6.0';
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
                'settingsPage' => static::getMenuLocation('AdminPerformance'),
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
                'settingsPage' => static::getMenuLocation('AdminPerformance'),
            ]);
            $this->context->controller->errors[] = $this->display(__FILE__, 'smarty_error.tpl');
        }
        if ($this->checkRoundingMode()) {
            $this->context->smarty->assign([
                'settingKey' => version_compare(_PS_VERSION_, '1.7.3.0', '>=')
                    ? $this->trans('Rounding mode', [], 'Admin.Shopparameters.Feature')
                    : Translate::getAdminTranslation('Rounding mode', 'AdminPreferences'),
                'settingValue' => version_compare(_PS_VERSION_, '1.7.3.0', '>=')
                    ? $this->trans('Round up away from zero, when it is half way there (recommended)', [], 'Admin.Shopparameters.Feature')
                    : Translate::getAdminTranslation('Round up away from zero, when it is half way there (recommended)', 'AdminPreferences'),
                'settingsPage' => static::getMenuLocation('AdminPreferences'),
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
            $resultMessage = $this->getSaveResult($errors);
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
            'webpack_urls' => static::getWebpackChunks('app'),
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

        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFieldsValues(),
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
                'countries' => $this->getActiveCountriesList(),
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

        $this->aasort($orderStatuses, 'id_order_state');

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
     * Get carrier configuration
     *
     * @return array
     *
     * @since 3.3.0
     */
    public static function carrierConfig()
    {
        $dbConfig = @json_decode(Configuration::get(Mollie\Config\Config::MOLLIE_TRACKING_URLS), true);
        if (!is_array($dbConfig)) {
            $dbConfig = [];
        }

        $carriers = Carrier::getCarriers(
            Context::getContext()->language->id,
            false,
            false,
            false,
            null,
            Carrier::ALL_CARRIERS
        );

        $configCarriers = [];
        foreach ($carriers as $carrier) {
            $idCarrier = (int)$carrier['id_carrier'];
            $configCarriers[] = [
                'id_carrier' => $idCarrier,
                'name' => $carrier['name'],
                'source' => isset($dbConfig[$idCarrier]) ? $dbConfig[$idCarrier]['source'] : ($carrier['external_module_name'] ? Mollie\Config\Config::MOLLIE_CARRIER_MODULE : Mollie\Config\Config::MOLLIE_CARRIER_CARRIER),
                'module' => !empty($carrier['external_module_name']) ? $carrier['external_module_name'] : null,
                'module_name' => !empty($carrier['external_module_name']) ? $carrier['external_module_name'] : null,
                'custom_url' => isset($dbConfig[$idCarrier]) ? $dbConfig[$idCarrier]['custom_url'] : '',
            ];
        }
        if (count($dbConfig) !== count($configCarriers)) {
            Configuration::updateValue(Mollie\Config\Config::MOLLIE_TRACKING_URLS, json_encode($configCarriers));
        }

        return $configCarriers;
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
     * @param int $order
     * @param string|int $statusId
     * @param bool|null $useExistngPayment
     * @param array $templateVars
     *
     * @return void
     *
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @since 3.3.2 Accept both Order ID and Order object
     * @since 3.3.2 Accept both Mollie status string and PrestaShop status ID
     * @since 3.3.2 $useExistingPayment option
     * @since 3.3.4 Accepts template vars for the corresponding email template
     */
    public function setOrderStatus($order, $statusId, $useExistingPayment = null, $templateVars = [])
    {
        if (is_string($statusId)) {
            $status = $statusId;
            if (empty(Mollie\Config\Config::getStatuses()[$statusId])) {
                return;
            }
            $statusId = (int)Mollie\Config\Config::getStatuses()[$statusId];
        } else {
            $status = '';
            foreach (Mollie\Config\Config::getStatuses() as $mollieStatus => $prestaShopStatusId) {
                if ((int)$prestaShopStatusId === $statusId) {
                    $status = $mollieStatus;
                    break;
                }
            }
        }

        if (!$order instanceof Order) {
            $order = new Order((int)$order);
        }

        if (!Validate::isLoadedObject($order)) {
            return;
        }

        $history = array_map(function ($item) {
            return (int)$item['id_order_state'];
        }, $order->getHistory(Context::getContext()->language->id));
        if (!Validate::isLoadedObject($order)
            || !$status
        ) {
            return;
        }
        if ($useExistingPayment === null) {
            $useExistingPayment = !$order->hasInvoice();
        }

        $history = new OrderHistory();
        $history->id_order = $order->id;
        $history->changeIdOrderState($statusId, $order, $useExistingPayment);

        if (Configuration::get('MOLLIE_MAIL_WHEN_' . Tools::strtoupper($status))) {
            $history->addWithemail(true, $templateVars);
        } else {
            $history->add();
        }
    }

    /**
     * @param string $column
     * @param int $id
     *
     * @return array
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @since 3.3.0 static function
     */
    public static function getPaymentBy($column, $id)
    {
        try {
            $paidPayment = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow(
                sprintf(
                    'SELECT * FROM `%s` WHERE `%s` = \'%s\' AND `bank_status` IN(\'%s\', \'%s\')',
                    _DB_PREFIX_ . 'mollie_payments',
                    bqSQL($column),
                    pSQL($id),
                    \Mollie\Api\Types\PaymentStatus::STATUS_PAID,
                    \Mollie\Api\Types\PaymentStatus::STATUS_AUTHORIZED
                )
            );
        } catch (PrestaShopDatabaseException $e) {
            static::tryAddOrderReferenceColumn();
            throw $e;
        }

        if ($paidPayment) {
            return $paidPayment;
        }

        try {
            $nonPaidPayment = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow(
                sprintf(
                    'SELECT * FROM `%s` WHERE `%s` = \'%s\' ORDER BY `created_at` DESC',
                    _DB_PREFIX_ . 'mollie_payments',
                    bqSQL($column),
                    pSQL($id)
                )
            );
        } catch (PrestaShopDatabaseException $e) {
            static::tryAddOrderReferenceColumn();
            throw $e;
        }

        return $nonPaidPayment;
    }

    /**
     * @param array $errors
     *
     * @return string
     * @throws PrestaShopException
     */
    protected function getSaveResult(&$errors = [])
    {
        $mollieApiKey = Tools::getValue(Mollie\Config\Config::MOLLIE_API_KEY);
        $mollieProfileId = Tools::getValue(Mollie\Config\Config::MOLLIE_PROFILE_ID);

        if (strpos($mollieApiKey, 'live') !== 0 && strpos($mollieApiKey, 'test') !== 0) {
            $errors[] = $this->l('The API key needs to start with test or live.');
        }

        if (Tools::getValue(Mollie\Config\Config::METHODS_CONFIG) && json_decode(Tools::getValue(Mollie\Config\Config::METHODS_CONFIG))) {
            Configuration::updateValue(
                Mollie\Config\Config::METHODS_CONFIG,
                json_encode(@json_decode(Tools::getValue(Mollie\Config\Config::METHODS_CONFIG)))
            );
        }
        /** @var \Mollie\Service\PaymentMethodService $paymentMethodService */
        $paymentMethodService = $this->getContainer(\Mollie\Service\PaymentMethodService::class);
        /** @var \Mollie\Service\ApiService $apiService */
        $apiService = $this->getContainer(\Mollie\Service\ApiService::class);
        if ($this->api->methods !== null && Configuration::get(Mollie\Config\Config::MOLLIE_API_KEY)) {
            foreach ($apiService->getMethodsForConfig($this->api, $this->getPathUri()) as $method) {
                try {
                    $paymentMethod = $paymentMethodService->savePaymentMethod($method);
                } catch (Exception $e) {
                    $errors[] = $this->l('Something went wrong. Couldn\'t save your payment methods');
                }

                /** @var \Mollie\Repository\PaymentMethodRepository $paymentMethodRepo */
                $paymentMethodRepo = $this->getContainer(\Mollie\Repository\PaymentMethodRepository::class);

                if (!$paymentMethodRepo->deletePaymentMethodIssuersByPaymentMethodId($paymentMethod->id)) {
                    $errors[] = $this->l('Something went wrong. Couldn\'t delete old payment methods issuers');
                }

                if ($method['issuers']) {
                    $paymentMethodIssuer = new MolPaymentMethodIssuer();
                    $paymentMethodIssuer->issuers_json = json_encode($method['issuers']);
                    $paymentMethodIssuer->id_payment_method = $paymentMethod->id;
                    try {
                        $paymentMethodIssuer->add();
                    } catch (Exception $e) {
                        $errors[] = $this->l('Something went wrong. Couldn\'t save your payment methods issuer');
                    }
                }

                $countries = Tools::getValue(Mollie\Config\Config::MOLLIE_METHOD_CERTAIN_COUNTRIES . $method['id']);
                $this->updateMethodCountries($method['id'], $countries);
            }
        }

        $molliePaymentscreenLocale = Tools::getValue(Mollie\Config\Config::MOLLIE_PAYMENTSCREEN_LOCALE);
        $mollieIFrameEnabled = Tools::getValue(Mollie\Config\Config::MOLLIE_IFRAME);
        $mollieImages = Tools::getValue(Mollie\Config\Config::MOLLIE_IMAGES);
        $mollieIssuers = Tools::getValue(Mollie\Config\Config::MOLLIE_ISSUERS);
        $mollieCss = Tools::getValue(Mollie\Config\Config::MOLLIE_CSS);
        if (!isset($mollieCss)) {
            $mollieCss = '';
        }
        $mollieLogger = Tools::getValue(Mollie\Config\Config::MOLLIE_DEBUG_LOG);
        $mollieApi = Tools::getValue(Mollie\Config\Config::MOLLIE_API);
        $mollieQrEnabled = (bool)Tools::getValue(Mollie\Config\Config::MOLLIE_QRENABLED);
        $mollieMethodCountriesEnabled = (bool)Tools::getValue(Mollie\Config\Config::MOLLIE_METHOD_COUNTRIES);
        $mollieMethodCountriesDisplayEnabled = (bool)Tools::getValue(Mollie\Config\Config::MOLLIE_METHOD_COUNTRIES_DISPLAY);
        $mollieErrors = Tools::getValue(Mollie\Config\Config::MOLLIE_DISPLAY_ERRORS);

        $mollieShipMain = Tools::getValue(Mollie\Config\Config::MOLLIE_AUTO_SHIP_MAIN);
        if (!isset($mollieErrors)) {
            $mollieErrors = false;
        } else {
            $mollieErrors = ($mollieErrors == 1);
        }

        if (empty($errors)) {
            Configuration::updateValue(Mollie\Config\Config::MOLLIE_API_KEY, $mollieApiKey);
            Configuration::updateValue(Mollie\Config\Config::MOLLIE_PROFILE_ID, $mollieProfileId);
            Configuration::updateValue(Mollie\Config\Config::MOLLIE_PAYMENTSCREEN_LOCALE, $molliePaymentscreenLocale);
            Configuration::updateValue(Mollie\Config\Config::MOLLIE_IFRAME, $mollieIFrameEnabled);
            Configuration::updateValue(Mollie\Config\Config::MOLLIE_IMAGES, $mollieImages);
            Configuration::updateValue(Mollie\Config\Config::MOLLIE_ISSUERS, $mollieIssuers);
            Configuration::updateValue(Mollie\Config\Config::MOLLIE_QRENABLED, (bool)$mollieQrEnabled);
            Configuration::updateValue(Mollie\Config\Config::MOLLIE_METHOD_COUNTRIES, (bool)$mollieMethodCountriesEnabled);
            Configuration::updateValue(Mollie\Config\Config::MOLLIE_METHOD_COUNTRIES_DISPLAY, (bool)$mollieMethodCountriesDisplayEnabled);
            Configuration::updateValue(Mollie\Config\Config::MOLLIE_CSS, $mollieCss);
            Configuration::updateValue(Mollie\Config\Config::MOLLIE_DISPLAY_ERRORS, (int)$mollieErrors);
            Configuration::updateValue(Mollie\Config\Config::MOLLIE_DEBUG_LOG, (int)$mollieLogger);
            Configuration::updateValue(Mollie\Config\Config::MOLLIE_API, $mollieApi);
            Configuration::updateValue(
                Mollie\Config\Config::MOLLIE_AUTO_SHIP_STATUSES,
                json_encode($this->getStatusesValue(Mollie\Config\Config::MOLLIE_AUTO_SHIP_STATUSES))
            );
            Configuration::updateValue(Mollie\Config\Config::MOLLIE_AUTO_SHIP_MAIN, (bool)$mollieShipMain);
            Configuration::updateValue(
                Mollie\Config\Config::MOLLIE_TRACKING_URLS,
                json_encode(@json_decode(Tools::getValue(Mollie\Config\Config::MOLLIE_TRACKING_URLS)))
            );
            foreach (array_keys(Mollie\Config\Config::getStatuses()) as $name) {
                $name = Tools::strtoupper($name);
                $new = (int)Tools::getValue("MOLLIE_STATUS_{$name}");
                Configuration::updateValue("MOLLIE_STATUS_{$name}", $new);
                Mollie\Config\Config::getStatuses()[Tools::strtolower($name)] = $new;

                if ($name != \Mollie\Api\Types\PaymentStatus::STATUS_OPEN) {
                    Configuration::updateValue(
                        "MOLLIE_MAIL_WHEN_{$name}",
                        Tools::getValue("MOLLIE_MAIL_WHEN_{$name}") ? true : false
                    );
                }
            }

            if ($mollieApiKey) {
                try {
                    $this->api->setApiKey($mollieApiKey);
                } catch (Exception $e) {
                    $errors[] = $e->getMessage();
                    Configuration::updateValue(Mollie\Config\Config::MOLLIE_API_KEY, null);
                    return $this->l('Wrong API Key!');
                }
            }

            $resultMessage = $this->l('The configuration has been saved!');
        } else {
            $resultMessage = [];
            foreach ($errors as $error) {
                $resultMessage[] = $error;
            }
        }

        return $resultMessage;
    }

    private function updateMethodCountries($idMethod, $idCountries)
    {

        $sql = 'DELETE FROM ' . _DB_PREFIX_ . 'mol_country WHERE `id_method` = "' . $idMethod . '"';
        if (!Db::getInstance()->execute($sql)) {
            return false;
        }

        if ($idCountries == false) {
            return true;
        }

        foreach ($idCountries as $idCountry) {
            $allCountries = 0;
            $sql = 'INSERT INTO `' . _DB_PREFIX_ . 'mol_country` (id_method, id_country, all_countries) VALUES (';

            if ($idCountry === '0') {
                $allCountries = 1;
            }
            $sql .= '"' . pSQL($idMethod) . '", ' . (int)$idCountry . ', ' . (int)$allCountries . ')';

            if (!Db::getInstance()->execute($sql)) {
                $response = false;
            }
        }

        return true;
    }

    /**
     * @param string|null $url
     *
     * @return bool|null|string|string[]
     * @throws PrestaShopException
     * @throws SmartyException
     * @throws ErrorException
     */
    protected function getLatestVersion($url = null)
    {
        if (!$url) {
            $url = (defined('_TB_VERSION_')
                ? 'https://api.github.com/repos/mollie/thirtybees/releases/latest'
                : 'https://api.github.com/repos/mollie/PrestaShop/releases/latest');
        }
        $curl = new Curl\Curl();
        $response = $curl->get($url);
        if (!is_object($response)) {
            throw new PrestaShopException($this->l('Warning: Could not retrieve update file from github.'));
        }
        if (empty($response->assets[0]->browser_download_url)) {
            throw new PrestaShopException($this->l('No download package found for the latest release.'));
        }

        return [
            'version' => $response->tag_name,
            'download' => $response->assets[0]->browser_download_url,
        ];
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
     * @param int $orderId
     * @param string $transactionId
     *
     * @return array
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     * @throws \Mollie\Api\Exceptions\ApiException
     * @throws \PrestaShop\PrestaShop\Adapter\CoreException
     * @since      3.0.0
     *
     * @deprecated 3.3.0
     */
    protected function doRefund($orderId, $transactionId)
    {
        return $this->doPaymentRefund($transactionId);
    }

    /**
     * @param string $transactionId Transaction/Mollie Order ID
     * @param float|null $amount Amount to refund, refund all if `null`
     *
     * @return array
     *
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     * @throws \Mollie\Api\Exceptions\ApiException
     * @throws CoreException
     *
     * @since 3.3.0 Renamed `doRefund` to `doPaymentRefund`, added `$amount`
     * @since 3.3.2 Omit $orderId
     */
    protected function doPaymentRefund($transactionId, $amount = null)
    {
        try {
            /** @var Payment $payment */
            $payment = $this->api->payments->get($transactionId);
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
        } catch (\Mollie\Api\Exceptions\ApiException $e) {
            return [
                'status' => 'fail',
                'msg_fail' => $this->lang('The order could not be refunded!'),
                'msg_details' => $this->lang('Reason:') . ' ' . $e->getMessage(),
            ];
        }

        if (Mollie::isLocalEnvironment()) {
            // Refresh payment on local environments
            /** @var Payment $payment */
            $apiPayment = $this->api->payments->get($transactionId);
            if (!Tools::isSubmit('module')) {
                $_GET['module'] = $this->name;
            }
            $webhookController = new MollieWebhookModuleFrontController();
            $webhookController->processTransaction($apiPayment);
        }

        return [
            'status' => 'success',
            'msg_success' => $this->lang('The order has been refunded!'),
            'msg_details' => $this->lang('Mollie B.V. will transfer the money back to the customer on the next business day.'),
        ];
    }

    /**
     * @param string $transactionId
     * @param array $lines
     * @param array|null $tracking
     *
     * @return array
     *
     * @since 3.3.0
     */
    protected function doShipOrderLines($transactionId, $lines = [], $tracking = null)
    {
        try {
            /** @var \Mollie\Api\Resources\Order $payment */
            $order = $this->api->orders->get($transactionId, ['embed' => 'payments']);
            $shipment = [
                'lines' => array_map(function ($line) {
                    return array_intersect_key(
                        (array)$line,
                        array_flip([
                            'id',
                            'quantity',
                        ]));
                }, $lines),
            ];
            if ($tracking && !empty($tracking['carrier']) && !empty($tracking['code'])) {
                $shipment['tracking'] = $tracking;
            }
            $order->createShipment($shipment);
        } catch (\Mollie\Api\Exceptions\ApiException $e) {
            return [
                'success' => false,
                'message' => $this->lang('The product(s) could not be shipped!'),
                'detailed' => $e->getMessage(),
            ];
        }

        return [
            'success' => true,
            'message' => '',
            'detailed' => '',
        ];
    }

    /**
     * @param string $transactionId
     * @param array $lines
     *
     * @return array
     *
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     * @throws CoreException
     *
     * @since 3.3.0
     */
    protected function doRefundOrderLines($transactionId, $lines = [])
    {
        try {
            /** @var \Mollie\Api\Resources\Order $payment */
            $order = $this->api->orders->get($transactionId, ['embed' => 'payments']);
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

            if (Mollie::isLocalEnvironment()) {
                // Refresh payment on local environments
                /** @var Payment $payment */
                $apiPayment = $this->api->orders->get($transactionId, ['embed' => 'payments']);
                if (!Tools::isSubmit('module')) {
                    $_GET['module'] = $this->name;
                }
                $webhookController = new MollieWebhookModuleFrontController();
                $webhookController->processTransaction($apiPayment);
            }
        } catch (\Mollie\Api\Exceptions\ApiException $e) {
            return [
                'success' => false,
                'message' => $this->lang('The product(s) could not be refunded!'),
                'detailed' => $e->getMessage(),
            ];
        }

        return [
            'success' => true,
            'message' => '',
            'detailed' => '',
        ];
    }

    /**
     * @param string $transactionId
     * @param array $lines
     *
     * @return array
     *
     * @throws Adapter_Exception
     * @throws ErrorException
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     * @throws CoreException
     *
     * @since 3.3.0
     */
    protected function doCancelOrderLines($transactionId, $lines = [])
    {
        try {
            /** @var \Mollie\Api\Resources\Order $payment */
            $order = $this->api->orders->get($transactionId, ['embed' => 'payments']);
            if ($lines === []) {
                $order->cancel();
            } else {
                $cancelableLines = [];
                foreach ($lines as $line) {
                    $cancelableLines[] = ['id' => $line['id'], 'quantity' => $line['quantity']];
                }
                $order->cancelLines(['lines' => $cancelableLines]);
            }

            if (Mollie::isLocalEnvironment()) {
                // Refresh payment on local environments
                /** @var Payment $payment */
                $apiPayment = $this->api->orders->get($transactionId, ['embed' => 'payments']);
                if (!Tools::isSubmit('module')) {
                    $_GET['module'] = $this->name;
                }
                $webhookController = new MollieWebhookModuleFrontController();
                $webhookController->processTransaction($apiPayment);
            }
        } catch (\Mollie\Api\Exceptions\ApiException $e) {
            return [
                'success' => false,
                'message' => $this->lang('The product(s) could not be canceled!'),
                'detailed' => $e->getMessage(),
            ];
        }

        return [
            'success' => true,
            'message' => '',
            'detailed' => '',
        ];
    }

    /**
     * @return array
     *
     * @throws \Mollie\Api\Exceptions\ApiException
     * @throws PrestaShopException
     */
    public function getIssuerList()
    {
        $methods = [];
        foreach ($this->api->methods->all(['include' => 'issuers']) as $method) {
            /** @var \Mollie\Api\Resources\Method $method */
            foreach ((array)$method->issuers as $issuer) {
                if (!$issuer) {
                    continue;
                }

                $issuer->href = $this->context->link->getModuleLink(
                    $this->name,
                    'payment',
                    ['method' => $method->id, 'issuer' => $issuer->id, 'rand' => time()],
                    true
                );

                if (!isset($methods[$method->id])) {
                    $methods[$method->id] = [];
                }
                $methods[$method->id][$issuer->id] = $issuer;
            }
        }

        return $methods;
    }

    /**
     * @param string|null $file
     *
     * @throws PrestaShopException
     */
    protected function addCSSFile($file = null)
    {
        if (!is_null($file)) {
            $file = Configuration::get(Mollie\Config\Config::MOLLIE_CSS);
        }
        if (empty($file)) {
            // Use default css file
            $file = $this->_path . 'views/css/mollie.css';
        } else {
            // Use a custom css file
            if (defined('_PS_BASE_URL_')) {
                $file = str_replace('{BASE}', _PS_BASE_URL_, $file);
            }
            if (defined('_PS_THEME_DIR_')) {
                $file = str_replace('{THEME}', _PS_THEME_DIR_, $file);
            }
            if (defined('_PS_CSS_DIR_')) {
                $file = str_replace('{CSS}', _PS_CSS_DIR_, $file);
            }
            if (defined('_THEME_MOBILE_DIR_')) {
                $file = str_replace('{MOBILE}', _THEME_MOBILE_DIR_, $file);
            }
            if (defined('_THEME_MOBILE_CSS_DIR_')) {
                $file = str_replace('{MOBILE_CSS}', _THEME_MOBILE_CSS_DIR_, $file);
            }
            if (defined('_PS_THEME_OVERRIDE_DIR_')) {
                $file = str_replace('{OVERRIDE}', _PS_THEME_OVERRIDE_DIR_, $file);
            }
        }

        return $file;
    }

    // Hooks

    /**
     * @throws PrestaShopException
     */
    public function hookActionFrontControllerSetMedia()
    {
        if ($this->context->controller instanceof OrderControllerCore) {

            Media::addJsDef([
                'profileId' => Configuration::get(Mollie\Config\Config::MOLLIE_PROFILE_ID),
                'isoCode' => $this->context->language->language_code,
                'isTestMode' => self::isTestMode()
            ]);
            if (self::isVersion17()) {
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
                'isPS17' => self::isVersion17(),
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
        $cartId = Cart::getCartIdByOrderId((int)$params['id_order']);
        $transaction = static::getPaymentBy('cart_id', (int)$cartId);
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
            'tracking' => static::getShipmentInformation($params['id_order']),
            'publicPath' => __PS_BASE_URI__ . 'modules/' . basename(__FILE__, '.php') . '/views/js/dist/',
            'webPackChunks' => static::getWebpackChunks('app'),
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
            'msg_pay_with' => $this->lang('Pay with %s'),
            'msg_bankselect' => $this->lang('Select your bank:'),
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
        if (!self::isVersion17() && $isIFrameEnabled) {
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
            $paymentFee = $this->getPaymentFee($methodObj, $cart->getOrderTotal());
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
        $payment = $this->getPaymentBy('cart_id', (int)Tools::getValue('id_cart'));
        if ($payment && $payment['bank_status'] == \Mollie\Api\Types\PaymentStatus::STATUS_PAID) {
            $this->context->smarty->assign('okMessage', $this->lang('Thank you. Your payment has been received.'));

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
            static::tryAddOrderReferenceColumn();

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

        $paymentFee = Mollie::getPaymentFee($molPaymentMethod, $amount);
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
        if (!static::isLocalEnvironment()) {
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
            if (!static::isLocalEnvironment()) {
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
            foreach (static::spreadAmountEvenly($roundedTotalWithTax, $quantity) as $unitPrice => $qty) {
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
     * Spread the amount evenly
     *
     * @param float $amount
     * @param int $qty
     *
     * @return array Spread amounts
     *
     * @since 3.3.3
     */
    public static function spreadAmountEvenly($amount, $qty)
    {
        // Start with a freshly rounded amount
        $amount = (float)round($amount, Mollie\Config\Config::API_ROUNDING_PRECISION);
        // Estimate a target spread amount to begin with
        $spreadTotals = array_fill(1, $qty, round($amount / $qty, Mollie\Config\Config::API_ROUNDING_PRECISION));
        $newTotal = $spreadTotals[1] * $qty;
        // Calculate the difference between applying this amount only and the total amount given
        $difference = abs(round($newTotal - $amount, Mollie\Config\Config::API_ROUNDING_PRECISION));
        // Start at the last index
        $index = $qty;
        // Keep going until there's no longer a difference
        $difference = new \PrestaShop\Decimal\Number((string) $difference);
        $decreaseNumber = new \PrestaShop\Decimal\Number('0.01');
        // Keep going until there's no longer a difference
        while ($difference->getPrecision() > 0) {
            // Go for a new pass if there's still a difference after the current one
            $index = $index > 0 ? $index : $qty;
            // Difference is going to be decreased by 0.01
            $difference = $difference->minus($decreaseNumber);
            // Apply the rounding difference at the current index
            $spreadTotals[$index--] += $newTotal < $amount ? 0.01 : -0.01;
        }
        // At the end, compensate for floating point inaccuracy and apply to the last index (points at the lowest amount)
        if (round(abs($amount - array_sum($spreadTotals)), Mollie\Config\Config::API_ROUNDING_PRECISION) >= 0.01) {
            $spreadTotals[count($spreadTotals) - 1] += 0.01;
        }

        // Group the amounts and return the unit prices at the indices, with the quantities as values
        return array_count_values(array_map('strval', $spreadTotals));
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
        $spread = static::spreadAmountEvenly($newTotal, $quantity);
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
     * Ajax process install module update
     *
     * @since 3.0.0
     */
    public function ajaxProcessInstallUpdate()
    {
        header('Content-Type: application/json;charset=UTF-8');
        try {
            $result = $this->unzipModule();
        } catch (Adapter_Exception $e) {
            $result = false;
        } catch (PrestaShopDatabaseException $e) {
            $result = false;
        } catch (PrestaShopException $e) {
            $result = false;
        }

        die(json_encode([
            'success' => $result,
            'message' => isset($this->context->controller->errors[0]) ? $this->context->controller->errors[0] : '',
        ]));
    }

    /**
     * Ajax process run module upgrade
     *
     * @since 3.0.0
     */
    public function ajaxProcessRunUpgrade()
    {
        header('Content-Type: application/json;charset=UTF-8');
        try {
            $result = $this->runUpgradeModule();
        } catch (PrestaShopDatabaseException $e) {
            $error = $e->getMessage();
            $result = false;
        } catch (PrestaShopException $e) {
            $error = $e->getMessage();
            $result = false;
        }
        if (method_exists('Module', 'upgradeModuleVersion')) {
            Module::upgradeModuleVersion($this->name, $this->version);
        }

        die(json_encode([
            'success' => $result,
            'message' => isset($error) ? $error : '',
        ]));
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
     * Unzip the module
     *
     * @return bool Whether the module has been successfully extracted
     *
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @since 3.0.0
     */
    protected function unzipModule()
    {
        if (@file_exists(_PS_MODULE_DIR_ . 'mollie-update.zip')) {
            return $this->extractModuleArchive($this->name, _PS_MODULE_DIR_ . 'mollie-update.zip');
        }

        return false;
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
     * Checks if status is paid
     *
     * @param $statusId
     * @return bool
     */
    private function isPaid($statusId)
    {
        $status = array_search($statusId, Mollie\Config\Config::getStatuses(), false);
        if ($status === \Mollie\Api\Types\PaymentStatus::STATUS_PAID
            || $status === \Mollie\Api\Types\PaymentStatus::STATUS_AUTHORIZED) {
            return true;
        }

        return false;
    }

    /**
     *
     *
     * @param $idCart
     * @return bool
     */
    private function isCartWithTaxes($idCart)
    {
        $cart = new Cart($idCart);
        $customer = new Customer($cart->id_customer);
        $group_price_display_method = Group::getPriceDisplayMethod($customer->id_default_group);
        $withTaxes = true;
        if ($group_price_display_method === Mollie\Config\Config::PRICE_DISPLAY_METHOD_NO_TAXES) {
            $withTaxes = false;
        }

        return $withTaxes;
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
     * Get the selected API
     *
     * @throws PrestaShopException
     *
     * @since 3.3.0
     *
     * @public ✓ This method is part of the public API
     */
    public static function selectedApi()
    {
        /** @var static $mollie */
        $mollie = Module::getInstanceByName('mollie');
        if (!in_array(static::$selectedApi, [Mollie\Config\Config::MOLLIE_ORDERS_API, Mollie\Config\Config::MOLLIE_PAYMENTS_API])) {
            static::$selectedApi = Configuration::get(Mollie\Config\Config::MOLLIE_API);
            if (!static::$selectedApi
                || !in_array(static::$selectedApi, [Mollie\Config\Config::MOLLIE_ORDERS_API, Mollie\Config\Config::MOLLIE_PAYMENTS_API])
                || $mollie->checkRoundingMode()
            ) {
                static::$selectedApi = Mollie\Config\Config::MOLLIE_PAYMENTS_API;
            }
        }

        return static::$selectedApi;
    }

    /**
     * @param string $transactionId
     * @param bool $process Process the new payment/order status
     *
     * @return array|null
     *
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     * @throws \Mollie\Api\Exceptions\ApiException
     * @throws CoreException
     *
     * @since 3.3.0
     * @since 3.3.2 $process option
     */
    public function getFilteredApiPayment($transactionId, $process = false)
    {
        /** @var Payment $payment */
        $payment = $this->api->payments->get($transactionId);
        if ($process) {
            if (!Tools::isSubmit('module')) {
                $_GET['module'] = $this->name;
            }
            $webhookController = new MollieWebhookModuleFrontController();
            $webhookController->processTransaction($payment);
        }

        if ($payment && method_exists($payment, 'refunds')) {
            $refunds = $payment->refunds();
            if (empty($refunds)) {
                $refunds = [];
            }
            $refunds = array_map(function ($refund) {
                return array_intersect_key(
                    (array)$refund,
                    array_flip([
                        'resource',
                        'id',
                        'amount',
                        'createdAt',
                    ]));
            }, (array)$refunds);
            $payment = array_intersect_key(
                (array)$payment,
                array_flip([
                    'resource',
                    'id',
                    'mode',
                    'amount',
                    'settlementAmount',
                    'amountRefunded',
                    'amountRemaining',
                    'description',
                    'method',
                    'status',
                    'createdAt',
                    'paidAt',
                    'canceledAt',
                    'expiresAt',
                    'failedAt',
                    'metadata',
                    'isCancelable',
                ])
            );
            $payment['refunds'] = (array)$refunds;
        } else {
            $payment = null;
        }

        return $payment;
    }

    /**
     * @param string $transactionId
     * @param bool $process Process the new payment/order status
     *
     * @return array|null
     *
     * @throws Adapter_Exception
     * @throws ErrorException
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     * @throws \Mollie\Api\Exceptions\ApiException
     * @throws CoreException
     *
     * @since 3.3.0
     * @since 3.3.2 $process option
     */
    public function getFilteredApiOrder($transactionId, $process = false)
    {
        /** @var \Mollie\Api\Resources\Order $order */
        $order = $this->api->orders->get($transactionId, ['embed' => 'payments']);

        if ($order && method_exists($order, 'refunds')) {
            $refunds = $order->refunds();
            if (empty($refunds)) {
                $refunds = [];
            }
            $refunds = array_map(function ($refund) {
                return array_intersect_key(
                    (array)$refund,
                    array_flip([
                        'resource',
                        'id',
                        'amount',
                        'createdAt',
                    ]));
            }, (array)$refunds);
            $order = array_intersect_key(
                (array)$order,
                array_flip([
                    'resource',
                    'id',
                    'mode',
                    'amount',
                    'settlementAmount',
                    'amountCaptured',
                    'status',
                    'method',
                    'metadata',
                    'isCancelable',
                    'createdAt',
                    'lines',
                ])
            );
            $order['refunds'] = (array)$refunds;
        } else {
            $order = null;
        }

        return $order;
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
        try {
            /** @var \Mollie\Service\ApiService $apiService */
            $apiService = $this->getContainer(\Mollie\Service\ApiService::class);
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
            'countries' => $this->getActiveCountriesList(),
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

        return ['success' => true, 'carriers' => static::carrierConfig()];
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

        $input = @json_decode(Tools::file_get_contents('php://input'), true);

        $mollieData = static::getPaymentBy('transaction_id', $input['transactionId']);

        try {
            $adminOrdersController = new AdminOrdersController();
            $access = Profile::getProfileAccess($this->context->employee->id_profile, $adminOrdersController->id);

            if ($input['resource'] === 'payments') {
                switch ($input['action']) {
                    case 'refund':
                        // Check order edit permissions
                        if (!$access || empty($access['edit'])) {
                            return [
                                'success' => false,
                                'message' => $this->l('You do not have permission to refund payments'),
                            ];
                        }
                        if (!isset($input['amount']) || empty($input['amount'])) {
                            // No amount = full refund
                            $status = $this->doPaymentRefund($mollieData['transaction_id']);
                        } else {
                            $status = $this->doPaymentRefund($mollieData['transaction_id'], $input['amount']);
                        }

                        return [
                            'success' => isset($status['status']) && $status['status'] === 'success',
                            'payment' => static::getFilteredApiPayment($input['transactionId'], false),
                        ];
                    case 'retrieve':
                        // Check order view permissions
                        if (!$access || empty($access['view'])) {
                            return [
                                'success' => false,
                                'message' => sprintf($this->l('You do not have permission to %s payments'), $this->l('view')),
                            ];
                        }
                        return [
                            'success' => true,
                            'payment' => static::getFilteredApiPayment($input['transactionId'], false)
                        ];
                    default:
                        return ['success' => false];
                }
            } elseif ($input['resource'] === 'orders') {
                switch ($input['action']) {
                    case 'retrieve':
                        // Check order edit permissions
                        if (!$access || empty($access['view'])) {
                            return [
                                'success' => false,
                                'message' => sprintf($this->l('You do not have permission to %s payments'), $this->l('edit')),
                            ];
                        }
                        $info = static::getPaymentBy('transaction_id', $input['transactionId']);
                        if (!$info) {
                            return ['success' => false];
                        }
                        $tracking = static::getShipmentInformation($info['order_id']);

                        return [
                            'success' => true,
                            'order' => static::getFilteredApiOrder($input['transactionId'], false),
                            'tracking' => $tracking,
                        ];
                    case 'ship':
                        // Check order edit permissions
                        if (!$access || empty($access['edit'])) {
                            return [
                                'success' => false,
                                'message' => sprintf($this->l('You do not have permission to %s payments'), $this->l('ship')),
                            ];
                        }
                        $status = $this->doShipOrderLines($input['transactionId'], isset($input['orderLines']) ? $input['orderLines'] : [], isset($input['tracking']) ? $input['tracking'] : null);
                        return array_merge($status, ['order' => static::getFilteredApiOrder($input['transactionId'], static::isLocalEnvironment())]);
                    case 'refund':
                        // Check order edit permissions
                        if (!$access || empty($access['edit'])) {
                            return [
                                'success' => false,
                                'message' => sprintf($this->l('You do not have permission to %s payments'), $this->l('refund')),
                            ];
                        }
                        $status = $this->doRefundOrderLines($input['transactionId'], isset($input['orderLines']) ? $input['orderLines'] : []);
                        return array_merge($status, ['order' => static::getFilteredApiOrder($input['transactionId'], false)]);
                    case 'cancel':
                        // Check order edit permissions
                        if (!$access || empty($access['edit'])) {
                            return [
                                'success' => false,
                                'message' => sprintf($this->l('You do not have permission to %s payments'), $this->l('cancel')),
                            ];
                        }
                        $status = $this->doCancelOrderLines($input['transactionId'], isset($input['orderLines']) ? $input['orderLines'] : []);
                        return array_merge($status, ['order' => static::getFilteredApiOrder($input['transactionId'], static::isLocalEnvironment())]);
                    default:
                        return ['success' => false];
                }
            }
        } catch (Exception $e) {
            PrestaShopLogger::addLog("Mollie module error: {$e->getMessage()}");
            return ['success' => false];
        }

        return ['success' => false];
    }

    /**
     * Get module version from database
     *
     * @param string $module
     *
     * @return string
     *
     * @throws PrestaShopException
     * @since 3.3.0
     */
    public static function getDatabaseVersion($module = 'mollie')
    {
        $sql = new DbQuery();
        $sql->select('`version`');
        $sql->from('module');
        $sql->where('`name` = \'' . pSQL($module) . '\'');

        return (string)Db::getInstance()->getValue($sql);
    }

    /**
     * Add the order reference column in case the module upgrade script hasn't run
     *
     * @return bool
     *
     * @since 3.3.0
     */
    public static function tryAddOrderReferenceColumn()
    {
        try {
            if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
                SELECT COUNT(*)
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = \'' . _DB_NAME_ . '\'
                AND TABLE_NAME = \'' . _DB_PREFIX_ . 'mollie_payments\'
                AND COLUMN_NAME = \'order_reference\'')
            ) {
                return Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'mollie_payments` ADD `order_reference` varchar(191)');
            }
        } catch (PrestaShopException $e) {
            return false;
        }

        return true;
    }

    /**
     * 2D array sort by key
     *
     * @param mixed $array
     * @param mixed $key
     *
     * @since 3.3.0
     */
    protected function aasort(&$array, $key)
    {
        $sorter = [];
        $ret = [];
        reset($array);
        foreach ($array as $ii => $va) {
            $sorter[$ii] = $va[$key];
        }
        asort($sorter);
        foreach ($sorter as $ii => $va) {
            $ret[$ii] = $array[$ii];
        }
        $array = $ret;
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
        $shipmentInfo = static::getShipmentInformation($idOrder);

        if (!(Configuration::get(Mollie\Config\Config::MOLLIE_AUTO_SHIP_MAIN) && in_array($orderStatusNumber, $checkStatuses)
            ) || $shipmentInfo === null
        ) {
            return;
        }

        try {
            $dbPayment = static::getPaymentBy('order_id', (int)$idOrder);
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

    /**
     * Get shipment information
     *
     * @param int $idOrder
     *
     * @return array|null
     *
     * @throws PrestaShopException
     * @throws Adapter_Exception
     * @since 3.3.0
     */
    public static function getShipmentInformation($idOrder)
    {
        $order = new Order($idOrder);
        if (!Validate::isLoadedObject($order)) {
            return null;
        }
        $invoiceAddress = new Address($order->id_address_invoice);
        $deliveryAddress = new Address($order->id_address_delivery);
        $carrierConfig = static::getOrderCarrierConfig($idOrder);
        if (!Validate::isLoadedObject($invoiceAddress)
            || !Validate::isLoadedObject($deliveryAddress)
            || !$carrierConfig
        ) {
            return [];
        }

        if ($carrierConfig['source'] === Mollie\Config\Config::MOLLIE_CARRIER_NO_TRACKING_INFO) {
            return [];
        }

        if ($carrierConfig['source'] === Mollie\Config\Config::MOLLIE_CARRIER_MODULE) {
            $carrier = new Carrier($order->id_carrier);
            if (in_array($carrier->external_module_name, ['postnl', 'myparcel'])) {
                if (version_compare(static::getDatabaseVersion($carrier->external_module_name), '2.1.0', '>=')) {
                    $table = $carrier->external_module_name === 'postnl' ? 'postnlmod_order' : 'myparcel_order';
                    $sql = new DbQuery();
                    $sql->select('`tracktrace`, `postcode`');
                    $sql->from(bqSQL($table));
                    $sql->where('`id_order` = \'' . pSQL($idOrder) . '\'');

                    try {
                        $info = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
                        if ($info['tracktrace'] && $info['postcode']) {
                            $postcode = Tools::strtoupper(str_replace(' ', '', $info['postcode']));
                            $langIso = Tools::strtoupper(Language::getIsoById($order->id_lang));
                            $countryIso = Tools::strtoupper(Country::getIsoById($deliveryAddress->id_country));
                            $tracktrace = $info['tracktrace'];

                            return [
                                'tracking' => [
                                    'carrier' => 'PostNL',
                                    'code' => $info['tracktrace'],
                                    'url' => "http://postnl.nl/tracktrace/?L={$langIso}&B={$tracktrace}&P={$postcode}&D={$countryIso}&T=C",
                                ],
                            ];
                        }
                    } catch (PrestaShopDatabaseException $e) {
                        return [];
                    }
                }
            }
            return [];
        }

        if ($carrierConfig['source'] === Mollie\Config\Config::MOLLIE_CARRIER_CARRIER) {
            $carrier = new Carrier($order->id_carrier);
            $shippingNumber = $order->shipping_number;
            if (!$shippingNumber && method_exists($order, 'getIdOrderCarrier')) {
                $orderCarrier = new OrderCarrier($order->getIdOrderCarrier());
                $shippingNumber = $orderCarrier->tracking_number;
            }

            if (!$shippingNumber || !$carrier->name) {
                return [];
            }

            return [
                'tracking' => [
                    'carrier' => $carrier->name,
                    'code' => $shippingNumber,
                    'url' => str_replace('@', $shippingNumber, $carrier->url),
                ],
            ];
        }

        if ($carrierConfig['source'] === Mollie\Config\Config::MOLLIE_CARRIER_CUSTOM) {
            $carrier = new Carrier($order->id_carrier);
            $shippingNumber = $order->shipping_number;
            if (!$shippingNumber && method_exists($order, 'getIdOrderCarrier')) {
                $orderCarrier = new OrderCarrier($order->getIdOrderCarrier());
                $shippingNumber = $orderCarrier->tracking_number;
            }
//
//            if (!$shippingNumber || !$carrier->name) {
//                return array();
//            }

            $invoicePostcode = Tools::strtoupper(str_replace(' ', '', $invoiceAddress->postcode));
            $invoiceCountryIso = Tools::strtoupper(Country::getIsoById($invoiceAddress->id_country));
            $deliveryPostcode = Tools::strtoupper(str_replace(' ', '', $deliveryAddress->postcode));
            $deliveryCountryIso = Tools::strtoupper(Country::getIsoById($deliveryAddress->id_country));

            $langIso = Tools::strtoupper(Language::getIsoById($order->id_lang));

            $info = [
                '@' => $shippingNumber,
                '%%shipping_number%%' => $shippingNumber,
                '%%invoice.country_iso%%' => $invoiceCountryIso,
                '%%invoice.postcode%%' => $invoicePostcode,
                '%%delivery.country_iso%%' => $deliveryCountryIso,
                '%%delivery.postcode%%' => $deliveryPostcode,
                '%%lang_iso%%' => $langIso,
            ];

            return [
                'tracking' => [
                    'carrier' => $carrier->name,
                    'code' => $shippingNumber,
                    'url' => str_ireplace(
                        array_keys($info),
                        array_values($info),
                        $carrierConfig['custom_url']
                    ),
                ],
            ];
        }

        return [];
    }

    /**
     * Get carrier config for order
     *
     * @param Order|int $order
     *
     * @return array|null Configuration or `null` if not tracking
     *
     * @throws PrestaShopException
     */
    public static function getOrderCarrierConfig($order)
    {
        if (is_int($order)) {
            $order = new Order($order);
        }

        if (!$carrierConfig = @json_decode(Configuration::get(Mollie\Config\Config::MOLLIE_TRACKING_URLS), true)) {
            return null;
        }

        if (!Validate::isLoadedObject($order) || !$order->id_carrier) {
            return null;
        }

        if (!isset($carrierConfig[$order->id_carrier]) || !isset($carrierConfig[$order->id_carrier]['source'])) {
            return null;
        }

        return $carrierConfig[$order->id_carrier];
    }

    /**
     * Get the webpack chunks for a given entry name
     *
     * @param string $entry Entry name
     *
     * @return array Array with chunk files, should be loaded in the given order
     *
     * @since 3.4.0
     */
    public static function getWebpackChunks($entry)
    {
        static $manifest = null;
        if (!$manifest) {
            $manifest = [];
            foreach (include(_PS_MODULE_DIR_ . 'mollie/views/js/dist/manifest.php') as $chunk) {
                $manifest[$chunk['name']] = array_map(function ($chunk) {
                    return \Mollie\Utility\UrlPathUtility::getMediaPath(_PS_MODULE_DIR_ . "mollie/views/js/dist/{$chunk}");
                }, $chunk['files']);
            }
        }

        return isset($manifest[$entry]) ? $manifest[$entry] : [];
    }

    /**
     * Checks if strings ends with the given needle
     *
     * @param string $haystack
     * @param string $needle
     *
     * @return bool
     *
     * @since 3.4.0
     */
    protected static function endsWith($haystack, $needle)
    {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }

        return (substr($haystack, -$length) === $needle);
    }

    /**
     * Get all status values from the form.
     *
     * @param $key string The key that is used in the HelperForm
     *
     * @return array Array with statuses
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @since 3.3.0
     */
    protected function getStatusesValue($key)
    {
        $statesEnabled = [];
        foreach (OrderState::getOrderStates($this->context->language->id) as $state) {
            if (Tools::isSubmit($key . '_' . $state['id_order_state'])) {
                $statesEnabled[] = $state['id_order_state'];
            }
        }

        return $statesEnabled;
    }

    /**
     * Removed, PS 1.7 translation system does not work for hybrid modules, yet.
     *
     * @param $text
     *
     * @return string
     *
     * @deprecated 3.4.0
     */
    public function translate($text)
    {
        return $this->l($text);
    }

    /**
     * Get page location
     *
     * @param string $class
     * @param int|null $idLang
     *
     * @return string
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @since 3.3.2
     */
    public static function getMenuLocation($class, $idLang = null)
    {
        if (!$idLang) {
            $idLang = Context::getContext()->language->id;
        }

        return implode(' > ', array_reverse(array_unique(array_map(function ($tab) use ($idLang) {
            return $tab->name[$idLang];
        }, static::getTabTreeByClass($class)))));
    }

    /**
     * Get the entire tab tree by tab class name
     *
     * @param string $class
     *
     * @return Tab[]|null
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @since 3.3.2
     */
    public static function getTabTreeByClass($class)
    {
        $tabs = [];
        $depth = 10;
        $tab = Tab::getInstanceFromClassName($class);
        while (Validate::isLoadedObject($tab) && $depth > 0) {
            $depth--;
            $tabs[] = $tab;
            $tab = new Tab($tab->id_parent);
        }

        return $tabs;
    }

    /**
     * Get tab name by tab class
     *
     * @param string $class
     * @param int|null $idLang
     *
     * @return string
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @since 3.3.2
     */
    public static function getTabNameByClass($class, $idLang = null)
    {
        $tab = Tab::getInstanceFromClassName($class);
        if (!$tab instanceof Tab) {
            throw new InvalidArgumentException('Tab not found');
        }

        if (!$idLang) {
            $idLang = Context::getContext()->language->id;
        }

        return $tab->name[$idLang];
    }

    /**
     * Check if local domain
     *
     * @param string|null $host
     *
     * @return bool
     *
     * @since 3.3.2
     */
    public static function isLocalEnvironment($host = null)
    {
        if (!$host) {
            $host = Tools::getHttpHost(false, false, true);
        }
        $hostParts = explode('.', $host);
        $tld = end($hostParts);

        return in_array($tld, ['localhost', 'test', 'dev', 'app', 'local', 'invalid', 'example'])
            || (filter_var($host, FILTER_VALIDATE_IP)
                && !filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE));
    }

    public function getActiveCountriesList($onlyActive = true)
    {
        $langId = $this->context->language->id;
        $countries = Country::getCountries($langId, $onlyActive);
        $countriesWithNames = [];
        $countriesWithNames[] = [
            'id' => 0,
            'name' => $this->l('All')
        ];
        foreach ($countries as $key => $country) {
            $countriesWithNames[] = [
                'id' => $key,
                'name' => $country['name'],
            ];
        }

        return $countriesWithNames;
    }

    public static function isVersion17()
    {
        return (bool)version_compare(_PS_VERSION_, '1.7', '>=');
    }

    public static function isTestMode()
    {
        $apiKey = Configuration::get(Mollie\Config\Config::MOLLIE_API_KEY);
        if (strpos($apiKey, 'test') === 0) {
            return true;
        }

        return false;
    }

    public static function getPaymentFee(MolPaymentMethod $paymentMethod, $totalCartPrice)
    {
        switch ($paymentMethod->surcharge) {
            case Mollie\Config\Config::FEE_FIXED_FEE:
                $totalFeePrice = new PrestaShop\Decimal\Number($paymentMethod->surcharge_fixed_amount);
                break;
            case Mollie\Config\Config::FEE_PERCENTAGE:
                $totalCartPrice = new PrestaShop\Decimal\Number((string) $totalCartPrice);
                $surchargePercentage = new PrestaShop\Decimal\Number($paymentMethod->surcharge_percentage);
                $maxPercentage = new PrestaShop\Decimal\Number('100');
                $totalFeePrice = $totalCartPrice->times(
                    $surchargePercentage->dividedBy(
                        $maxPercentage
                    )
                );
                break;
            case Mollie\Config\Config::FEE_FIXED_FEE_AND_PERCENTAGE:
                $totalCartPrice = new PrestaShop\Decimal\Number((string) $totalCartPrice);
                $surchargePercentage = new PrestaShop\Decimal\Number($paymentMethod->surcharge_percentage);
                $maxPercentage = new PrestaShop\Decimal\Number('100');
                $surchargeFixedPrice = new PrestaShop\Decimal\Number($paymentMethod->surcharge_fixed_amount);
                $totalFeePrice = $totalCartPrice->times(
                    $surchargePercentage->dividedBy(
                        $maxPercentage
                    )
                )->plus($surchargeFixedPrice);
                break;
            case Mollie\Config\Config::FEE_NO_FEE:
            default:
                return false;
        }

        $surchargeMaxValue = new PrestaShop\Decimal\Number($paymentMethod->surcharge_limit);
        $zero = new PrestaShop\Decimal\Number('0');
        if ($surchargeMaxValue->isGreaterThan($zero) && $totalFeePrice->isGreaterOrEqualThan($surchargeMaxValue)) {
            $totalFeePrice = $surchargeMaxValue;
        }

        return $totalFeePrice->toPrecision(2);
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
