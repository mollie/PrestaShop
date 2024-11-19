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

namespace Mollie\Builder;

use HelperFormCore as HelperForm;
use Mollie;
use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Adapter\Context;
use Mollie\Adapter\Language;
use Mollie\Adapter\Link;
use Mollie\Adapter\Smarty;
use Mollie\Api\Types\OrderStatus;
use Mollie\Api\Types\PaymentStatus;
use Mollie\Api\Types\RefundStatus;
use Mollie\Config\Config;
use Mollie\Provider\CustomLogoProviderInterface;
use Mollie\Repository\TaxRulesGroupRepositoryInterface;
use Mollie\Service\ApiService;
use Mollie\Service\ConfigFieldService;
use Mollie\Service\CountryService;
use Mollie\Service\MolCarrierInformationService;
use Mollie\Utility\EnvironmentUtility;
use Mollie\Utility\TagsUtility;
use OrderStateCore as OrderState;
use ToolsCore as Tools;

if (!defined('_PS_VERSION_')) {
    exit;
}

class FormBuilder
{
    const FILE_NAME = 'FormBuilder';

    /**
     * @var Mollie
     */
    private $module;

    /**
     * @var ApiService
     */
    private $apiService;

    /**
     * @var CountryService
     */
    private $countryService;

    /**
     * @var Language
     */
    private $lang;

    /**
     * @var Smarty
     */
    private $smarty;

    private $link;

    /**
     * @var ConfigFieldService
     */
    private $configFieldService;

    /**
     * @var MolCarrierInformationService
     */
    private $carrierInformationService;

    /**
     * @var CustomLogoProviderInterface
     */
    private $creditCardLogoProvider;

    /**
     * @var ConfigurationAdapter
     */
    private $configuration;

    /** @var TaxRulesGroupRepositoryInterface */
    private $taxRulesGroupRepository;

    /** @var Context */
    private $context;

    public function __construct(
        Mollie $module,
        ApiService $apiService,
        CountryService $countryService,
        ConfigFieldService $configFieldService,
        MolCarrierInformationService $carrierInformationService,
        Language $lang,
        Smarty $smarty,
        Link $link,
        CustomLogoProviderInterface $creditCardLogoProvider,
        ConfigurationAdapter $configuration,
        TaxRulesGroupRepositoryInterface $taxRulesGroupRepository,
        Context $context
    ) {
        $this->module = $module;
        $this->apiService = $apiService;
        $this->countryService = $countryService;
        $this->lang = $lang;
        $this->smarty = $smarty;
        $this->link = $link;
        $this->configFieldService = $configFieldService;
        $this->carrierInformationService = $carrierInformationService;
        $this->creditCardLogoProvider = $creditCardLogoProvider;
        $this->configuration = $configuration;
        $this->taxRulesGroupRepository = $taxRulesGroupRepository;
        $this->context = $context;
    }

    public function buildSettingsForm()
    {
        $isApiKeyProvided = (bool) EnvironmentUtility::getApiKey();
        $isApiKeyProvided = ($isApiKeyProvided && $this->module->getApiClient() !== null);

        $inputs = $this->getAccountSettingsSection($isApiKeyProvided);

        if ($isApiKeyProvided) {
            $inputs = array_merge($inputs, $this->getAdvancedSettingsSection());
        }

        $fields = [
            'form' => [
                'tabs' => $this->getSettingTabs($isApiKeyProvided),
                'input' => $inputs,
                'submit' => [
                    'title' => $this->module->l('Save', self::FILE_NAME),
                    'class' => 'btn btn-default pull-right',
                ],
            ],
        ];

        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->module->getTable();
        $helper->module = $this->module;
        $helper->default_form_language = $this->module->getContext()->language->id;
        $helper->allow_employee_form_lang = $this->configuration->get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->module->getIdentifier();
        $helper->submit_action = 'submitmollie';
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = [
            'fields_value' => $this->configFieldService->getConfigFieldsValues(),
            'languages' => $this->module->getContext()->controller->getLanguages(),
            'id_language' => $this->module->getContext()->language->id,
        ];

        return $helper->generateForm([$fields]);
    }

