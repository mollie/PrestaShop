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

namespace Mollie\Builder;

use _PhpScoper5eddef0da618a\Mollie\Api\Types\OrderStatus;
use _PhpScoper5eddef0da618a\Mollie\Api\Types\PaymentMethod;
use _PhpScoper5eddef0da618a\Mollie\Api\Types\PaymentStatus;
use _PhpScoper5eddef0da618a\Mollie\Api\Types\RefundStatus;
use Configuration;
use HelperForm;
use Module;
use Mollie;
use Mollie\Config\Config;
use Mollie\Provider\CreditCardLogoProvider;
use Mollie\Provider\CustomLogoProviderInterface;
use Mollie\Repository\CountryRepository;
use Mollie\Service\ApiService;
use Mollie\Service\ConfigFieldService;
use Mollie\Service\CountryService;
use Mollie\Service\MolCarrierInformationService;
use Mollie\Utility\AssortUtility;
use Mollie\Utility\EnvironmentUtility;
use Mollie\Utility\TagsUtility;
use OrderState;
use Smarty;
use Tools;

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

    private $lang;

    /**
     * @var Smarty
     */
    private $smarty;

    private $link;

    /**
     * @var CountryRepository
     */
    private $countryRepository;

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

    public function __construct(
        Mollie $module,
        ApiService $apiService,
        CountryService $countryService,
        CountryRepository $countryRepository,
        ConfigFieldService $configFieldService,
        MolCarrierInformationService $carrierInformationService,
        $lang,
        Smarty $smarty,
        $link,
        CustomLogoProviderInterface $creditCardLogoProvider
    ) {
        $this->module = $module;
        $this->apiService = $apiService;
        $this->countryService = $countryService;
        $this->lang = $lang;
        $this->smarty = $smarty;
        $this->link = $link;
        $this->countryRepository = $countryRepository;
        $this->configFieldService = $configFieldService;
        $this->carrierInformationService = $carrierInformationService;
        $this->creditCardLogoProvider = $creditCardLogoProvider;
    }

    public function buildSettingsForm()
    {
        $isApiKeyProvided = EnvironmentUtility::getApiKey();

        $inputs = $this->getAccountSettingsSection($isApiKeyProvided);

        if ($isApiKeyProvided) {
            $inputs = array_merge($inputs, $this->getAdvancedSettingsSection());
        }

        $fields = [
            'form' => [
                'tabs' => $this->getSettingTabs($isApiKeyProvided),
                'input' => $inputs,
                'submit' => [
                    'title' => $this->module->l('Save'),
                    'class' => 'btn btn-default pull-right',
                ],
            ],
        ];

        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->module->getTable();
        $helper->module = $this->module;
        $helper->default_form_language = $this->module->getContext()->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->module->getIdentifier();
        $helper->submit_action = 'submitmollie';
        $helper->currentIndex = $this->module->getContext()->link->getAdminLink('AdminModules', false)
            . "&configure={$this->module->name}&tab_module={$this->module->tab}&module_name={$this->module->name}";
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
            ]
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
                    $this->module->l('You can find your API key in your [1]Mollie Profile[/1]; it starts with test or live.', self::FILE_NAME),
                    [$this->module->display($this->module->getPathUri(), 'views/templates/admin/profile.tpl')]
                ),
                'name' => Config::MOLLIE_API_KEY_TEST,
                'required' => true,
                'class' => 'fixed-width-xxl',
                'form_group_class' => 'js-test-api-group'
            ];
            $input[] = [
                'type' => 'mollie-password',
                'label' => $this->module->l('API Key Live', self::FILE_NAME),
                'tab' => $generalSettings,
                'name' => Config::MOLLIE_API_KEY,
                'required' => true,
                'class' => 'fixed-width-xxl',
                'form_group_class' => 'js-live-api-group'
            ];
            $input[] = [
                'type' => 'mollie-password',
                'label' => $this->module->l('Profile ID', self::FILE_NAME),
                'tab' => $generalSettings,
                'desc' => TagsUtility::ppTags(
                    $this->module->l('You can find your API key in your [1]Mollie Profile[/1].', self::FILE_NAME),
                    [$this->module->display($this->module->getPathUri(), 'views/templates/admin/profile.tpl')]
                ),
                'name' => Config::MOLLIE_PROFILE_ID,
                'required' => true,
                'class' => 'fixed-width-xxl',
                'form_group_class' => 'js-api-profile-id'
            ];
            $input[] = [
                'type' => 'mollie-button',
                'label' => '',
                'tab' => $generalSettings,
                'name' => Config::MOLLIE_API_KEY_TESTING_BUTTON,
                'text' => $this->module->l('Test ApiKey', self::FILE_NAME),
                'class' => 'js-test-api-keys',
                'form_group_class' => 'js-api-key-test'
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
                    $this->module->l('You can find your API key in your [1]Mollie Profile[/1]; it starts with test or live.', self::FILE_NAME),
                    [$this->module->display($this->module->getPathUri(), 'views/templates/admin/profile.tpl')]
                ),
                'name' => Config::MOLLIE_API_KEY_TEST,
                'required' => true,
                'class' => 'fixed-width-xxl',
                'form_group_class' => 'js-test-api-group'
            ];
            $input[] = [
                'type' => 'mollie-password',
                'label' => $this->module->l('API Key Live', self::FILE_NAME),
                'tab' => $generalSettings,
                'name' => Config::MOLLIE_API_KEY,
                'required' => true,
                'class' => 'fixed-width-xxl',
                'form_group_class' => 'js-live-api-group'
            ];
        }
        if (!$isApiKeyProvided) {
            return $input;
        }
        $input[] = [
            'type' => 'switch',
            'label' => $this->module->l('Use Mollie Components for CreditCards', self::FILE_NAME),
            'tab' => $generalSettings,
            'name' => Config::MOLLIE_IFRAME,
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
            'label' => $this->module->l('Use Single Click Payments for CreditCards', self::FILE_NAME),
            'tab' => $generalSettings,
            'name' => Config::MOLLIE_SINGLE_CLICK_PAYMENT,
            'desc' => TagsUtility::ppTags(
                $this->module->l('Read more about [1]Single Click Payments[/1] and how it improves your conversion.', self::FILE_NAME),
                [
                    $this->module->display($this->module->getPathUri(), 'views/templates/admin/mollie_single_click_payment_info.tpl')
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

        $input = array_merge($input, [
                [
                    'type' => 'select',
                    'label' => $this->module->l('Issuer list', self::FILE_NAME),
                    'tab' => $generalSettings,
                    'desc' => $this->module->l('Some payment methods (eg. iDEAL) have an issuer list. This setting specifies where it is shown.', self::FILE_NAME),
                    'name' => Config::MOLLIE_ISSUERS,
                    'options' => [
                        'query' => [
                            [
                                'id' => Config::ISSUERS_ON_CLICK,
                                'name' => $this->module->l('On click', self::FILE_NAME),
                            ],
                            [
                                'id' => Config::ISSUERS_PAYMENT_PAGE,
                                'name' => $this->module->l('Payment page', self::FILE_NAME),
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
            'title' => $this->module->l('Payment methods', self::FILE_NAME),
        ];

        $dateStamp = time();
        $input[] = [
            'type' => 'mollie-methods',
            'name' => Config::METHODS_CONFIG,
            'paymentMethods' => $this->apiService->getMethodsForConfig($this->module->api, $this->module->getPathUri()),
            'countries' => $this->countryService->getActiveCountriesList(),
            'tab' => $generalSettings,
            'klarnaPayments' => [
                PaymentMethod::KLARNA_PAY_LATER,
                PaymentMethod::KLARNA_SLICE_IT,
            ],
            'displayErrors' => Configuration::get(Config::MOLLIE_DISPLAY_ERRORS),
            'methodDescription' => TagsUtility::ppTags(
                $this->module->l('Click [1]here[/1] to read more about the differences between the Payment and Orders API.', self::FILE_NAME),
                [
                    $this->module->display($this->module->getPathUri(), 'views/templates/admin/mollie_method_info.tpl')
                ]
            ),
            'showCustomLogo' => Configuration::get(Config::MOLLIE_SHOW_CUSTOM_LOGO),
            'customLogoUrl' => $this->creditCardLogoProvider->getLogoPathUri() . "?{$dateStamp}",
            'customLogoExist' => $this->creditCardLogoProvider->logoExists(),
        ];

        return $input;
    }

    protected function getAdvancedSettingsSection()
    {
        $advancedSettings = 'advanced_settings';
        $input = [];
        $orderStatuses = [];
        $orderStatuses = array_merge($orderStatuses, OrderState::getOrderStates($this->lang->id));
        $input[] = [
            'type' => 'select',
            'label' => $this->module->l('Push Locale to Payment Screen', self::FILE_NAME),
            'tab' => $advancedSettings,
            'desc' => TagsUtility::ppTags(
                $this->module->l('When activated, Mollie will use your webshop\'s [1]Locale[/1]. If not, the browser\'s locale will be used.', self::FILE_NAME),
                [$this->module->display($this->module->getPathUri(), 'views/templates/admin/locale_wiki.tpl')]
            ),
            'name' => Config::MOLLIE_PAYMENTSCREEN_LOCALE,
            'options' => [
                'query' => [
                    [
                        'id' => Config::PAYMENTSCREEN_LOCALE_SEND_WEBSITE_LOCALE,
                        'name' => $this->module->l('Yes, push webshop Locale', self::FILE_NAME),
                    ],
                    [
                        'id' => Config::PAYMENTSCREEN_LOCALE_BROWSER_LOCALE,
                        'name' => $this->module->l('No, use browser\'s Locale', self::FILE_NAME),
                    ],

                ],
                'id' => 'id',
                'name' => 'name',
            ],
        ];

        if (Config::isVersion17()) {
            $input[] = [
                'type' => 'select',
                'label' => $this->module->l('Send order confirmation email', self::FILE_NAME),
                'tab' => $advancedSettings,
                'name' => Config::MOLLIE_SEND_ORDER_CONFIRMATION,
                'options' => [
                    'query' => [
                        [
                            'id' => Config::ORDER_CONF_MAIL_SEND_ON_CREATION,
                            'name' => $this->module->l('When Order is created', self::FILE_NAME),
                        ],
                        [
                            'id' => Config::ORDER_CONF_MAIL_SEND_ON_PAID,
                            'name' => $this->module->l('When Order is Paid', self::FILE_NAME),
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

            if (Module::isEnabled(Config::EMAIL_ALERTS_MODULE_NAME)) {
                $input[] = [
                    'type' => 'select',
                    'label' => $this->module->l('Send new order email to merchant', self::FILE_NAME),
                    'tab' => $advancedSettings,
                    'name' => Config::MOLLIE_SEND_NEW_ORDER,
                    'desc' => TagsUtility::ppTags(
                        $this->module->l('Change when \'new_order\' email to merchant is sent (When using PrestaShop Mail Alerts module)', self::FILE_NAME),
                        [$this->module->display($this->module->getPathUri(), 'views/templates/admin/locale_wiki.tpl')]
                    ),
                    'options' => [
                        'query' => [
                            [
                                'id' => Config::NEW_ORDER_MAIL_SEND_ON_CREATION,
                                'name' => $this->module->l('When Order is created', self::FILE_NAME),
                            ],
                            [
                                'id' => Config::NEW_ORDER_MAIL_SEND_ON_PAID,
                                'name' => $this->module->l('When Order is Paid', self::FILE_NAME),
                            ],
                            [
                                'id' => Config::NEW_ORDER_MAIL_SEND_ON_NEVER,
                                'name' => $this->module->l('Never', self::FILE_NAME),
                            ],
                        ],
                        'id' => 'id',
                        'name' => 'name',
                    ],
                ];
            }
        }

        $messageStatus = $this->module->l('Status for %s payments', self::FILE_NAME);
        $descriptionStatus = $this->module->l('`%s` payments get status `%s`', self::FILE_NAME);
        $messageMail = $this->module->l('Send mails when %s', self::FILE_NAME);
        $descriptionMail = $this->module->l('Send mails when transaction status becomes %s?, self::FILE_NAME', self::FILE_NAME);
        $allStatuses = array_merge([['id_order_state' => 0, 'name' => $this->module->l('Skip this status', self::FILE_NAME), 'color' => '#565656']], OrderState::getOrderStates($this->lang->id));
        $statuses = [];
        foreach (Config::getStatuses() as $name => $val) {
            if ($name === PaymentStatus::STATUS_AUTHORIZED) {
                continue;
            }

            $val = (int) $val;
            if ($val) {
                $orderStatus = new OrderState($val);
                $statusName = $orderStatus->getFieldByLang('name', $this->lang->id);
                $desc = Tools::strtolower(
                    sprintf(
                        $descriptionStatus,
                        $this->module->lang($name),
                        $statusName
                    )
                );
            } else {
                $desc = sprintf($this->module->l('`%s` payments do not get a status', self::FILE_NAME), $this->module->lang($name));
            }
            $statuses[] = [
                'name' => $name,
                'key' => @constant('Mollie\Config\Config::MOLLIE_STATUS_' . Tools::strtoupper($name)),
                'value' => $val,
                'description' => $desc,
                'message' => sprintf($messageStatus, $this->module->lang($name)),
                'key_mail' => @constant('Mollie\Config\Config::MOLLIE_MAIL_WHEN_' . Tools::strtoupper($name)),
                'value_mail' => Configuration::get('MOLLIE_MAIL_WHEN_' . Tools::strtoupper($name)),
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

        foreach (array_filter($statuses, function ($status) {
            return in_array($status['name'], [
                PaymentStatus::STATUS_PAID,
                OrderStatus::STATUS_COMPLETED,
                PaymentStatus::STATUS_AUTHORIZED,
                PaymentStatus::STATUS_CANCELED,
                PaymentStatus::STATUS_EXPIRED,
                RefundStatus::STATUS_REFUNDED,
                PaymentStatus::STATUS_OPEN,
                Config::PARTIAL_REFUND_CODE,
                OrderStatus::STATUS_SHIPPING,
            ]);
        }) as $status) {
            if (!in_array($status['name'], [Config::PARTIAL_REFUND_CODE])) {
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
                'title' => $this->module->l('Visual Settings', self::FILE_NAME),
            ],
            [
                'type' => 'select',
                'label' => $this->module->l('Images', self::FILE_NAME),
                'tab' => $advancedSettings,
                'desc' => $this->module->l('Show big, normal or no payment method logos on checkout.', self::FILE_NAME),
                'name' => Config::MOLLIE_IMAGES,
                'options' => [
                    'query' => [
                        [
                            'id' => Config::LOGOS_HIDE,
                            'name' => $this->module->l('hide', self::FILE_NAME),
                        ],
                        [
                            'id' => Config::LOGOS_NORMAL,
                            'name' => $this->module->l('normal', self::FILE_NAME),
                        ],
                        [
                            'id' => Config::LOGOS_BIG,
                            'name' => $this->module->l('big', self::FILE_NAME),
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
                    $this->module->l('Leave empty for default stylesheet. Should include file path when set. Hint: You can use [1]{BASE}[/1], [1]{THEME}[/1], [1]{CSS}[/1], [1]{MOBILE}[/1], [1]{MOBILE_CSS}[/1] and [1]{OVERRIDE}[/1] for easy folder mapping.', self::FILE_NAME),
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
            'carriers' => $this->carrierInformationService->getAllCarriersInformation($this->lang->id)
        ];
        $input[] = [
            'type' => 'mollie-carrier-switch',
            'label' => $this->module->l('Automatically ship on marked statuses', self::FILE_NAME),
            'tab' => $advancedSettings,
            'name' => Config::MOLLIE_AUTO_SHIP_MAIN,
            'desc' => $this->module->l('Enabling this feature will automatically send shipment information when an order gets marked status.', self::FILE_NAME),
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
            'desc' =>
                $this->module->l('If an order reaches one of these statuses the module will automatically send shipment information', self::FILE_NAME),
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
        $orderStatuses = array_merge($orderStatuses, OrderState::getOrderStates($this->lang->id));
        $orderStatusesCount = count($orderStatuses);
        for ($i = 0; $i < $orderStatusesCount; $i++) {
            $orderStatuses[$i]['name'] = $orderStatuses[$i]['id_order_state'] . ' - ' . $orderStatuses[$i]['name'];
        }

        AssortUtility::aasort($orderStatuses, 'id_order_state');

        $this->smarty->assign([
            'logs' => $this->link->getAdminLink('AdminLogs')
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
                    'desc' => $this->module->l('Enabling this feature will display error messages (if any) on the front page. Use for debug purposes only!', self::FILE_NAME),
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
                            $this->module->display($this->module->getPathUri(), 'views/templates/admin/view_logs.tpl')
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