    protected function getAccountSettingsSection($isApiKeyProvided)
    {
        $generalSettings = 'general_settings';

        $input = [
            [
                'type' => 'mollie-support',
                'tab' => $generalSettings,
                'name' => '',
            ],
        ];

        $input[] = [
            'type' => 'mollie-hidden-input',
            'tab' => $generalSettings,
            'name' => Config::MOLLIE_ENV_CHANGED,
            'value' => 0,
        ];

        if ($isApiKeyProvided) {
            $input[] =
                [
                    'type' => 'select',
                    'label' => $this->module->l('Environment', self::FILE_NAME),
                    'tab' => $generalSettings,
                    'name' => Config::MOLLIE_ENVIRONMENT,
                    'options' => [
                        'query' => [
                            [
                                'id' => Config::ENVIRONMENT_TEST,
                                'name' => $this->module->l('Test', self::FILE_NAME),
                            ],
                            [
                                'id' => Config::ENVIRONMENT_LIVE,
                                'name' => $this->module->l('Live', self::FILE_NAME),
                            ],
                        ],
                        'id' => 'id',
                        'name' => 'name',
                    ],
                ];
            $input[] = [
                'type' => 'mollie-password',
                'label' => $this->module->l('API Key Test', self::FILE_NAME),
                'tab' => $generalSettings,
                'desc' => TagsUtility::ppTags(
                    $this->module->l('Go to your [1]Mollie account[/1] to get your API keys. They start with test and live.', self::FILE_NAME),
                    [$this->module->display($this->module->getPathUri(), 'views/templates/admin/profile.tpl')]
                ),
                'name' => Config::MOLLIE_API_KEY_TEST,
                'required' => true,
                'class' => 'fixed-width-xxl',
                'form_group_class' => 'js-test-api-group',
            ];
            $input[] = [
                'type' => 'mollie-password',
                'label' => $this->module->l('Live API key', self::FILE_NAME),
                'tab' => $generalSettings,
                'name' => Config::MOLLIE_API_KEY,
                'required' => true,
                'class' => 'fixed-width-xxl',
                'form_group_class' => 'js-live-api-group',
            ];
            $input[] = [
                'type' => 'mollie-button',
                'label' => '',
                'tab' => $generalSettings,
                'name' => Config::MOLLIE_API_KEY_TESTING_BUTTON,
                'text' => $this->module->l('Test API key', self::FILE_NAME),
                'class' => 'js-test-api-keys',
                'form_group_class' => 'js-api-key-test',
            ];
            $input[] =
                [
                    'type' => 'mollie-h3',
                    'tab' => $generalSettings,
                    'name' => '',
                    'title' => '',
                ];
        } else {
            $input[] =
                [
                    'type' => 'mollie-switch',
                    'label' => $this->module->l('Do you already have a Mollie account?', self::FILE_NAME),
                    'name' => Config::MOLLIE_ACCOUNT_SWITCH,
                    'tab' => $generalSettings,
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'active_on',
                            'value' => true,
                            'label' => $this->module->l('Enabled', self::FILE_NAME),
                        ],
                        [
                            'id' => 'active_off',
                            'value' => false,
                            'label' => $this->module->l('Disabled', self::FILE_NAME),
                        ],
                    ],
                    'desc' => $this->module->display(
                        $this->module->getPathUri(), 'views/templates/admin/create_new_account_link.tpl'
                    ),
                ];
            $input[] = [
                'type' => 'select',
                'label' => $this->module->l('Environment', self::FILE_NAME),
                'tab' => $generalSettings,
                'name' => Config::MOLLIE_ENVIRONMENT,
                'options' => [
                    'query' => [
                        [
                            'id' => Config::ENVIRONMENT_TEST,
                            'name' => $this->module->l('Test', self::FILE_NAME),
                        ],
                        [
                            'id' => Config::ENVIRONMENT_LIVE,
                            'name' => $this->module->l('Live', self::FILE_NAME),
                        ],
                    ],
                    'id' => 'id',
                    'name' => 'name',
                ],
            ];
            $input[] = [
                'type' => 'mollie-password',
                'label' => $this->module->l('API Key Test', self::FILE_NAME),
                'tab' => $generalSettings,
                'desc' => TagsUtility::ppTags(
                    $this->module->l('You can find your API key in your [1]Mollie Profile[/1]', self::FILE_NAME),
                    [$this->module->display($this->module->getPathUri(), 'views/templates/admin/profile.tpl')]
                ),
                'name' => Config::MOLLIE_API_KEY_TEST,
                'required' => true,
                'class' => 'fixed-width-xxl',
                'form_group_class' => 'js-test-api-group',
            ];
            $input[] = [
                'type' => 'mollie-password',
                'label' => $this->module->l('API Key Live', self::FILE_NAME),
                'tab' => $generalSettings,
                'name' => Config::MOLLIE_API_KEY,
                'required' => true,
                'class' => 'fixed-width-xxl',
                'form_group_class' => 'js-live-api-group',
            ];
        }
        if (!$isApiKeyProvided) {
            return $input;
        }
        $input[] = [
            'type' => 'mollie-save-warning',
            'name' => 'warning',
            'tab' => $generalSettings,
        ];

        $input[] = [
            'type' => 'switch',
            'label' => $this->module->l('Use Mollie Components for credit cards', self::FILE_NAME),
            'tab' => $generalSettings,
            'name' => Config::MOLLIE_IFRAME[(int) $this->configuration->get(Config::MOLLIE_ENVIRONMENT) ? 'production' : 'sandbox'],
            'desc' => TagsUtility::ppTags(
                $this->module->l('Read more about [1]Mollie Components[/1] and how it improves your conversion.', self::FILE_NAME),
                [$this->module->display($this->module->getPathUri(), 'views/templates/admin/mollie_components_info.tpl')]
            ),
            $this->module->l('Read more about Mollie Components and how it improves your conversion', self::FILE_NAME),
            'is_bool' => true,
            'values' => [
                [
                    'id' => 'active_on',
                    'value' => true,
                    'label' => $this->module->l('Enabled', self::FILE_NAME),
                ],
                [
                    'id' => 'active_off',
                    'value' => false,
                    'label' => $this->module->l('Disabled', self::FILE_NAME),
                ],
            ],
        ];

        $input[] = [
            'type' => 'switch',
            'label' => $this->module->l('Use one-click payments for credit cards', self::FILE_NAME),
            'tab' => $generalSettings,
            'name' => Config::MOLLIE_SINGLE_CLICK_PAYMENT[(int) $this->configuration->get(Config::MOLLIE_ENVIRONMENT) ? 'production' : 'sandbox'],
            'desc' => TagsUtility::ppTags(
                $this->module->l('Read more about [1]Single Click Payments[/1] and how it improves your conversion.', self::FILE_NAME),
                [
                    $this->module->display($this->module->getPathUri(), 'views/templates/admin/mollie_single_click_payment_info.tpl'),
                ]
            ),
            'is_bool' => true,
            'values' => [
                [
                    'id' => 'active_on',
                    'value' => true,
                    'label' => $this->module->l('Enabled', self::FILE_NAME),
                ],
                [
                    'id' => 'active_off',
                    'value' => false,
                    'label' => $this->module->l('Disabled', self::FILE_NAME),
                ],
            ],
        ];

        $input[] = [
            'type' => 'mollie-h2',
            'tab' => $generalSettings,
            'name' => '',
            'title' => $this->module->l('Payment methods', self::FILE_NAME),
        ];

        $molliePaymentMethods = $this->apiService->getMethodsForConfig($this->module->getApiClient());

        if (empty($molliePaymentMethods)) {
            $input[] = [
                'type' => 'mollie-payment-empty-alert',
                'tab' => $generalSettings,
                'name' => '',
            ];
        }

        $dateStamp = Mollie\Utility\TimeUtility::getCurrentTimeStamp();
        $input[] = [
            'type' => 'mollie-methods',
            'name' => Config::METHODS_CONFIG,
            'paymentMethods' => $molliePaymentMethods,
            'countries' => $this->countryService->getActiveCountriesList(),
            'taxRulesGroups' => $this->taxRulesGroupRepository->getTaxRulesGroups($this->context->getShopId()),
            'tab' => $generalSettings,
            'onlyOrderMethods' => Config::ORDER_API_ONLY_METHODS,
            'onlyPaymentsMethods' => Config::PAYMENT_API_ONLY_METHODS,
            'displayErrors' => $this->configuration->get(Config::MOLLIE_DISPLAY_ERRORS),
            'methodDescription' => TagsUtility::ppTags(
                $this->module->l('[1]Read more[/1] about the differences between Payments and Orders API.', self::FILE_NAME),
                [
                    $this->module->display($this->module->getPathUri(), 'views/templates/admin/mollie_method_info.tpl'),
                ]
            ),
            'showCustomLogo' => $this->configuration->get(Config::MOLLIE_SHOW_CUSTOM_LOGO),
            'customLogoUrl' => $this->creditCardLogoProvider->getLogoPathUri() . "?{$dateStamp}",
            'customLogoExist' => $this->creditCardLogoProvider->logoExists(),
            'voucherCategory' => $this->configuration->get(Config::MOLLIE_VOUCHER_CATEGORY),
            'applePayDirectProduct' => (int) $this->configuration->get(Config::MOLLIE_APPLE_PAY_DIRECT_PRODUCT),
            'applePayDirectCart' => (int) $this->configuration->get(Config::MOLLIE_APPLE_PAY_DIRECT_CART),
            'applePayDirectStyle' => (int) $this->configuration->get(Config::MOLLIE_APPLE_PAY_DIRECT_STYLE),
            'isBancontactQrCodeEnabled' => (int) $this->configuration->get(Config::MOLLIE_BANCONTACT_QR_CODE_ENABLED),
            'isLive' => (int) $this->configuration->get(Config::MOLLIE_ENVIRONMENT),
            'bancontactQRCodeDescription' => TagsUtility::ppTags(
                $this->module->l('Only available with your Live API key and Payments API. [1]Learn more[/1] about QR Codes.', self::FILE_NAME),
                [
                    $this->module->display($this->module->getPathUri(), 'views/templates/admin/mollie_bancontact_qr_code_info.tpl'),
                ]
            ),
            'applePayButtonBlack' => $this->module->getPathUri() . 'views/img/applePayButtons/ApplePay_black_yes.png',
            'applePayButtonOutline' => $this->module->getPathUri() . 'views/img/applePayButtons/ApplePay_outline_yes.png',
            'applePayButtonWhite' => $this->module->getPathUri() . 'views/img/applePayButtons/ApplePay_white_yes.png',
        ];

        return $input;
    }

    protected function getAdvancedSettingsSection()
    {
        $advancedSettings = 'advanced_settings';
        $input = [];
        $orderStatuses = [];
        $orderStatuses = array_merge($orderStatuses, OrderState::getOrderStates($this->lang->getDefaultLanguageId()));
        $input[] = [
            'type' => 'select',
            'label' => $this->module->l('Use selected locale in webshop', self::FILE_NAME),
            'tab' => $advancedSettings,
            'desc' => TagsUtility::ppTags(
                $this->module->l('Activate to use your shop\'s [1]locale[/1]. Otherwise, your shop uses the browser\'s locale. ', self::FILE_NAME),
                [$this->module->display($this->module->getPathUri(), 'views/templates/admin/locale_wiki.tpl')]
            ),
            'name' => Config::MOLLIE_PAYMENTSCREEN_LOCALE,
            'options' => [
                'query' => [
                    [
                        'id' => Config::PAYMENTSCREEN_LOCALE_SEND_WEBSITE_LOCALE,
                        'name' => $this->module->l('Use webshop locale', self::FILE_NAME),
                    ],
                    [
                        'id' => Config::PAYMENTSCREEN_LOCALE_BROWSER_LOCALE,
                        'name' => $this->module->l('Use browser locale', self::FILE_NAME),
                    ],
                ],
                'id' => 'id',
                'name' => 'name',
            ],
        ];

        $input[] = [
            'type' => 'select',
            'label' => $this->module->l('Send order confirmation email', self::FILE_NAME),
            'tab' => $advancedSettings,
            'name' => Config::MOLLIE_SEND_ORDER_CONFIRMATION,
            'options' => [
                'query' => [
                    [
                        'id' => Config::ORDER_CONF_MAIL_SEND_ON_PAID,
                        'name' => $this->module->l('When the order is paid', self::FILE_NAME),
                    ],
                    [
                        'id' => Config::ORDER_CONF_MAIL_SEND_ON_NEVER,
                        'name' => $this->module->l('Never', self::FILE_NAME),
                    ],
                ],
                'id' => 'id',
                'name' => 'name',
            ],
        ];

        $input[] = [
            'type' => 'select',
            'label' => $this->module->l('Select when to create the Order invoice', self::FILE_NAME),
            'desc' => $this->module->display($this->module->getPathUri(), 'views/templates/admin/invoice_description.tpl'),
            'tab' => $advancedSettings,
            'name' => Config::MOLLIE_AUTHORIZABLE_PAYMENT_INVOICE_ON_STATUS,
            'options' => [
                'query' => [
                    [
                        'id' => Config::MOLLIE_AUTHORIZABLE_PAYMENT_STATUS_DEFAULT,
                        'name' => $this->module->l('Default', self::FILE_NAME),
                    ],
                    [
                        'id' => Config::MOLLIE_AUTHORIZABLE_PAYMENT_STATUS_AUTHORIZED,
                        'name' => $this->module->l('Authorised', self::FILE_NAME),
                    ],
                    [
                        'id' => Config::MOLLIE_AUTHORIZABLE_PAYMENT_STATUS_SHIPPED,
                        'name' => $this->module->l('Shipped', self::FILE_NAME),
                    ],
                ],
                'id' => 'id',
                'name' => 'name',
            ],
        ];

        $messageStatus = $this->module->l('Status for %s payments', self::FILE_NAME);
        $descriptionStatus = $this->module->l('`%s` payments get `%s` status', self::FILE_NAME);
        $messageMail = $this->module->l('Send email when %s', self::FILE_NAME);
        $descriptionMail = $this->module->l('Send email when transaction status becomes %s?, self::FILE_NAME', self::FILE_NAME);
        $allStatuses = OrderState::getOrderStates($this->lang->getDefaultLanguageId());
        $allStatusesWithSkipOption = array_merge([['id_order_state' => 0, 'name' => $this->module->l('Skip this status', self::FILE_NAME), 'color' => '#565656']], $allStatuses);

        $statusOptions = [
            Config::MOLLIE_AWAITING_PAYMENT,
            PaymentStatus::STATUS_OPEN,
            PaymentStatus::STATUS_PAID,
            OrderStatus::STATUS_COMPLETED,
            PaymentStatus::STATUS_AUTHORIZED,
            PaymentStatus::STATUS_CANCELED,
            PaymentStatus::STATUS_EXPIRED,
            RefundStatus::STATUS_REFUNDED,
            Config::PARTIAL_REFUND_CODE,
            OrderStatus::STATUS_SHIPPING,
            Config::MOLLIE_CHARGEBACK,
        ];

        $statuses = [];
        foreach (Config::getStatuses() as $name => $val) {
            if (PaymentStatus::STATUS_AUTHORIZED === $name) {
                continue;
            }

            if (!in_array($name, $statusOptions)) {
                continue;
            }
            $val = (int) $val;
            if ($val) {
                $orderStatus = new OrderState($val);
                $statusName = $orderStatus->getFieldByLang('name', $this->lang->getDefaultLanguageId());
                $desc = Tools::strtolower(
                    sprintf(
                        $descriptionStatus,
                        $this->module->lang($name),
                        $statusName
                    )
                );
            } else {
                $desc = sprintf($this->module->l('`%s` payments don\'t get a status', self::FILE_NAME), $this->module->lang($name));
            }
            $statuses[] = [
                'name' => $name,
                'key' => @constant('Mollie\Config\Config::MOLLIE_STATUS_' . Tools::strtoupper($name)),
                'value' => $val,
                'description' => $desc,
                'message' => sprintf($messageStatus, $this->module->lang($name)),
                'key_mail' => @constant('Mollie\Config\Config::MOLLIE_MAIL_WHEN_' . Tools::strtoupper($name)),
                'value_mail' => $this->configuration->get('MOLLIE_MAIL_WHEN_' . Tools::strtoupper($name)),
                'description_mail' => sprintf($descriptionMail, $this->module->lang($name)),
                'message_mail' => sprintf($messageMail, $this->module->lang($name)),
            ];
        }
        $input[] = [
            'type' => 'mollie-h2',
            'name' => '',
            'tab' => $advancedSettings,
            'title' => $this->module->l('Order statuses', self::FILE_NAME),
        ];

        foreach (array_filter($statuses, function ($status) use ($statusOptions) {
            return in_array($status['name'], $statusOptions);
        }) as $status) {
            if (!in_array($status['name'], [Config::PARTIAL_REFUND_CODE, Config::MOLLIE_AWAITING_PAYMENT, PaymentStatus::STATUS_OPEN])) {
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
                            'label' => $this->module->l('Enabled', self::FILE_NAME),
                        ],
                        [
                            'id' => 'active_off',
                            'value' => false,
                            'label' => $this->module->l('Disabled', self::FILE_NAME),
                        ],
                    ],
                ];
            }

            $isStatusAwaiting = Config::MOLLIE_AWAITING_PAYMENT === $status['name'];
            $isStatusOpen = Config::MOLLIE_OPEN_PAYMENT === $status['name'];

            $input[] = [
                'type' => 'select',
                'label' => $status['message'],
                'tab' => $advancedSettings,
                'desc' => $status['description'],
                'name' => $status['key'],
                'options' => [
                    'query' => $isStatusAwaiting || $isStatusOpen ? $allStatuses : $allStatusesWithSkipOption,
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
                'title' => $this->module->l('Visual settings', self::FILE_NAME),
            ],
            [
                'type' => 'select',
                'label' => $this->module->l('Images', self::FILE_NAME),
                'tab' => $advancedSettings,
                'desc' => $this->module->l('Show big, normal, or no payment method logos on checkout.', self::FILE_NAME),
                'name' => Config::MOLLIE_IMAGES,
                'options' => [
                    'query' => [
                        [
                            'id' => Config::LOGOS_HIDE,
                            'name' => $this->module->l('Hide', self::FILE_NAME),
                        ],
                        [
                            'id' => Config::LOGOS_NORMAL,
                            'name' => $this->module->l('Normal', self::FILE_NAME),
                        ],
                        [
                            'id' => Config::LOGOS_BIG,
                            'name' => $this->module->l('Big', self::FILE_NAME),
                        ],
                    ],
                    'id' => 'id',
                    'name' => 'name',
                ],
            ],
            [
                'type' => 'text',
                'label' => $this->module->l('CSS file', self::FILE_NAME),
                'tab' => $advancedSettings,
                'desc' => TagsUtility::ppTags(
                    $this->module->l('Leave empty for the default stylesheet. Include the file path when applying custom CSS. You can use [1]{BASE}[/1], [1]{THEME}[/1], [1]{CSS}[/1], [1]{MOBILE}[/1], [1]{MOBILE_CSS}[/1], and [1]{OVERRIDE}[/1] for easy folder mapping.', self::FILE_NAME),
                    [$this->module->display($this->module->getPathUri(), 'views/templates/front/kbd.tpl')]
                ),
                'name' => Config::MOLLIE_CSS,
                'class' => 'long-text',
            ],
        ]);
        $input[] = [
            'type' => 'mollie-carriers',
            'label' => $this->module->l('Shipment information', self::FILE_NAME),
            'tab' => $advancedSettings,
            'name' => Config::MOLLIE_TRACKING_URLS,
            'depends' => Config::MOLLIE_API,
            'depends_value' => Config::MOLLIE_ORDERS_API,
            'carriers' => $this->carrierInformationService->getAllCarriersInformation($this->lang->getDefaultLanguageId()),
        ];
        $input[] = [
            'type' => 'mollie-carrier-switch',
            'label' => $this->module->l('Automatically ship on marked statuses', self::FILE_NAME),
            'tab' => $advancedSettings,
            'name' => Config::MOLLIE_AUTO_SHIP_MAIN,
            'desc' => $this->module->l('Enable to automatically send shipment information when an order gets a marked status.', self::FILE_NAME),
            'is_bool' => true,
            'values' => [
                [
                    'id' => 'active_on',
                    'value' => true,
                    'label' => $this->module->l('Enabled', self::FILE_NAME),
                ],
                [
                    'id' => 'active_off',
                    'value' => false,
                    'label' => $this->module->l('Disabled', self::FILE_NAME),
                ],
            ],
            'depends' => Config::MOLLIE_API,
            'depends_value' => Config::MOLLIE_ORDERS_API,
        ];
        $input[] = [
            'type' => 'checkbox',
            'label' => $this->module->l('Automatically ship when one of these statuses is reached', self::FILE_NAME),
            'tab' => $advancedSettings,
            'desc' => $this->module->l('If an order reaches one of these statuses, the module automatically sends shipment information', self::FILE_NAME),
            'name' => Config::MOLLIE_AUTO_SHIP_STATUSES,
            'multiple' => true,
            'values' => [
                'query' => $orderStatuses,
                'id' => 'id_order_state',
                'name' => 'name',
            ],
            'expand' => (count($orderStatuses) > 10) ? [
                'print_total' => count($orderStatuses),
                'default' => 'show',
                'show' => ['text' => $this->module->l('Show', self::FILE_NAME), 'icon' => 'plus-sign-alt'],
                'hide' => ['text' => $this->module->l('Hide', self::FILE_NAME), 'icon' => 'minus-sign-alt'],
            ] : null,
            'depends' => Config::MOLLIE_API,
            'depends_value' => Config::MOLLIE_ORDERS_API,
        ];
        $orderStatuses = [
            [
                'name' => $this->module->l('Disable this status', self::FILE_NAME),
                'id_order_state' => '0',
            ],
        ];
        $orderStatuses = array_merge($orderStatuses, OrderState::getOrderStates($this->lang->getDefaultLanguageId()));
        $orderStatusesCount = count($orderStatuses);
        for ($i = 0; $i < $orderStatusesCount; ++$i) {
            $orderStatuses[$i]['name'] = $orderStatuses[$i]['id_order_state'] . ' - ' . $orderStatuses[$i]['name'];
        }

//        AssortUtility::aasort($orderStatuses, 'id_order_state');

        $this->smarty->assign([
            'logs' => $this->link->getAdminLink('AdminLogs'),
        ]);
        $input = array_merge(
            $input,
            [
                [
                    'type' => 'mollie-h2',
                    'name' => '',
                    'title' => $this->module->l('Debug level', self::FILE_NAME),
                    'tab' => $advancedSettings,
                ],
                [
                    'type' => 'switch',
                    'label' => $this->module->l('Display errors', self::FILE_NAME),
                    'tab' => $advancedSettings,
                    'name' => Config::MOLLIE_DISPLAY_ERRORS,
                    'desc' => $this->module->l('Enable to display full error messages in the webshop. Only use this for debugging.', self::FILE_NAME),
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'active_on',
                            'value' => true,
                            'label' => $this->module->l('Enabled', self::FILE_NAME),
                        ],
                        [
                            'id' => 'active_off',
                            'value' => false,
                            'label' => $this->module->l('Disabled', self::FILE_NAME),
                        ],
                    ],
                ],
                [
                    'type' => 'select',
                    'label' => $this->module->l('Log level', self::FILE_NAME),
                    'tab' => $advancedSettings,
                    'desc' => TagsUtility::ppTags(
                        $this->module->l('Recommended level: Errors. Set to Everything to monitor incoming webhook requests. [1]View logs.[/1]', self::FILE_NAME),
                        [
                            $this->module->display($this->module->getPathUri(), 'views/templates/admin/view_logs.tpl'),
                        ]
                    ),
                    'name' => Config::MOLLIE_DEBUG_LOG,
                    'options' => [
                        'query' => [
                            [
                                'id' => Config::DEBUG_LOG_NONE,
                                'name' => $this->module->l('Nothing', self::FILE_NAME),
                            ],
                            [
                                'id' => Config::DEBUG_LOG_ERRORS,
                                'name' => $this->module->l('Errors', self::FILE_NAME),
                            ],
                            [
                                'id' => Config::DEBUG_LOG_ALL,
                                'name' => $this->module->l('Everything', self::FILE_NAME),
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

    private function getSettingTabs($isApiKeyProvided)
    {
        $tabs = [
            'general_settings' => $this->module->l('General settings', self::FILE_NAME),
        ];

        if ($isApiKeyProvided) {
            $tabs['advanced_settings'] = $this->module->l('Advanced settings', self::FILE_NAME);
        }

        return $tabs;
    }
}
