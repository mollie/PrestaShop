<?php
/**
 * Copyright (c) 2012-2019, Mollie B.V.
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
if (!include_once(dirname(__FILE__).'/vendor/autoload.php')) {
    return;
}

// PSR-7 standard autoload, if failure, skip this module
if (!function_exists('\\Hough\\Psr7\\str')) {
    if (!include_once(dirname(__FILE__).'/vendor/ehough/psr7/src/functions.php')) {
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
    /** @var \MollieModule\Mollie\Api\MollieApiClient|null */
    public $api = null;
    /** @var array $statuses */
    public $statuses = array();
    /** @var array $lang */
    public $lang = array();
    /** @var string $currentOrderReference */
    public $currentOrderReference;
    /** @var string $selectedApi */
    public static $selectedApi;
    /** @var bool $cacheCleared Indicates whether the Smarty cache has been cleared during updates */
    public static $cacheCleared;
    /**
     * Currency restrictions per payment method
     *
     * @var array
     */
    public static $methodCurrencies = array(
        'banktransfer'    => array('eur'),
        'belfius'         => array('eur'),
        'bitcoin'         => array('eur'),
        'cartasi'         => array('eur'),
        'cartesbancaires' => array('eur'),
        'creditcard'      => array('aud', 'bgn', 'cad', 'chf', 'czk', 'dkk', 'eur', 'gbp', 'hkd', 'hrk', 'huf', 'ils', 'isk', 'jpy', 'pln', 'ron', 'sek', 'usd'),
        'directdebit'     => array('eur'),
        'eps'             => array('eur'),
        'giftcard'        => array('eur'),
        'giropay'         => array('eur'),
        'ideal'           => array('eur'),
        'inghomepay'      => array('eur'),
        'kbc'             => array('eur'),
        'bancontact'      => array('eur'),
        'paypal'          => array('aud', 'brl', 'cad', 'chf', 'czk', 'dkk', 'eur', 'gbp', 'hkd', 'huf', 'ils', 'jpy', 'mxn', 'myr', 'nok', 'nzd', 'php', 'pln', 'rub', 'sek', 'sgd', 'thb', 'twd', 'usd'),
        'paysafecard'     => array('eur'),
        'sofort'          => array('eur'),
        'klarnapaylater'  => array('eur'),
        'klarnasliceit'   => array('eur'),
    );
    /**
     * Default payment method availability
     *
     * empty array is global availability
     *
     * @var array
     *
     * @since 3.3.2
     */
    public static $defaultMethodAvailability = array(
        'creditcard'      => array(),
        'klarnapaylater'  => array('nl', 'de', 'at', 'fi'),
        'klarnasliceit'   => array('de', 'at', 'fi'),
        'ideal'           => array('nl'),
        'bancontact'      => array('be'),
        'paypal'          => array(),
        'giropay'         => array('de'),
        'eps'             => array('at'),
        'belfius'         => array('be'),
        'inghomepay'      => array('be'),
        'kbc'             => array('be'),
        'sofort'          => array('de', 'at', 'ch', 'pl', 'it', 'es', 'be', 'nl'),
        'giftcard'        => array('nl'),
        'bitcoin'         => array(),
        'paysafecard'     => array(),
        'banktransfer'    => array(),
        'cartasi'         => array('it'),
        'cartesbancaires' => array('fr'),
        'directdebit'     => array(
            'fi', 'at', 'pt', 'be', 'bg', 'es', 'hr', 'cy', 'cz', 'dk', 'ee', 'fr', 'gf', 'de', 'gi', 'gr', 'gp', 'gg', 'hu',
            'is', 'ie', 'im', 'it', 'je', 'lv', 'li', 'lt', 'lu', 'pt', 'mt', 'mq', 'yt', 'mc', 'nl', 'no', 'pl', 'pt', 're',
            'ro', 'bl', 'mf', 'pm', 'sm', 'sk', 'sl', 'es', 'se', 'ch', 'gb', 'uk',
        ),
    );

    // The Addons version does not include the GitHub updater
    const ADDONS = false;

    const NOTICE = 1;
    const WARNING = 2;
    const ERROR = 3;
    const CRASH = 4;

    const NAME = 'mollie';

    const PAYMENTSCREEN_LOCALE_BROWSER_LOCALE = 'browser_locale';
    const PAYMENTSCREEN_LOCALE_SEND_WEBSITE_LOCALE = 'website_locale';

    const LOGOS_BIG = 'big';
    const LOGOS_NORMAL = 'normal';
    const LOGOS_HIDE = 'hide';

    const ISSUERS_ON_CLICK = 'on-click';
    const ISSUERS_OWN_PAGE = 'own-page';
    const ISSUERS_PAYMENT_PAGE = 'payment-page';
    const METHODS_CONFIG = 'MOLLIE_METHODS_CONFIG';

    const DEBUG_LOG_NONE = 0;
    const DEBUG_LOG_ERRORS = 1;
    const DEBUG_LOG_ALL = 2;

    const MOLLIE_API_KEY = 'MOLLIE_API_KEY';
    const MOLLIE_DESCRIPTION = 'MOLLIE_DESCRIPTION';
    const MOLLIE_PAYMENTSCREEN_LOCALE = 'MOLLIE_PAYMENTSCREEN_LOCALE';
    const MOLLIE_IMAGES = 'MOLLIE_IMAGES';
    const MOLLIE_ISSUERS = 'MOLLIE_ISSUERS';
    const MOLLIE_CSS = 'MOLLIE_CSS';
    const MOLLIE_DEBUG_LOG = 'MOLLIE_DEBUG_LOG';
    const MOLLIE_QRENABLED = 'MOLLIE_QRENABLED';
    const MOLLIE_DISPLAY_ERRORS = 'MOLLIE_DISPLAY_ERRORS';
    const MOLLIE_TRACKING_URLS = 'MOLLIE_TRACKING_URLS';
    const MOLLIE_AUTO_SHIP_MAIN = 'MOLLIE_AS_MAIN';
    const MOLLIE_AUTO_SHIP_STATUSES = 'MOLLIE_AS_STATUSES';
    const MOLLIE_STATUS_OPEN = 'MOLLIE_STATUS_OPEN';
    const MOLLIE_STATUS_PAID = 'MOLLIE_STATUS_PAID';
    const MOLLIE_STATUS_CANCELED = 'MOLLIE_STATUS_CANCELED';
    const MOLLIE_STATUS_EXPIRED = 'MOLLIE_STATUS_EXPIRED';
    const MOLLIE_STATUS_PARTIAL_REFUND = 'MOLLIE_PARTIAL_REFUND';
    const MOLLIE_STATUS_REFUNDED = 'MOLLIE_STATUS_REFUNDED';
    const MOLLIE_MAIL_WHEN_OPEN = 'MOLLIE_MAIL_WHEN_OPEN';
    const MOLLIE_MAIL_WHEN_PAID = 'MOLLIE_MAIL_WHEN_PAID';
    const MOLLIE_MAIL_WHEN_CANCELED = 'MOLLIE_MAIL_WHEN_CANCELED';
    const MOLLIE_MAIL_WHEN_EXPIRED = 'MOLLIE_MAIL_WHEN_EXPIRED';
    const MOLLIE_MAIL_WHEN_REFUNDED = 'MOLLIE_MAIL_WHEN_REFUNDED';
    const PARTIAL_REFUND_CODE = 'partial_refund';

    const MOLLIE_RESELLER_PARTNER_ID = 4602094;
    const MOLLIE_RESELLER_PROFILE_KEY = 'B69C2D66';
    const MOLLIE_RESELLER_APP_SECRET = '49726EB7650EC592F732E7B82A4C1EFD6EE8A10F';

    const MOLLIE_CARRIER_DO_NOT_AUTO_SHIPO = 'do_not_auto_ship';
    const MOLLIE_CARRIER_NO_TRACKING_INFO = 'no_tracking_info';
    const MOLLIE_CARRIER_MODULE = 'module';
    const MOLLIE_CARRIER_CARRIER = 'carrier_url';
    const MOLLIE_CARRIER_CUSTOM = 'custom_url';

    const MOLLIE_API = 'MOLLIE_API';
    const MOLLIE_ORDERS_API = 'orders';
    const MOLLIE_PAYMENTS_API = 'payments';

    const MOLLIE_METHODS_LAST_CHECK = 'MOLLIE_METHOD_CHECK_UPD';
    const MOLLIE_METHODS_CHECK_INTERVAL = 86400; //daily check

    const API_ROUNDING_PRECISION = 2;

    /**
     * Hooks for this module
     *
     * @var array $hooks
     */
    public $hooks = array(
        'displayPayment',
        'displayPaymentEU',
        'paymentOptions',
        'displayAdminOrder',
        'displayHeader',
        'displayBackOfficeHeader',
        'displayOrderConfirmation',
    );

    /** @var array $methods */
    public static $methods = array(
        'banktransfer'    => 'Bank',
        'belfius'         => 'Belfius',
        'bitcoin'         => 'Bitcoin',
        'cartasi'         => 'CartaSi',
        'cartesbancaires' => 'Cartes Bancaires',
        'creditcard'      => 'Credit Card',
        'directdebit'     => 'Direct Debit',
        'eps'             => 'EPS',
        'giftcard'        => 'Giftcard',
        'giropay'         => 'Giropay',
        'ideal'           => 'iDEAL',
        'inghomepay '     => 'ING Homepay',
        'kbc'             => 'KBC',
        'bancontact'      => 'Bancontact',
        'paypal'          => 'PayPal',
        'paysafecard'     => 'Paysafecard',
        'sofort'          => 'Sofort Banking',
        'klarnapaylater'  => 'Pay later.',
        'klarnaspliceit'  => 'Slice it.',
    );

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
        $this->version = '3.3.3';
        $this->author = 'Mollie B.V.';
        $this->need_instance = 1;
        $this->bootstrap = true;
        $this->module_key = 'a48b2f8918358bcbe6436414f48d8915';

        parent::__construct();

        $this->displayName = $this->l('Mollie');
        $this->description = $this->l('Mollie Payments');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall the Mollie Payment Module?');

        $this->controllers = array('payment', 'return', 'webhook', 'qrcode');

        try {
            $this->api = new \MollieModule\Mollie\Api\MollieApiClient();
            if (Configuration::get(static::MOLLIE_API_KEY)) {
                try {
                    $this->api->setApiKey(Configuration::get(static::MOLLIE_API_KEY));
                } catch (\MollieModule\Mollie\Api\Exceptions\ApiException $e) {
                }
            } elseif (!empty($this->context->employee)
                && Tools::getValue('Mollie_Api_Key')
                && $this->context->controller instanceof AdminModulesController
            ) {
                $this->api->setApiKey(Tools::getValue('Mollie_Api_Key'));
            }
            if (defined('_TB_VERSION_')) {
                $this->api->addVersionString('ThirtyBees/'._TB_VERSION_);
                $this->api->addVersionString("MollieThirtyBees/{$this->version}");
            } else {
                $this->api->addVersionString('PrestaShop/'._PS_VERSION_);
                $this->api->addVersionString("MolliePrestaShop/{$this->version}");
            }
        } catch (\MollieModule\Mollie\Api\Exceptions\IncompatiblePlatform $e) {
            Logger::addLog(__METHOD__.' - System incompatible: '.$e->getMessage(), static::CRASH);
        } catch (\MollieModule\Mollie\Api\Exceptions\ApiException $e) {
            $this->warning = $this->l('Payment error:').$e->getMessage();
            Logger::addLog(__METHOD__.' said: '.$this->warning, static::CRASH);
        }

        $this->statuses = array(
            \MollieModule\Mollie\Api\Types\PaymentStatus::STATUS_PAID       => Configuration::get(static::MOLLIE_STATUS_PAID),
            \MollieModule\Mollie\Api\Types\PaymentStatus::STATUS_AUTHORIZED => Configuration::get(static::MOLLIE_STATUS_PAID),
            \MollieModule\Mollie\Api\Types\PaymentStatus::STATUS_CANCELED   => Configuration::get(static::MOLLIE_STATUS_CANCELED),
            \MollieModule\Mollie\Api\Types\PaymentStatus::STATUS_EXPIRED    => Configuration::get(static::MOLLIE_STATUS_EXPIRED),
            \MollieModule\Mollie\Api\Types\RefundStatus::STATUS_REFUNDED    => Configuration::get(static::MOLLIE_STATUS_REFUNDED),
            \MollieModule\Mollie\Api\Types\PaymentStatus::STATUS_OPEN       => Configuration::get(static::MOLLIE_STATUS_OPEN),
            static::PARTIAL_REFUND_CODE                                     => Configuration::get(static::MOLLIE_STATUS_PARTIAL_REFUND),
            'created'                                                       => Configuration::get(static::MOLLIE_STATUS_OPEN),
        );

        // Load all translatable text here so we have a single translation point
        $this->lang = array(
            \MollieModule\Mollie\Api\Types\PaymentStatus::STATUS_PAID                                                                         => $this->l('Paid'),
            \MollieModule\Mollie\Api\Types\PaymentStatus::STATUS_AUTHORIZED                                                                   => $this->l('Authorized'),
            \MollieModule\Mollie\Api\Types\PaymentStatus::STATUS_CANCELED                                                                     => $this->l('Canceled'),
            \MollieModule\Mollie\Api\Types\PaymentStatus::STATUS_EXPIRED                                                                      => $this->l('Expired'),
            \MollieModule\Mollie\Api\Types\RefundStatus::STATUS_REFUNDED                                                                      => $this->l('Refunded'),
            \MollieModule\Mollie\Api\Types\PaymentStatus::STATUS_OPEN                                                                         => $this->l('Bankwire pending'),
            static::PARTIAL_REFUND_CODE                                                                                                       => $this->l('Partially refunded'),
            'created'                                                                                                                         => $this->l('Created'),
            'This payment method is not available.'                                                                                           => $this->l('This payment method is not available.'),
            'Click here to continue'                                                                                                          => $this->l('Click here to continue'),
            'This payment method is only available for Euros.'                                                                                => $this->l('This payment method is only available for Euros.'),
            'There was an error while processing your request: '                                                                              => $this->l('There was an error while processing your request: '),
            'The order with this id does not exist.'                                                                                          => $this->l('The order with this id does not exist.'),
            'We have not received a definite payment status. You will be notified as soon as we receive a confirmation of the bank/merchant.' => $this->l('We have not received a definite payment status. You will be notified as soon as we receive a confirmation of the bank/merchant.'),
            'Unfortunately your payment was expired.'                                                                                         => $this->l('Unfortunately your payment was expired.'),
            'Thank you. Your payment has been received.'                                                                                      => $this->l('Thank you. Your payment has been received.'),
            'The transaction has an unexpected status.'                                                                                       => $this->l('The transaction has an unexpected status.'),
            'You are not authorised to see this page.'                                                                                        => $this->l('You are not authorised to see this page.'),
            'Continue shopping'                                                                                                               => $this->l('Continue shopping'),
            'Welcome back'                                                                                                                    => $this->l('Welcome back'),
            'Select your bank:'                                                                                                               => $this->l('Select your bank:'),
            'OK'                                                                                                                              => $this->l('OK'),
            'Different payment method'                                                                                                        => $this->l('Different payment method'),
            'Pay with %s'                                                                                                                     => $this->l('Pay with %s'),
            'Refund this order'                                                                                                               => $this->l('Refund this order'),
            'Mollie refund'                                                                                                                   => $this->l('Mollie refund'),
            'Refund order #%d through the Mollie API.'                                                                                        => $this->l('Refund order #%d through the Mollie API.'),
            'The order has been refunded!'                                                                                                    => $this->l('The order has been refunded!'),
            'Mollie B.V. will transfer the money back to the customer on the next business day.'                                              => $this->l('Mollie B.V. will transfer the money back to the customer on the next business day.'),
            'Awaiting Mollie payment'                                                                                                         => $this->l('Awaiting Mollie payment'),
            'Mollie partially refunded'                                                                                                       => $this->l('Mollie partially refunded'),
            'iDEAL'                                                                                                                           => $this->l('iDEAL'),
            'CartaSi'                                                                                                                         => $this->l('CartaSi'),
            'Cartes Bancaires'                                                                                                                => $this->l('Cartes Bancaires'),
            'Credit card'                                                                                                                     => $this->l('Credit card'),
            'Bancontact'                                                                                                                      => $this->l('Bancontact'),
            'SOFORT Banking'                                                                                                                  => $this->l('SOFORT Banking'),
            'SEPA Direct Debit'                                                                                                               => $this->l('SEPA Direct Debit'),
            'Belfius Pay Button'                                                                                                              => $this->l('Belfius Pay Button'),
            'Bitcoin'                                                                                                                         => $this->l('Bitcoin'),
            'PODIUM Cadeaukaart'                                                                                                              => $this->l('PODIUM Cadeaukaart'),
            'Gift cards'                                                                                                                      => $this->l('Gift cards'),
            'Bank transfer'                                                                                                                   => $this->l('Bank transfer'),
            'PayPal'                                                                                                                          => $this->l('PayPal'),
            'paysafecard'                                                                                                                     => $this->l('paysafecard'),
            'KBC/CBC Payment Button'                                                                                                          => $this->l('KBC/CBC Payment Button'),
            'ING Home\'Pay'                                                                                                                   => $this->l('ING Home\'Pay'),
            'Giropay'                                                                                                                         => $this->l('Giropay'),
            'eps'                                                                                                                             => $this->l('eps'),
            'Pay later.'                                                                                                                      => $this->l('Pay later.'),
            'Slice it.'                                                                                                                       => $this->l('Slice it.'),
        );

        // Register json Smarty function when missing, happens on older 1.5; some 1.6 versions
        try {
            smartyRegisterFunction(Context::getContext()->smarty, 'modifier', 'json_encode', array('Tools', 'jsonEncode'));
            smartyRegisterFunction(Context::getContext()->smarty, 'modifier', 'json_decode', array('Tools', 'jsonDecode'));
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
                smartyRegisterFunction(Context::getContext()->smarty, 'function', 'displayPrice', array('Tools', 'displayPriceSmarty'));
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

        foreach ($this->hooks as $hook) {
            $this->registerHook($hook);
        }

        if (!$this->partialRefundOrderState()) {
            $this->_errors[] = 'Unable to install Mollie partially refunded order state';

            return false;
        }

        $this->initConfig();
        $this->setDefaultCarrierStatuses();

        include(dirname(__FILE__).'/sql/install.php');

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
        foreach ($this->hooks as $hook) {
            $this->unregisterHook($hook);
        }

        Configuration::deleteByName(static::MOLLIE_API_KEY);
        Configuration::deleteByName(static::MOLLIE_DESCRIPTION);
        Configuration::deleteByName(static::MOLLIE_PAYMENTSCREEN_LOCALE);
        Configuration::deleteByName(static::MOLLIE_IMAGES);
        Configuration::deleteByName(static::MOLLIE_ISSUERS);
        Configuration::deleteByName(static::MOLLIE_CSS);
        Configuration::deleteByName(static::MOLLIE_DEBUG_LOG);
        Configuration::deleteByName(static::MOLLIE_QRENABLED);
        Configuration::deleteByName(static::MOLLIE_DISPLAY_ERRORS);
        Configuration::deleteByName(static::MOLLIE_STATUS_OPEN);
        Configuration::deleteByName(static::MOLLIE_STATUS_PAID);
        Configuration::deleteByName(static::MOLLIE_STATUS_CANCELED);
        Configuration::deleteByName(static::MOLLIE_STATUS_EXPIRED);
        Configuration::deleteByName(static::MOLLIE_STATUS_PARTIAL_REFUND);
        Configuration::deleteByName(static::MOLLIE_STATUS_REFUNDED);
        Configuration::deleteByName(static::MOLLIE_MAIL_WHEN_OPEN);
        Configuration::deleteByName(static::MOLLIE_MAIL_WHEN_PAID);
        Configuration::deleteByName(static::MOLLIE_MAIL_WHEN_CANCELED);
        Configuration::deleteByName(static::MOLLIE_MAIL_WHEN_EXPIRED);
        Configuration::deleteByName(static::MOLLIE_MAIL_WHEN_REFUNDED);

        return parent::uninstall();
    }

    /**
     * @return void
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function reinstall()
    {
        foreach ($this->hooks as $hook) {
            $this->unregisterHook($hook);
            $this->registerHook($hook);
        }

        $this->initConfig();
    }

    /**
     * @return void
     *
     * @throws PrestaShopException
     */
    protected function initConfig()
    {
        Configuration::updateGlobalValue(static::MOLLIE_API_KEY, '');
        Configuration::updateGlobalValue(static::MOLLIE_DESCRIPTION, 'Cart %');
        Configuration::updateGlobalValue(static::MOLLIE_PAYMENTSCREEN_LOCALE, static::PAYMENTSCREEN_LOCALE_BROWSER_LOCALE);
        Configuration::updateGlobalValue(static::MOLLIE_IMAGES, static::LOGOS_NORMAL);
        Configuration::updateGlobalValue(static::MOLLIE_ISSUERS, static::ISSUERS_ON_CLICK);
        Configuration::updateGlobalValue(static::MOLLIE_CSS, '');
        Configuration::updateGlobalValue(static::MOLLIE_DEBUG_LOG, static::DEBUG_LOG_ERRORS);
        Configuration::updateGlobalValue(static::MOLLIE_QRENABLED, false);
        Configuration::updateGlobalValue(static::MOLLIE_DISPLAY_ERRORS, false);
        Configuration::updateGlobalValue(static::MOLLIE_STATUS_OPEN, Configuration::get('PS_OS_BANKWIRE'));
        Configuration::updateGlobalValue(static::MOLLIE_STATUS_PAID, Configuration::get('PS_OS_PAYMENT'));
        Configuration::updateGlobalValue(static::MOLLIE_STATUS_CANCELED, Configuration::get('PS_OS_CANCELED'));
        Configuration::updateGlobalValue(static::MOLLIE_STATUS_EXPIRED, Configuration::get('PS_OS_CANCELED'));
        Configuration::updateGlobalValue(
            static::MOLLIE_STATUS_PARTIAL_REFUND,
            Configuration::get(static::MOLLIE_STATUS_PARTIAL_REFUND)
        );
        Configuration::updateGlobalValue(static::MOLLIE_STATUS_REFUNDED, Configuration::get('PS_OS_REFUND'));
        Configuration::updateGlobalValue(static::MOLLIE_MAIL_WHEN_PAID, true);
        Configuration::updateGlobalValue(static::MOLLIE_MAIL_WHEN_CANCELED, true);
        Configuration::updateGlobalValue(static::MOLLIE_MAIL_WHEN_EXPIRED, true);
        Configuration::updateGlobalValue(static::MOLLIE_MAIL_WHEN_REFUNDED, true);

        Configuration::updateGlobalValue(static::MOLLIE_API, static::MOLLIE_ORDERS_API);
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

            if (!method_exists($this, 'displayAjax'.Tools::ucfirst(Tools::getValue('action')))) {
                die(json_encode(array(
                    'success' => false,
                )));
            }
            die(json_encode($this->{'displayAjax'.Tools::ucfirst(Tools::getValue('action'))}()));
        }

        if ($module = $this->checkPaymentModuleOverride()) {
            $this->context->controller->warnings[] = sprintf(
                $this->l('The method %s is overridden by module %s. This can cause interference with payments.'),
                'PaymentModule::validateOrder',
                $module
            );
        }
        if ($this->checkTemplateCompilation()) {
            $this->context->smarty->assign(array(
                'settingKey'   => version_compare(_PS_VERSION_, '1.7.3.0', '>=')
                    ? $this->trans('Template compilation', array(), 'Admin.Advparameters.Feature')
                    : Translate::getAdminTranslation('Template compilation', 'AdminPerformance'),
                'settingValue' => version_compare(_PS_VERSION_, '1.7.3.0', '>=')
                    ? $this->trans('Never recompile template files', array(), 'Admin.Advparameters.Feature')
                    : Translate::getAdminTranslation('Never recompile template files', 'AdminPerformance'),
                'settingsPage' => static::getMenuLocation('AdminPerformance'),
            ));
            $this->context->controller->warnings[] = $this->display(__FILE__, 'smarty_warning.tpl');
        }
        if ($this->checkStaleSmartyCache()) {
            $this->context->smarty->assign(array(
                'settingKey'   => version_compare(_PS_VERSION_, '1.7.3.0', '>=')
                    ? $this->trans('Clear cache', array(), 'Admin.Advparameters.Feature')
                    : Translate::getAdminTranslation('Clear cache', 'AdminPerformance'),
                'settingValue' => version_compare(_PS_VERSION_, '1.7.3.0', '>=')
                    ? $this->trans('Never clear cache files', array(), 'Admin.Advparameters.Feature')
                    : Translate::getAdminTranslation('Never clear cache files', 'AdminPerformance'),
                'settingsPage' => static::getMenuLocation('AdminPerformance'),
            ));
            $this->context->controller->errors[] = $this->display(__FILE__, 'smarty_error.tpl');
        }
        if ($this->checkRoundingMode()) {
            $this->context->smarty->assign(array(
                'settingKey'   => version_compare(_PS_VERSION_, '1.7.3.0', '>=')
                    ? $this->trans('Rounding mode', array(), 'Admin.Shopparameters.Feature')
                    : Translate::getAdminTranslation('Rounding mode', 'AdminPreferences'),
                'settingValue' => version_compare(_PS_VERSION_, '1.7.3.0', '>=')
                    ? $this->trans('Round up away from zero, when it is half way there (recommended)', array(), 'Admin.Shopparameters.Feature')
                    : Translate::getAdminTranslation('Round up away from zero, when it is half way there (recommended)', 'AdminPreferences'),
                'settingsPage' => static::getMenuLocation('AdminPreferences'),
            ));
            $this->context->controller->errors[] = $this->display(__FILE__, 'rounding_error.tpl');
        }

        $this->checkOrderStatusHook();

        $this->context->smarty->assign(array(
            'link'       => Context::getContext()->link,
            'module_dir' => __PS_BASE_URI__.'modules/'.basename(__FILE__, '.php').'/',
        ));

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

        $errors = array();
        if (Tools::isSubmit('submitNewAccount')) {
            $this->processNewAccount();
        }

        if (Tools::isSubmit("submit{$this->name}")) {
            $resultMessage = $this->getSaveResult($errors);
            if (!empty($errors)) {
                $this->context->controller->errors[] = $resultMessage;
            } else {
                $this->context->controller->confirmations[] = $resultMessage;
            }
        }

        $data = array(
            'update_message'         => $updateMessage,
            'title_status'           => $this->l('%s statuses:'),
            'title_visual'           => $this->l('Visual settings:'),
            'title_debug'            => $this->l('Debug info:'),
            'msg_result'             => $resultMessage,
            'msg_warning'            => $warningMessage,
            'path'                   => $this->_path,
            'val_api_key'            => Configuration::get(static::MOLLIE_API_KEY),
            'val_desc'               => Configuration::get(static::MOLLIE_DESCRIPTION),
            'payscreen_locale_value' => Configuration::get(static::MOLLIE_PAYMENTSCREEN_LOCALE),
            'val_images'             => Configuration::get(static::MOLLIE_IMAGES),
            'val_issuers'            => Configuration::get(static::MOLLIE_ISSUERS),
            'val_css'                => Configuration::get(static::MOLLIE_CSS),
            'val_errors'             => Configuration::get(static::MOLLIE_DISPLAY_ERRORS),
            'val_qrenabled'          => Configuration::get(static::MOLLIE_QRENABLED),
            'val_logger'             => Configuration::get(static::MOLLIE_DEBUG_LOG),
            'val_save'               => $this->l('Save'),
            'lang'                   => $this->lang,
        );

        if (file_exists("{$this->local_path}views/js/dist/back-v{$this->version}.min.js")) {
            $this->context->controller->addJS("{$this->_path}views/js/dist/back-v{$this->version}.min.js");
        } else {
            $this->context->controller->addJS($this->_path.'views/js/dist/back.min.js');
        }

        $this->context->smarty->assign($data);

        $html = $this->display(__FILE__, 'views/templates/admin/logo.tpl');
        if (!Configuration::get(static::MOLLIE_API_KEY)) {
            $html .= $this->generateAccountForm();
        }

        return $html.$this->generateSettingsForm();
    }

    /**
     * @return string
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     * @since 1.0.0
     */
    protected function generateAccountForm()
    {
        $fields = array(
            'form' => array(
                'legend'      => array(
                    'title' => $this->l('Create your account'),
                    'icon'  => 'icon-user',
                ),
                'description' => $this->l('Do you already have an API Key? Then you can skip this step and proceed to entering your API key.'),
                'input'       => array(
                    array(
                        'type'     => 'text',
                        'label'    => $this->l('First and last name'),
                        'name'     => 'mollie_new_name',
                        'required' => true,
                        'class'    => 'fixed-width-xxl',
                    ),
                    array(
                        'type'     => 'text',
                        'label'    => $this->l('Email address'),
                        'name'     => 'mollie_new_email',
                        'required' => true,
                        'class'    => 'fixed-width-xxl',
                    ),
                    array(
                        'type'     => 'text',
                        'label'    => $this->l('Shop name'),
                        'name'     => 'mollie_new_company',
                        'required' => true,
                        'class'    => 'fixed-width-xxl',
                    ),
                    array(
                        'type'     => 'text',
                        'label'    => $this->l('Address'),
                        'name'     => 'mollie_new_address',
                        'required' => true,
                        'class'    => 'fixed-width-xxl',
                    ),
                    array(
                        'type'     => 'text',
                        'label'    => $this->l('Postcode'),
                        'name'     => 'mollie_new_zipcode',
                        'required' => true,
                        'class'    => 'fixed-width-xxl',
                    ),
                    array(
                        'type'     => 'text',
                        'label'    => $this->l('City'),
                        'name'     => 'mollie_new_city',
                        'required' => true,
                        'class'    => 'fixed-width-xxl',
                    ),
                    array(
                        'type'    => 'select',
                        'label'   => $this->l('Country'),
                        'name'    => 'mollie_new_country',
                        'options' => array(
                            'query' => @include(dirname(__FILE__).'/countries.php'),
                            'id'    => 'id',
                            'name'  => 'name',
                        ),
                        'required' => true,
                        'class' => 'fixed-width-xxl',
                    ),
                ),
            ),
        );

        if (version_compare(_PS_VERSION_, '1.6.0.0', '>=')) {
            $fields['form']['buttons'] = array(
                array(
                    'title' => $this->l('Create'),
                    'class' => 'btn btn-default pull-right',
                    'icon'  => 'process-icon-plus',
                    'type'  => 'submit',
                    'name'  => 'submitNewAccount',
                ),
            );
        } else {
            $fields['form']['submit'] = array(
                'title' => $this->l('Create'),
            );
        }

        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitNewAccount';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            ."&configure={$this->name}&tab_module={$this->tab}&module_name={$this->name}";
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $employee = $this->context->employee;
        $helper->tpl_vars = array(
            'fields_value' => array(
                'mollie_new_name'    => Tools::isSubmit('mollie_new_name') ? Tools::getValue('mollie_new_name') : "{$employee->firstname} {$employee->lastname}",
                'mollie_new_email'   => Tools::isSubmit('mollie_new_email') ? Tools::getValue('mollie_new_email') : Configuration::get('PS_SHOP_EMAIL'),
                'mollie_new_company' => Tools::isSubmit('mollie_new_company') ? Tools::getValue('mollie_new_company') : Configuration::get('PS_SHOP_NAME'),
                'mollie_new_address' => Tools::isSubmit('mollie_new_address') ? Tools::getValue('mollie_new_address') : trim(Configuration::get('PS_SHOP_ADDR1').' '.Configuration::get('PS_SHOP_ADDR2')),
                'mollie_new_zipcode' => Tools::isSubmit('mollie_new_zipcode') ? Tools::getValue('mollie_new_zipcode') : trim(Configuration::get('PS_SHOP_CODE')),
                'mollie_new_city'    => Tools::isSubmit('mollie_new_city') ? Tools::getValue('mollie_new_city') : trim(Configuration::get('PS_SHOP_CITY')),
                'mollie_new_country' => Tools::isSubmit('mollie_new_country') ? Tools::getValue('mollie_new_country') : Country::getIsoById(Configuration::get('PS_SHOP_COUNTRY_ID')),
            ),
            'languages'    => $this->context->controller->getLanguages(),
            'id_language'  => $this->context->language->id,
        );

        return $helper->generateForm(array($fields));
    }

    /**
     * @return string
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     * @since 1.0.0
     */
    protected function generateSettingsForm()
    {
        $lang = Context::getContext()->language->id;
        $messageStatus = $this->l('Status for %s payments');
        $descriptionStatus = $this->l('`%s` payments get status `%s`');
        $messageMail = $this->l('Send mails when %s');
        $descriptionMail = $this->l('Send mails when transaction status becomes %s?');
        $allStatuses = array_merge(array(array('id_order_state' => 0, 'name' => $this->l('Skip this status'), 'color' => '#565656')), OrderState::getOrderStates($lang));
        $statuses = array();
        foreach ($this->statuses as $name => $val) {
            if ($name === \MollieModule\Mollie\Api\Types\PaymentStatus::STATUS_AUTHORIZED) {
                continue;
            }

            $val = (int) $val;
            if ($val) {
                $desc = Tools::strtolower(
                    sprintf(
                        $descriptionStatus,
                        $this->lang($name),
                        Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
                            'SELECT `name`
                            FROM `'._DB_PREFIX_.'order_state_lang`
                            WHERE `id_order_state` = '.(int) $val.'
                            AND `id_lang` = '.(int) $lang
                        )
                    )
                );
            } else {
                $desc = sprintf($this->l('`%s` payments do not get a status'), $this->lang($name));
            }
            $statuses[] = array(
                'name'             => $name,
                'key'              => @constant('static::MOLLIE_STATUS_'.Tools::strtoupper($name)),
                'value'            => $val,
                'description'      => $desc,
                'message'          => sprintf($messageStatus, $this->lang($name)),
                'key_mail'         => @constant('static::MOLLIE_MAIL_WHEN_'.Tools::strtoupper($name)),
                'value_mail'       => Configuration::get('MOLLIE_MAIL_WHEN_'.Tools::strtoupper($name)),
                'description_mail' => sprintf($descriptionMail, $this->lang($name)),
                'message_mail'     => sprintf($messageMail, $this->lang($name)),
            );
        }

        $input = array(
            array(
                'type'  => 'mollie-h2',
                'name'  => '',
                'title' => $this->l('Mollie Settings'),
            ),
            array(
                'type'     => 'text',
                'label'    => $this->l('API Key'),
                'desc'     => static::ppTags(
                    $this->l('You can find your API key in your [1]Mollie Profile[/1]; it starts with test or live.'),
                    array('<a href="https://www.mollie.com/dashboard/developers/api-keys" target="_blank" rel="noopener noreferrer">')
                ),
                'name'     => static::MOLLIE_API_KEY,
                'required' => true,
                'class'    => 'fixed-width-xxl',
            ),
            array(
                'type'          => 'mollie-description',
                'label'         => $this->l('Description'),
                'desc'          => sprintf($this->l('Enter a description here. Note: Payment methods may have a character limit, best keep the description under 29 characters. You can use the following variables: %s'), '{cart.id} {order.reference} {customer.firstname} {customer.lastname} {customer.company}'),
                'name'          => static::MOLLIE_DESCRIPTION,
                'required'      => true,
                'class'         => 'fixed-width-xxl',
                'depends'       => static::MOLLIE_API,
                'depends_value' => static::MOLLIE_PAYMENTS_API,
            ),
        );

        if (static::selectedApi() === static::MOLLIE_PAYMENTS_API) {
            $input[] = array(
                'type'    => 'select',
                'label'   => $this->l('Send locale for payment screen'),
                'desc'    => static::ppTags(
                    $this->l('Should the plugin send the current webshop [1]locale[/1] to Mollie. Mollie payment screens will be in the same language as your webshop. Mollie can also detect the language based on the user\'s browser language.'),
                    array('<a href="https://en.wikipedia.org/wiki/Locale">')
                ),
                'name'    => static::MOLLIE_PAYMENTSCREEN_LOCALE,
                'options' => array(
                    'query' => array(
                        array(
                            'id'   => static::PAYMENTSCREEN_LOCALE_BROWSER_LOCALE,
                            'name' => $this->l('Do not send locale using browser language'),
                        ),
                        array(
                            'id'   => static::PAYMENTSCREEN_LOCALE_SEND_WEBSITE_LOCALE,
                            'name' => $this->l('Send locale for payment screen'),
                        ),
                    ),
                    'id'    => 'id',
                    'name'  => 'name',
                ),
            );
        }

        $input = array_merge($input, array(
            array(
                'type'  => 'mollie-h2',
                'name'  => '',
                'title' => $this->l('Visual Settings'),
            ),
            array(
                'type'    => 'select',
                'label'   => $this->l('Images'),
                'desc'    => $this->l('Show big, normal or no payment method logos on checkout.'),
                'name'    => static::MOLLIE_IMAGES,
                'options' => array(
                    'query' => array(
                        array(
                            'id'   => static::LOGOS_HIDE,
                            'name' => $this->l('hide'),
                        ),
                        array(
                            'id'   => static::LOGOS_NORMAL,
                            'name' => $this->l('normal'),
                        ),
                        array(
                            'id'   => static::LOGOS_BIG,
                            'name' => $this->l('big'),
                        ),
                    ),
                    'id'    => 'id',
                    'name'  => 'name',
                ),
            ),
            array(
                'type'    => 'select',
                'label'   => $this->l('Issuer list'),
                'desc'    => $this->l('Some payment methods (eg. iDEAL) have an issuer list. This setting specifies where it is shown.'),
                'name'    => static::MOLLIE_ISSUERS,
                'options' => array(
                    'query' => array(
                        array(
                            'id'   => static::ISSUERS_ON_CLICK,
                            'name' => $this->l('On click'),
                        ),
                        array(
                            'id'   => static::ISSUERS_OWN_PAGE,
                            'name' => $this->l('Own page'),
                        ),
                        array(
                            'id'   => static::ISSUERS_PAYMENT_PAGE,
                            'name' => $this->l('Payment page'),
                        ),
                    ),
                    'id'    => 'id',
                    'name'  => 'name',
                ),
            ),
            array(
                'type'     => 'text',
                'label'    => $this->l('CSS file'),
                'desc'     => static::ppTags(
                    $this->l('Leave empty for default stylesheet. Should include file path when set. Hint: You can use [1]{BASE}[/1], [1]{THEME}[/1], [1]{CSS}[/1], [1]{MOBILE}[/1], [1]{MOBILE_CSS}[/1] and [1]{OVERRIDE}[/1] for easy folder mapping.'),
                    array('<kbd>')
                ),
                'name'     => static::MOLLIE_CSS,
                'class'    => 'long-text',
            ),
            array(
                'type'    => 'mollie-methods',
                'name'    => static::METHODS_CONFIG,
                'label'   => $this->l('Payment methods'),
                'desc'    => $this->l('Enable or disable the payment methods. You can drag and drop to rearrange the payment methods.'),
            ),
        ));

        if (static::selectedApi() === static::MOLLIE_PAYMENTS_API) {
            $input[] = array(
                'type'    => 'switch',
                'label'   => $this->l('Enable iDEAL QR'),
                'name'    => static::MOLLIE_QRENABLED,
                'is_bool' => true,
                'values'  => array(
                    array(
                        'id'    => 'active_on',
                        'value' => true,
                        'label' => \Translate::getAdminTranslation('Enabled', 'AdminCarriers'),
                    ),
                    array(
                        'id'    => 'active_off',
                        'value' => false,
                        'label' => \Translate::getAdminTranslation('Disabled', 'AdminCarriers'),
                    ),
                ),
            );
        } else {
            $input[] = array(
                'type'    => 'mollie-warning',
                'label'   => $this->l('Enable iDEAL QR'),
                'name'    => static::MOLLIE_QRENABLED,
                'message' => $this->l('QR Codes are currently not supported by the Orders API. Our apologies for the inconvenience!'),
            );
        }

        foreach (array_filter($statuses, function ($status) {
            return in_array($status['name'], array(
                \MollieModule\Mollie\Api\Types\PaymentStatus::STATUS_PAID,
                \MollieModule\Mollie\Api\Types\PaymentStatus::STATUS_AUTHORIZED,
                \MollieModule\Mollie\Api\Types\PaymentStatus::STATUS_CANCELED,
                \MollieModule\Mollie\Api\Types\PaymentStatus::STATUS_EXPIRED,
                \MollieModule\Mollie\Api\Types\RefundStatus::STATUS_REFUNDED,
                \MollieModule\Mollie\Api\Types\PaymentStatus::STATUS_OPEN,
                'partial_refund',
            ));
        }) as $status) {
            $input[] = array(
                'type'  => 'mollie-h3',
                'name'  => '',
                'title' => sprintf($this->l('%s statuses'), $this->lang($status['name'])),
            );
            $input[] = array(
                'type'    => 'select',
                'label'   => $status['message'],
                'desc'    => $status['description'],
                'name'    => $status['key'],
                'options' => array(
                    'query' => $allStatuses,
                    'id'    => 'id_order_state',
                    'name'  => 'name',
                ),
            );
            if (!in_array($status['name'], array('paid', 'partial_refund'))) {
                $input[] = array(
                    'type'    => 'switch',
                    'label'   => $status['message_mail'],
                    'name'    => $status['key_mail'],
                    'is_bool' => true,
                    'values'  => array(
                        array(
                            'id'    => 'active_on',
                            'value' => true,
                            'label' => \Translate::getAdminTranslation('Enabled', 'AdminCarriers'),
                        ),
                        array(
                            'id'    => 'active_off',
                            'value' => false,
                            'label' => \Translate::getAdminTranslation('Disabled', 'AdminCarriers'),
                        ),
                    ),
                );
            }
        }

        $orderStatuses = array(
            array(
                'name'           => $this->l('Disable this status'),
                'id_order_state' => '0',
            ),
        );
        $orderStatuses = array_merge($orderStatuses, OrderState::getOrderStates($this->context->language->id));

        for ($i = 0; $i < count($orderStatuses); $i++) {
            $orderStatuses[$i]['name'] = $orderStatuses[$i]['id_order_state'].' - '.$orderStatuses[$i]['name'];
        }

        $this->aasort($orderStatuses, 'id_order_state');

        $orderStatuses = OrderState::getOrderStates($this->context->language->id);

        $input = array_merge(
            $input,
            array(
                array(
                    'type'  => 'mollie-h2',
                    'name'  => '',
                    'title' => $this->l('Mollie API'),
                ),
                array(
                    'type'    => 'select',
                    'label'   => $this->l('Select which Mollie API to use'),
                    'desc'    => $this->l('Should the plugin use the new Mollie Orders API? This enables payment methods such as Klarna Pay Later.'),
                    'name'    => static::MOLLIE_API,
                    'options' => array(
                        'query' => array(
                            array(
                                'id'   => static::MOLLIE_PAYMENTS_API,
                                'name' => $this->l('Payments API'),
                            ),
                            array(
                                'id'   => static::MOLLIE_ORDERS_API,
                                'name' => $this->l('Orders API'),
                            ),
                        ),
                        'id'    => 'id',
                        'name'  => 'name',
                    ),
                ),
                array(
                    'type'  => 'mollie-h3',
                    'name'  => '',
                    'title' => $this->l('Orders API'),
                ),
                array(
                    'type'           => 'mollie-carriers',
                    'label'          => $this->l('Shipment information'),
                    'name'           => static::MOLLIE_TRACKING_URLS,
                    'depends'        => static::MOLLIE_API,
                    'depends_value'  => static::MOLLIE_ORDERS_API,
                ),
                array(
                    'type'    => 'mollie-carrier-switch',
                    'label'   => $this->l('Automatically ship when marked as `shipped`'),
                    'name'    => static::MOLLIE_AUTO_SHIP_MAIN,
                    'desc'    => $this->l('Enabling this feature will automatically send shipment information when an order has been marked as `shipped`'),
                    'is_bool' => true,
                    'values'  => array(
                        array(
                            'id'    => 'active_on',
                            'value' => true,
                            'label' => \Translate::getAdminTranslation('Enabled', 'AdminCarriers'),
                        ),
                        array(
                            'id'    => 'active_off',
                            'value' => false,
                            'label' => \Translate::getAdminTranslation('Disabled', 'AdminCarriers'),
                        ),
                    ),
                    'depends'        => static::MOLLIE_API,
                    'depends_value'  => static::MOLLIE_ORDERS_API,
                ),
                array(
                    'type'     => 'checkbox',
                    'label'    => $this->l('Automatically ship when one of these statuses is reached'),
                    'desc'     =>
                        $this->l('If an order reaches one of these statuses the module will automatically send shipment information'),
                    'name'     => static::MOLLIE_AUTO_SHIP_STATUSES,
                    'multiple' => true,
                    'values'   => array(
                        'query' => $orderStatuses,
                        'id'    => 'id_order_state',
                        'name'  => 'name',
                    ),
                    'expand'   => (count($orderStatuses) > 10) ? array(
                        'print_total' => count($orderStatuses),
                        'default'     => 'show',
                        'show'        => array('text' => $this->l('Show'), 'icon' => 'plus-sign-alt'),
                        'hide'        => array('text' => $this->l('Hide'), 'icon' => 'minus-sign-alt'),
                    ) : null,
                    'depends'        => static::MOLLIE_API,
                    'depends_value'  => static::MOLLIE_ORDERS_API,
                ),
                array(
                    'type'  => 'mollie-h2',
                    'name'  => '',
                    'title' => $this->l('Debug level'),
                ),
                array(
                    'type'    => 'switch',
                    'label'   => $this->l('Display errors'),
                    'name'    => static::MOLLIE_DISPLAY_ERRORS,
                    'desc'    => $this->l('Enabling this feature will display error messages (if any) on the front page. Use for debug purposes only!'),
                    'is_bool' => true,
                    'values'  => array(
                        array(
                            'id'    => 'active_on',
                            'value' => true,
                            'label' => \Translate::getAdminTranslation('Enabled', 'AdminCarriers'),
                        ),
                        array(
                            'id'    => 'active_off',
                            'value' => false,
                            'label' => \Translate::getAdminTranslation('Disabled', 'AdminCarriers'),
                        ),
                    ),
                ),
                array(
                    'type'    => 'select',
                    'label'   => $this->l('Log level'),
                    'desc'    => static::ppTags(
                        $this->l('Recommended level: Errors. Set to Everything to monitor incoming webhook requests. [1]View logs.[/1]'),
                        array("<a href='{$this->context->link->getAdminLink('AdminLogs')}'>")
                    ),
                    'name'    => static::MOLLIE_DEBUG_LOG,
                    'options' => array(
                        'query' => array(
                            array(
                                'id'   => static::DEBUG_LOG_NONE,
                                'name' => $this->l('Nothing'),
                            ),
                            array(
                                'id'   => static::DEBUG_LOG_ERRORS,
                                'name' => $this->l('Errors'),
                            ),
                            array(
                                'id'   => static::DEBUG_LOG_ALL,
                                'name' => $this->l('Everything'),
                            ),
                        ),
                        'id'    => 'id',
                        'name'  => 'name',
                    ),
                ),
            )
        );

        $fields = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Mollie'),
                    'icon'  => 'icon-credit-card',
                ),
                'input'  => $input,
                'submit' => array(
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right',
                ),
            ),
        );

        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = "submit{$this->name}";
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            ."&configure={$this->name}&tab_module={$this->tab}&module_name={$this->name}";
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages'    => $this->context->controller->getLanguages(),
            'id_language'  => $this->context->language->id,
        );

        return $helper->generateForm(array($fields));
    }

    /**
     * @return array
     * @throws PrestaShopException
     */
    protected function getConfigFieldsValues()
    {
        $configFields = array(
            static::MOLLIE_API_KEY              => Configuration::get(static::MOLLIE_API_KEY),
            static::MOLLIE_DESCRIPTION          => Configuration::get(static::MOLLIE_DESCRIPTION),
            static::MOLLIE_PAYMENTSCREEN_LOCALE => Configuration::get(static::MOLLIE_PAYMENTSCREEN_LOCALE),

            static::MOLLIE_CSS     => Configuration::get(static::MOLLIE_CSS),
            static::MOLLIE_IMAGES  => Configuration::get(static::MOLLIE_IMAGES),
            static::MOLLIE_ISSUERS => Configuration::get(static::MOLLIE_ISSUERS),

            static::MOLLIE_QRENABLED => Configuration::get(static::MOLLIE_QRENABLED),

            static::MOLLIE_STATUS_OPEN           => Configuration::get(static::MOLLIE_STATUS_OPEN),
            static::MOLLIE_STATUS_PAID           => Configuration::get(static::MOLLIE_STATUS_PAID),
            static::MOLLIE_STATUS_CANCELED       => Configuration::get(static::MOLLIE_STATUS_CANCELED),
            static::MOLLIE_STATUS_CANCELED       => Configuration::get(static::MOLLIE_STATUS_CANCELED),
            static::MOLLIE_STATUS_EXPIRED        => Configuration::get(static::MOLLIE_STATUS_EXPIRED),
            static::MOLLIE_STATUS_PARTIAL_REFUND => Configuration::get(static::MOLLIE_STATUS_PARTIAL_REFUND),
            static::MOLLIE_STATUS_REFUNDED       => Configuration::get(static::MOLLIE_STATUS_REFUNDED),
            static::MOLLIE_MAIL_WHEN_OPEN        => Configuration::get(static::MOLLIE_MAIL_WHEN_OPEN),
            static::MOLLIE_MAIL_WHEN_PAID        => Configuration::get(static::MOLLIE_MAIL_WHEN_PAID),
            static::MOLLIE_MAIL_WHEN_CANCELED    => Configuration::get(static::MOLLIE_MAIL_WHEN_CANCELED),
            static::MOLLIE_MAIL_WHEN_EXPIRED     => Configuration::get(static::MOLLIE_MAIL_WHEN_EXPIRED),
            static::MOLLIE_MAIL_WHEN_REFUNDED    => Configuration::get(static::MOLLIE_MAIL_WHEN_REFUNDED),

            static::MOLLIE_DISPLAY_ERRORS => Configuration::get(static::MOLLIE_DISPLAY_ERRORS),
            static::MOLLIE_DEBUG_LOG      => Configuration::get(static::MOLLIE_DEBUG_LOG),
            static::MOLLIE_API            => Configuration::get(static::MOLLIE_API),

            static::MOLLIE_AUTO_SHIP_MAIN  => Configuration::get(static::MOLLIE_AUTO_SHIP_MAIN),
        );

        $checkStatuses = array();
        if (Configuration::get(static::MOLLIE_AUTO_SHIP_STATUSES)) {
            $checkConfs = @json_decode(Configuration::get(static::MOLLIE_AUTO_SHIP_STATUSES), true);
        }
        if (!isset($checkConfs) || !is_array($checkConfs)) {
            $checkConfs = array();
        }

        foreach ($checkConfs as $conf) {
            $checkStatuses[static::MOLLIE_AUTO_SHIP_STATUSES.'_'.(int) $conf] = true;
        }

        $configFields = array_merge($configFields, $checkStatuses);

        return $configFields;
    }

    /**
     * Get carrier configuration
     *
     * @return array
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @since 3.3.0
     */
    public static function carrierConfig()
    {
        $dbConfig = @json_decode(Configuration::get(static::MOLLIE_TRACKING_URLS), true);
        if (!is_array($dbConfig)) {
            $dbConfig = array();
        }

        $carriers = Carrier::getCarriers(
            Context::getContext()->language->id,
            false,
            false,
            false,
            null,
            Carrier::ALL_CARRIERS
        );

        $configCarriers = array();
        foreach ($carriers as $carrier) {
            $idCarrier = (int) $carrier['id_carrier'];
            $configCarriers[] = array(
                'id_carrier'  => $idCarrier,
                'name'        => $carrier['name'],
                'source'      => isset($dbConfig[$idCarrier]) ? $dbConfig[$idCarrier]['source'] : ($carrier['external_module_name'] ? static::MOLLIE_CARRIER_MODULE : static::MOLLIE_CARRIER_CARRIER),
                'module'      => !empty($carrier['external_module_name']) ? $carrier['external_module_name'] : null,
                'module_name' => !empty($carrier['external_module_name']) ? $carrier['external_module_name'] : null,
                'custom_url'  => isset($dbConfig[$idCarrier]) ? $dbConfig[$idCarrier]['custom_url'] : '',
            );
        }
        if (count($dbConfig) !== count($configCarriers)) {
            Configuration::updateValue(static::MOLLIE_TRACKING_URLS, ($configCarriers));
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
        if (array_key_exists($str, $this->lang)) {
            return $this->lang[$str];
        }

        return $str;
    }

    /**
     * @param int        $order
     * @param string|int $statusId
     * @param bool|null  $useExistingPayment
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
     */
    public function setOrderStatus($order, $statusId, $useExistingPayment = null)
    {
        if (is_string($statusId)) {
            $status = $statusId;
            if (empty($this->statuses[$statusId])) {
                return;
            }
            $statusId = (int) $this->statuses[$statusId];
        } else {
            $status = '';
            foreach ($this->statuses as $mollieStatus => $prestaShopStatusId) {
                if ((int) $prestaShopStatusId === $statusId) {
                    $status = $mollieStatus;
                    break;
                }
            }
        }

        if (!$order instanceof Order) {
            $order = new Order((int) $order);
        }

        if (!Validate::isLoadedObject($order)) {
            return;
        }

        $history = array_map(function ($item) {
            return (int) $item['id_order_state'];
        }, $order->getHistory(Context::getContext()->language->id));
        if (!Validate::isLoadedObject($order)
            || $statusId === (int) $order->current_state
            || in_array($statusId, $history)
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

        Logger::addLog($status);
        if (Configuration::get('MOLLIE_MAIL_WHEN_'.Tools::strtoupper($status))) {
            Logger::addLog('addWithEmail');
            $history->addWithemail();
        } else {
            Logger::addLog('add');
            $history->add();
        }
    }

    /**
     * @param string $column
     * @param int    $id
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
                    _DB_PREFIX_.'mollie_payments',
                    bqSQL($column),
                    pSQL($id),
                    \MollieModule\Mollie\Api\Types\PaymentStatus::STATUS_PAID,
                    \MollieModule\Mollie\Api\Types\PaymentStatus::STATUS_AUTHORIZED
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
                    _DB_PREFIX_.'mollie_payments',
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
    protected function getSaveResult(&$errors = array())
    {
        $mollieApiKey = Tools::getValue(static::MOLLIE_API_KEY);

        if (!empty($mollieApiKey) && strpos($mollieApiKey, 'live') !== 0 && strpos($mollieApiKey, 'test') !== 0) {
            $errors[] = $this->l('The API key needs to start with test or live.');
        }

        $mollieDescription = Tools::getValue(static::MOLLIE_DESCRIPTION);
        if (Tools::getValue(static::METHODS_CONFIG) && json_decode(Tools::getValue(static::METHODS_CONFIG))) {
            Configuration::updateValue(
                static::METHODS_CONFIG,
                json_encode(@json_decode(Tools::getValue(static::METHODS_CONFIG)))
            );
        }
        $molliePaymentscreenLocale = Tools::getValue(static::MOLLIE_PAYMENTSCREEN_LOCALE);
        $mollieImages = Tools::getValue(static::MOLLIE_IMAGES);
        $mollieIssuers = Tools::getValue(static::MOLLIE_ISSUERS);
        $mollieCss = Tools::getValue(static::MOLLIE_CSS);
        if (!isset($mollieCss)) {
            $mollieCss = '';
        }
        $mollieLogger = Tools::getValue(static::MOLLIE_DEBUG_LOG);
        $mollieApi = Tools::getValue(static::MOLLIE_API);
        $mollieQrEnabled = (bool) Tools::getValue(static::MOLLIE_QRENABLED);
        $mollieErrors = Tools::getValue(static::MOLLIE_DISPLAY_ERRORS);

        $mollieShipMain = Tools::getValue(static::MOLLIE_AUTO_SHIP_MAIN);
        if (!isset($mollieErrors)) {
            $mollieErrors = false;
        } else {
            $mollieErrors = ($mollieErrors == 1);
        }

        if (empty($errors)) {
            Configuration::updateValue(static::MOLLIE_API_KEY, $mollieApiKey);
            Configuration::updateValue(static::MOLLIE_DESCRIPTION, $mollieDescription);
            Configuration::updateValue(static::MOLLIE_PAYMENTSCREEN_LOCALE, $molliePaymentscreenLocale);
            Configuration::updateValue(static::MOLLIE_IMAGES, $mollieImages);
            Configuration::updateValue(static::MOLLIE_ISSUERS, $mollieIssuers);
            Configuration::updateValue(static::MOLLIE_QRENABLED, (bool) $mollieQrEnabled);
            Configuration::updateValue(static::MOLLIE_CSS, $mollieCss);
            Configuration::updateValue(static::MOLLIE_DISPLAY_ERRORS, (int) $mollieErrors);
            Configuration::updateValue(static::MOLLIE_DEBUG_LOG, (int) $mollieLogger);
            Configuration::updateValue(static::MOLLIE_API, $mollieApi);
            Configuration::updateValue(
                static::MOLLIE_AUTO_SHIP_STATUSES,
                json_encode($this->getStatusesValue(static::MOLLIE_AUTO_SHIP_STATUSES))
            );
            Configuration::updateValue(static::MOLLIE_AUTO_SHIP_MAIN, (bool) $mollieShipMain);
            Configuration::updateValue(
                static::MOLLIE_TRACKING_URLS,
                json_encode(@json_decode(Tools::getValue(static::MOLLIE_TRACKING_URLS)))
            );

            foreach (array_keys($this->statuses) as $name) {
                $name = Tools::strtoupper($name);
                $new = (int) Tools::getValue("MOLLIE_STATUS_{$name}");
                $this->statuses[Tools::strtolower($name)] = $new;
                Configuration::updateValue("MOLLIE_STATUS_{$name}", $new);

                if ($name != \MollieModule\Mollie\Api\Types\PaymentStatus::STATUS_OPEN) {
                    Configuration::updateValue(
                        "MOLLIE_MAIL_WHEN_{$name}",
                        Tools::getValue("MOLLIE_MAIL_WHEN_{$name}") ? true : false
                    );
                }
            }
            $resultMessage = $this->l('The configuration has been saved!');
        } else {
            $resultMessage = 'The configuration could not be saved:<br /> - '.implode('<br /> - ', $errors);
        }

        return $resultMessage;
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
        $curl = new \MollieModule\Curl\Curl();
        $response = $curl->get($url);
        if (!is_object($response)) {
            throw new PrestaShopException($this->l('Warning: Could not retrieve update file from github.'));
        }
        if (empty($response->assets[0]->browser_download_url)) {
            throw new PrestaShopException($this->l('No download package found for the latest release.'));
        }

        return array(
            'version'  => $response->tag_name,
            'download' => $response->assets[0]->browser_download_url,
        );
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
                        $this->context->smarty->assign(array(
                            'this_version'    => $this->version,
                            'release_version' => $latestVersion,
                        ));
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

        return @Tools::file_get_contents($url.'/releases.atom');
    }

    /**
     * @param int    $orderId
     * @param string $transactionId
     *
     * @return array
     *
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @since      3.0.0
     * @deprecated 3.3.0
     */
    protected function doRefund($orderId, $transactionId)
    {
        return $this->doPaymentRefund($orderId, $transactionId);
    }

    /**
     * @param int        $orderId       PrestaShop Order ID
     * @param string     $transactionId Transaction/Mollie Order ID
     * @param float|null $amount        Amount to refund, refund all if `null`
     *
     * @return array
     *
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @since 3.3.0 Renamed `doRefund` to `doPaymentRefund`, added `$amount`
     */
    protected function doPaymentRefund($orderId, $transactionId, $amount = null)
    {
        try {
            /** @var \MollieModule\Mollie\Api\Resources\Payment $payment */
            $payment = $this->api->payments->get($transactionId);
            if ($amount) {
                $payment->refund(array(
                    'amount' => array(
                        'currency' => (string) $payment->amount->currency,
                        'value'    => (string) number_format($amount, 2, '.', ''),
                    ),
                ));
            } elseif ((float) $payment->settlementAmount->value - (float) $payment->amountRefunded->value > 0) {
                $payment->refund(array(
                    'amount' => array(
                        'currency' => (string) $payment->amount->currency,
                        'value'    => (string) number_format(((float) $payment->settlementAmount->value - (float) $payment->amountRefunded->value), 2, '.', ''),
                    ),
                ));
            }
        } catch (\MollieModule\Mollie\Api\Exceptions\ApiException $e) {
            return array(
                'status'      => 'fail',
                'msg_fail'    => $this->lang('The order could not be refunded!'),
                'msg_details' => $this->lang('Reason:').' '.$e->getMessage(),
            );
        }

        // Tell status to shop
        $this->setOrderStatus($orderId, \MollieModule\Mollie\Api\Types\RefundStatus::STATUS_REFUNDED);

        // Save status in mollie_payments table
        try {
            Db::getInstance()->update(
                'mollie_payments',
                array(
                    'updated_at'  => array('type' => 'sql', 'value' => 'NOW()'),
                    'bank_status' => \MollieModule\Mollie\Api\Types\RefundStatus::STATUS_REFUNDED,
                ),
                '`order_id` = '.(int) $orderId
            );
        } catch (PrestaShopDatabaseException $e) {
            static::tryAddOrderReferenceColumn();
            throw $e;
        }

        return array(
            'status'      => 'success',
            'msg_success' => $this->lang('The order has been refunded!'),
            'msg_details' => $this->lang('Mollie B.V. will transfer the money back to the customer on the next business day.'),
        );
    }

    /**
     * @param string     $transactionId
     * @param array      $lines
     * @param array|null $tracking
     *
     * @return array
     *
     * @since 3.3.0
     */
    protected function doShipOrderLines($transactionId, $lines = array(), $tracking = null)
    {
        try {
            /** @var \MollieModule\Mollie\Api\Resources\Order $payment */
            $order = $this->api->orders->get($transactionId);
            $shipment = array(
                'lines' => array_map(function ($line) {
                    return array_intersect_key(
                        (array) $line,
                        array_flip(array(
                            'id',
                            'quantity',
                        )));
                }, $lines),
            );
            if ($tracking && !empty($tracking['carrier']) && !empty($tracking['code'])) {
                $shipment['tracking'] = $tracking;
            }
            $order->createShipment($shipment);
        } catch (\MollieModule\Mollie\Api\Exceptions\ApiException $e) {
            return array(
                'success'  => false,
                'message'  => $this->lang('The product(s) could not be shipped!'),
                'detailed' => $e->getMessage(),
            );
        }

        return array(
            'success'  => true,
            'message'  => '',
            'detailed' => '',
        );
    }

    /**
     * @param string     $transactionId
     * @param array      $lines
     *
     * @return array
     *
     * @since 3.3.0
     */
    protected function doRefundOrderLines($transactionId, $lines = array())
    {
        try {
            /** @var \MollieModule\Mollie\Api\Resources\Order $payment */
            $order = $this->api->orders->get($transactionId);
            $refund = array(
                'lines' => array_map(function ($line) {
                    return array_intersect_key(
                        (array) $line,
                        array_flip(array(
                            'id',
                            'quantity',
                        )));
                }, $lines),
            );
            $order->refund($refund);
        } catch (\MollieModule\Mollie\Api\Exceptions\ApiException $e) {
            return array(
                'success'  => false,
                'message'  => $this->lang('The product(s) could not be refunded!'),
                'detailed' => $e->getMessage(),
            );
        }

        return array(
            'success'  => true,
            'message'  => '',
            'detailed' => '',
        );
    }

    /**
     * @param string $transactionId
     * @param array  $lines
     *
     * @return array
     *
     * @since 3.3.0
     * @throws ErrorException
     */
    protected function doCancelOrderLines($transactionId, $lines = array())
    {
        try {
            /** @var \MollieModule\Mollie\Api\Resources\Order $payment */
            $order = $this->api->orders->get($transactionId);
            if ($lines === array()) {
                $order->cancel();
            } else {
                $cancelableLines = array();
                foreach ($lines as $line) {
                    $cancelableLines[] = array('id' => $line['id'], 'quantity' => $line['quantity']);
                }
                $order->cancelLines(array('lines' => $cancelableLines));
            }
        } catch (\MollieModule\Mollie\Api\Exceptions\ApiException $e) {
            return array(
                'success'  => false,
                'message'  => $this->lang('The product(s) could not be canceled!'),
                'detailed' => $e->getMessage(),
            );
        }

        return array(
            'success'  => true,
            'message'  => '',
            'detailed' => '',
        );
    }

    /**
     * @return array
     *
     * @throws \MollieModule\Mollie\Api\Exceptions\ApiException
     * @throws PrestaShopException
     */
    public function getIssuerList()
    {
        $methods = array();
        foreach ($this->api->methods->all(array('include' => 'issuers')) as $method) {
            /** @var \MollieModule\Mollie\Api\Resources\Method $method */
            foreach ((array) $method->issuers as $issuer) {
                if (!$issuer) {
                    continue;
                }

                $issuer->href = $this->context->link->getModuleLink(
                    $this->name,
                    'payment',
                    array('method' => $method->id, 'issuer' => $issuer->id, 'rand' => time()),
                    true
                );

                if (!isset($methods[$method->id])) {
                    $methods[$method->id] = array();
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
        if (is_null($file)) {
            $file = Configuration::get(static::MOLLIE_CSS);
        }
        if (empty($file)) {
            // Use default css file
            $file = $this->_path.'views/css/mollie.css';
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
        $this->context->controller->addCSS($file);
    }

    // Hooks

    /**
     * @throws PrestaShopException
     */
    public function hookDisplayHeader()
    {
        $this->addCSSFile($this->_path.'views/css/front.css');
        $this->addCSSFile(Configuration::get(static::MOLLIE_CSS));
    }

    /**
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function hookDisplayBackOfficeHeader()
    {
        $html = '';
        if ($this->context->controller instanceof AdminOrdersController && version_compare(_PS_VERSION_, '1.6.0.0', '<')
            || $this->context->controller instanceof AdminModulesController && Tools::getValue('configure') === $this->name
        ) {
            $this->addCSSFile(Configuration::get(static::MOLLIE_CSS));
        }

        if ($this->context->controller instanceof AdminOrdersController) {
            $this->context->smarty->assign(array(
                'mollieProcessUrl'   => $this->context->link->getAdminLink('AdminModules', true).'&configure=mollie&ajax=1',
                'mollieCheckMethods' => time() > ((int) Configuration::get(static::MOLLIE_METHODS_LAST_CHECK) + static::MOLLIE_METHODS_CHECK_INTERVAL),
            ));
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
     *
     * @fixme: find a solution for 1.7 currencies
     */
    public function hookDisplayAdminOrder($params)
    {
        $cartId = Cart::getCartIdByOrderId((int) $params['id_order']);
        $transaction = static::getPaymentBy('cart_id', (int) $cartId);
        if (empty($transaction)) {
            return false;
        }
        $currencies = array();
        foreach (Currency::getCurrencies() as $currency) {
            $currencies[Tools::strtoupper($currency['iso_code'])] = array(
                'name'     => $currency['name'],
                'iso_code' => Tools::strtoupper($currency['iso_code']),
                'sign'     => $currency['sign'],
                'blank'    => (bool) isset($currency['blank']) ? $currency['blank'] : true,
                'format'   => (int) $currency['format'],
                'decimals' => (bool) isset($currency['decimals']) ? $currency['decimals'] : true,
            );
        }

        if (file_exists("{$this->local_path}views/js/dist/back-v{$this->version}.min.js")) {
            $this->context->controller->addJS("{$this->_path}views/js/dist/back-v{$this->version}.min.js");
        } else {
            $this->context->controller->addJS($this->_path.'views/js/dist/back.min.js');
        }

        $this->context->smarty->assign(array(
            'ajaxEndpoint'  => $this->context->link->getAdminLink('AdminModules', true).'&configure=mollie&ajax=1&action=MollieOrderInfo',
            'transactionId' => $transaction['transaction_id'],
            'currencies'    => $currencies,
            'tracking'      => static::getShipmentInformation($params['id_order']),
        ));

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
        $issuerSetting = Configuration::get(static::MOLLIE_ISSUERS);
        $apiMethods = $this->getMethodsForCheckout();
        $issuerList = array();
        foreach ($apiMethods as $apiMethod) {
            if ($apiMethod['id'] === \MollieModule\Mollie\Api\Types\PaymentMethod::IDEAL) {
                $issuerList[\MollieModule\Mollie\Api\Types\PaymentMethod::IDEAL] = array();
                foreach ($apiMethod['issuers'] as $issuer) {
                    $issuer['href'] = $this->context->link->getModuleLink(
                        $this->name,
                        'payment',
                        array('method' => $apiMethod['id'], 'issuer' => $issuer['id'], 'rand' => time()),
                        true
                    );
                    $issuerList[\MollieModule\Mollie\Api\Types\PaymentMethod::IDEAL][$issuer['id']] = $issuer;
                }
            }
        }

        $cart = Context::getContext()->cart;
        $smarty->assign(array(
            'link'                   => $this->context->link,
            'cartAmount'             => (int) ($cart->getOrderTotal(true) * 100),
            'methods'                => $apiMethods,
            'issuers'                => $issuerList,
            'issuer_setting'         => $issuerSetting,
            'images'                 => Configuration::get(static::MOLLIE_IMAGES),
            'warning'                => $this->warning,
            'msg_pay_with'           => $this->lang('Pay with %s'),
            'msg_bankselect'         => $this->lang('Select your bank:'),
            'module'                 => $this,
            'mollie_front_app_path'  => file_exists("{$this->local_path}views/js/dist/front-v{$this->version}.min.js") ? static::getMediaPath("{$this->_path}views/js/dist/front-v{$this->version}.min.js") : static::getMediaPath("{$this->_path}views/js/dist/front.min.js"),
            'mollie_translations'    => array(
                'chooseYourBank' => $this->l('Choose your bank'),
                'orPayByIdealQr' => $this->l('or pay by iDEAL QR'),
                'choose'         => $this->l('Choose'),
                'cancel'         => $this->l('Cancel'),
            ),
        ));

        return $this->display(__FILE__, 'addjsdef.tpl').$this->display(__FILE__, 'payment.tpl');
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
        $methods = $this->getMethodsForCheckout();
        $issuerList = array();
        foreach ($methods as $apiMethod) {
            if ($apiMethod['id'] === \MollieModule\Mollie\Api\Types\PaymentMethod::IDEAL) {
                $issuerList[\MollieModule\Mollie\Api\Types\PaymentMethod::IDEAL] = array();
                foreach ($apiMethod['issuers'] as $issuer) {
                    $issuer['href'] = $this->context->link->getModuleLink(
                        $this->name,
                        'payment',
                        array('method' => $apiMethod['id'], 'issuer' => $issuer['id'], 'rand' => time()),
                        true
                    );
                    $issuerList[\MollieModule\Mollie\Api\Types\PaymentMethod::IDEAL][$issuer['id']] = $issuer;
                }
            }
        }

        $context = Context::getContext();
        $iso = Tools::strtolower($context->currency->iso_code);
        $paymentOptions = array();

        foreach ($methods as $method) {
            if (!isset(static::$methodCurrencies[$method['id']])) {
                continue;
            }
            if (!in_array($iso, static::$methodCurrencies[$method['id']])) {
                continue;
            }

            $paymentOptions[] = array(
                'cta_text' => $this->lang($method['name']),
                'logo'     => Configuration::get(static::MOLLIE_IMAGES) === static::LOGOS_NORMAL
                    ? $method['image']['size1x']
                    : $method['image']['size2x'],
                'action'   => $this->context->link->getModuleLink(
                    'mollie',
                    'payment',
                    array('method' => $method['id'], 'rand' => time()),
                    true
                ),
            );
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
            return array();
        }

        $methods = $this->getMethodsForCheckout();
        $issuerList = array();
        foreach ($methods as $apiMethod) {
            if ($apiMethod['id'] === \MollieModule\Mollie\Api\Types\PaymentMethod::IDEAL) {
                $issuerList[\MollieModule\Mollie\Api\Types\PaymentMethod::IDEAL] = array();
                foreach ($apiMethod['issuers'] as $issuer) {
                    $issuer['href'] = $this->context->link->getModuleLink(
                        $this->name,
                        'payment',
                        array('method' => $apiMethod['id'], 'issuer' => $issuer['id'], 'rand' => time()),
                        true
                    );
                    $issuerList[\MollieModule\Mollie\Api\Types\PaymentMethod::IDEAL][$issuer['id']] = $issuer;
                }
            }
        }

        $context = Context::getContext();
        $cart = $context->cart;

        $context->smarty->assign(array(
            'idealIssuers'  => isset($issuerList[\MollieModule\Mollie\Api\Types\PaymentMethod::IDEAL])
                ? $issuerList[\MollieModule\Mollie\Api\Types\PaymentMethod::IDEAL]
                : array(),
            'link'          => $this->context->link,
            'qrCodeEnabled' => Configuration::get(static::MOLLIE_QRENABLED),
            'qrAlign'       => 'left',
            'cartAmount'    => (int) ($cart->getOrderTotal(true) * 100),
        ));

        $iso = Tools::strtolower($context->currency->iso_code);
        $paymentOptions = array();
        foreach ($methods as $method) {
            if (!isset(static::$methodCurrencies[$method['id']])) {
                continue;
            }
            if (!in_array($iso, static::$methodCurrencies[$method['id']])) {
                continue;
            }

            if ($method['id'] === \MollieModule\Mollie\Api\Types\PaymentMethod::IDEAL
                && Configuration::get(static::MOLLIE_ISSUERS) === static::ISSUERS_ON_CLICK
            ) {
                $newOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();
                $newOption
                    ->setCallToActionText($this->lang($method['name']))
                    ->setModuleName($this->name)
                    ->setAction(Context::getContext()->link->getModuleLink(
                        $this->name,
                        'payment',
                        array('method' => $method['id'], 'rand' => time()),
                        true
                    ))
                    ->setInputs(array(
                        'token' => array(
                            'name'  => 'issuer',
                            'type'  => 'hidden',
                            'value' => '',
                        ),
                    ))
                    ->setAdditionalInformation($this->display(__FILE__, 'ideal_dropdown.tpl'));

                $imageConfig = Configuration::get(static::MOLLIE_IMAGES);
                if ($imageConfig === static::LOGOS_NORMAL) {
                    $newOption->setLogo($method['image']['svg']);
                } elseif ($imageConfig === static::LOGOS_BIG) {
                    $newOption->setLogo($method['image']['size2x']);
                }

                $paymentOptions[] = $newOption;
            } else {
                $newOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();
                $newOption
                    ->setCallToActionText($this->lang($method['name']))
                    ->setModuleName($this->name)
                    ->setAction(Context::getContext()->link->getModuleLink(
                        'mollie',
                        'payment',
                        array('method' => $method['id'], 'rand' => time()),
                        true
                    ));

                $imageConfig = Configuration::get(static::MOLLIE_IMAGES);
                if ($imageConfig === static::LOGOS_NORMAL) {
                    $newOption->setLogo($method['image']['svg']);
                } elseif ($imageConfig === static::LOGOS_BIG) {
                    $newOption->setLogo($method['image']['size2x']);
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
        $payment = $this->getPaymentBy('cart_id', (int) Tools::getValue('id_cart'));
        if ($payment && $payment['bank_status'] == \MollieModule\Mollie\Api\Types\PaymentStatus::STATUS_PAID) {
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
            _DB_PREFIX_.'mollie_payments'
        );

        try {
            if (!Db::getInstance()->execute($sql)) {
                $this->_errors[] = 'Database error: '.Db::getInstance()->getMsgError();

                return false;
            }
        } catch (PrestaShopException $e) {
            static::tryAddOrderReferenceColumn();

            $this->_errors[] = 'Database error: '.Db::getInstance()->getMsgError();

            return false;
        }

        return true;
    }

    /**
     * Create new order state for partial refunds.
     *
     * @since 2.0.0
     *
     * @return boolean
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws Adapter_Exception
     */
    public function partialRefundOrderState()
    {
        $stateExist = false;
        $states = OrderState::getOrderStates((int) $this->context->language->id);
        foreach ($states as $state) {
            if (in_array($this->lang('Mollie Partially Refunded'), $state)) {
                $stateExist = true;
                break;
            }
        }
        if (!$stateExist) {
            $orderState = new OrderState();
            $orderState->send_email = false;
            $orderState->color = '#6F8C9F';
            $orderState->hidden = false;
            $orderState->delivery = false;
            $orderState->logable = false;
            $orderState->invoice = false;
            $orderState->module_name = $this->name;
            $orderState->name = array();
            $languages = Language::getLanguages(false);
            foreach ($languages as $language) {
                $orderState->name[$language['id_lang']] = $this->lang('Mollie partially refunded');
            }
            if ($orderState->add()) {
                $source = _PS_MODULE_DIR_.'mollie/views/img/logo_small.png';
                $destination = _PS_ROOT_DIR_.'/img/os/'.(int) $orderState->id.'.gif';
                @copy($source, $destination);
            }
            Configuration::updateValue(static::MOLLIE_STATUS_PARTIAL_REFUND, (int) $orderState->id);
        }

        return true;
    }

    /**
     * @param string      $mediaUri
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
            $mediaUri = '/'.ltrim(str_replace(str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, _PS_ROOT_DIR_), __PS_BASE_URI__, $mediaUri), '/\\');
            // remove PS_BASE_URI on _PS_ROOT_DIR_ for the following
            $fileUri = _PS_ROOT_DIR_.Tools::str_replace_once(__PS_BASE_URI__, DIRECTORY_SEPARATOR, $mediaUri);

            if (!@filemtime($fileUri) || @filesize($fileUri) === 0) {
                return false;
            }

            $mediaUri = str_replace('//', '/', $mediaUri);
        }

        if ($cssMediaType) {
            return array($mediaUri => $cssMediaType);
        }

        return $mediaUri;
    }

    /**
     * Get media path for JS
     *
     * @param string      $relativeMediaUri
     * @param string|null $cssMediaType
     *
     * @return array|bool|mixed|string
     *
     * @since 3.2.0
     */
    public static function getMediaPathForJavaScript($relativeMediaUri, $cssMediaType = null)
    {
        $version = static::getDatabaseVersion();
        foreach (array('front.min.js', 'back.min.js') as $needle) {
            if (Tools::substr($relativeMediaUri, -Tools::strlen($needle)) === $needle) {
                $parts = explode('.', $relativeMediaUri);
                $parts[count($parts) - 3] = "{$parts[count($parts) - 3]}-v{$version}";
                $newRelativeMediaUri = implode('.', $parts);
                if (file_exists(_PS_MODULE_DIR_."mollie/{$newRelativeMediaUri}")) {
                    $relativeMediaUri = $newRelativeMediaUri;
                }
            }
        }

        return static::getMediaPath(_PS_MODULE_DIR_."mollie/{$relativeMediaUri}", $cssMediaType);
    }

    /**
     * Get payment data
     *
     * @param float|string $amount
     * @param              $currency
     * @param string       $method
     * @param string|null  $issuer
     * @param int|Cart     $cartId
     * @param string       $secureKey
     * @param bool         $qrCode
     * @param string       $orderReference
     *
     * @return array
     * @throws PrestaShopException
     * @throws Adapter_Exception
     * @throws \PrestaShop\PrestaShop\Adapter\CoreException
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
        $qrCode = false,
        $orderReference = ''
    ) {
        if (!$orderReference) {
            /** @var Mollie $module */
            $module = Module::getInstanceByName('mollie');
            $module->currentOrderReference = $orderReference = Order::generateReference();
        }
        $description = static::generateDescriptionFromCart($cartId, $orderReference);
        $context = Context::getContext();
        $cart = new Cart($cartId);
        $customer = new Customer($cart->id_customer);

        $paymentData = array(
            'amount'      => array(
                'currency' => (string) ($currency ? Tools::strtoupper($currency) : 'EUR'),
                'value'    => (string) (number_format(str_replace(',', '.', $amount), 2, '.', '')),
            ),
            'method'      => $method,
            'redirectUrl' => ($qrCode
                ? $context->link->getModuleLink(
                    'mollie',
                    'qrcode',
                    array('cart_id' => $cartId, 'done' => 1, 'rand' => time()),
                    true
                )
                : $context->link->getModuleLink(
                    'mollie',
                    'return',
                    array('cart_id' => $cartId, 'utm_nooverride' => 1, 'rand' => time()),
                    true
                )
            ),
        );
        if (!static::isLocalEnvironment()) {
            $paymentData['webhookUrl'] = $context->link->getModuleLink(
                'mollie',
                'webhook',
                array(),
                true
            );
        }

        $paymentData['metadata'] = array(
            'cart_id'         => $cartId,
            'order_reference' => $orderReference,
            'secure_key'      => Tools::encrypt($secureKey),
        );

        // Send webshop locale
        if ((static::selectedApi() === static::MOLLIE_PAYMENTS_API
                && Configuration::get(static::MOLLIE_PAYMENTSCREEN_LOCALE) === static::PAYMENTSCREEN_LOCALE_SEND_WEBSITE_LOCALE)
            || static::selectedApi() === static::MOLLIE_ORDERS_API
        ) {
            $locale = static::getWebshopLocale();
            if (preg_match(
                '/^[a-z]{2}(?:[\-_][A-Z]{2})?$/iu',
                $locale
            )) {
                $paymentData['locale'] = $locale;
            }
        }

        if (static::selectedApi() === static::MOLLIE_PAYMENTS_API) {
            $paymentData['description'] = str_ireplace(
                array('%'),
                array($cartId),
                $description
            );
            $paymentData['issuer'] = $issuer;

            if (isset($context->cart)) {
                if (isset($context->cart->id_customer)) {
                    $buyer = new Customer($context->cart->id_customer);
                    $paymentData['billingEmail'] = (string) $buyer->email;
                }
                if (isset($context->cart->id_address_invoice)) {
                    $billing = new Address((int) $context->cart->id_address_invoice);
                    $paymentData['billingAddress'] = array(
                        'streetAndNumber' => (string) $billing->address1.' '.$billing->address2,
                        'city'            => (string) $billing->city,
                        'region'          => (string) State::getNameById($billing->id_state),
                        'country'         => (string) Country::getIsoById($billing->id_country),
                    );
                    $paymentData['billingAddress']['postalCode'] = (string) $billing->postcode ?: '-';
                }
                if (isset($context->cart->id_address_delivery)) {
                    $shipping = new Address((int) $context->cart->id_address_delivery);
                    $paymentData['shippingAddress'] = array(
                        'streetAndNumber' => (string) $shipping->address1.' '.$shipping->address2,
                        'city'            => (string) $shipping->city,
                        'region'          => (string) State::getNameById($shipping->id_state),
                        'country'         => (string) Country::getIsoById($shipping->id_country),
                    );
                    $paymentData['shippingAddress']['postalCode'] = (string) $shipping->postcode ?: '-';
                }
            }
        } elseif (static::selectedApi() === static::MOLLIE_ORDERS_API) {
            if (isset($cart->id_address_invoice)) {
                $billing = new Address((int) $cart->id_address_invoice);
                $paymentData['billingAddress'] = array(
                    'givenName'       => (string) $customer->firstname,
                    'familyName'      => (string) $customer->lastname,
                    'email'           => (string) $customer->email,
                    'streetAndNumber' => (string) $billing->address1.' '.$billing->address2,
                    'city'            => (string) $billing->city,
                    'region'          => (string) State::getNameById($billing->id_state),
                    'country'         => (string) Country::getIsoById($billing->id_country),
                );
                $paymentData['billingAddress']['postalCode'] = (string) $billing->postcode ?: '-';
            }
            if (isset($cart->id_address_delivery)) {
                $shipping = new Address((int) $cart->id_address_delivery);
                $paymentData['shippingAddress'] = array(
                    'givenName'       => (string) $customer->firstname,
                    'familyName'      => (string) $customer->lastname,
                    'email'           => (string) $customer->email,
                    'streetAndNumber' => (string) $shipping->address1.' '.$shipping->address2,
                    'city'            => (string) $shipping->city,
                    'region'          => (string) State::getNameById($shipping->id_state),
                    'country'         => (string) Country::getIsoById($shipping->id_country),
                );
                $paymentData['shippingAddress']['postalCode'] = (string) $shipping->postcode ?: '-';
            }
            $paymentData['orderNumber'] = $orderReference;
            $paymentData['lines'] = static::getCartLines($amount);
            $paymentData['payment'] = array();
            if ($issuer) {
                $paymentData['payment']['issuer'] = $issuer;
            }
            if (empty($paymentData['payment'])) {
                unset($paymentData['payment']);
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
    public static function getCartLines($amount)
    {
        /** @var Cart $cart */
        $cart = Context::getContext()->cart;
        /** @var static $mollie */
        $mollie = Module::getInstanceByName('mollie');
        $oCurrency = new Currency($cart->id_currency);
        $apiRoundingPrecision = static::API_ROUNDING_PRECISION; // PHP 5.3, closures and static access, not a good combo :(

        $remaining = round($amount, $apiRoundingPrecision);
        $shipping = round($cart->getTotalShippingCost(null, true), $apiRoundingPrecision);
        $wrapping = round($cart->getGiftWrappingPrice(), $apiRoundingPrecision);
        $remaining = round($remaining - $shipping - $wrapping, $apiRoundingPrecision);
        $cartItems = $cart->getProducts();

        $aItems = array();
        /* Item */
        foreach ($cartItems as $cartItem) {
            // Get the rounded total w/ tax
            $roundedTotalWithTax = round($cartItem['total_wt'], $apiRoundingPrecision);

            // Skip if no qty
            $quantity = (int) $cartItem['cart_quantity'];
            if ($quantity <= 0 || $cartItem['price_wt'] <= 0) {
                continue;
            }

            // Generate the product hash
            $idProduct = number_format($cartItem['id_product']);
            $idProductAttribute = number_format($cartItem['id_product_attribute']);
            $idCustomization = number_format($cartItem['id_customization']);

            $productHash = "{$idProduct}¤{$idProductAttribute}¤{$idCustomization}";
            $aItems[$productHash] = array();

            // Try to spread this product evenly and account for rounding differences on the order line
            foreach (static::spreadAmountEvenly($roundedTotalWithTax, $quantity) as $unitPrice => $qty) {
                $aItems[$productHash][] = array(
                    'name'        => $cartItem['name'],
                    'sku'         => $productHash,
                    'targetVat'   => (float) $cartItem['rate'],
                    'quantity'    => $qty,
                    'unitPrice'   => $unitPrice,
                    'totalAmount' => (float) $unitPrice * $qty,
                );
                $remaining -= round((float) $unitPrice * $qty, $apiRoundingPrecision);
            }
        }

        // Compensate for order total rounding inaccuracies
        if (round($remaining, $apiRoundingPrecision) < 0) {
            $remaining = round($remaining, $apiRoundingPrecision);
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
        }

        // Fill the order lines with the rest of the data (tax, total amount, etc.)
        foreach ($aItems as $productHash => $aItem) {
            $aItems[$productHash] = array_map(function ($line) use ($apiRoundingPrecision, $oCurrency) {
                $quantity = (int) $line['quantity'];
                $targetVat = $line['targetVat'];
                $unitPrice = $line['unitPrice'];
                $unitPriceNoTax = round($line['unitPrice'] / (1 + ($targetVat / 100)), $apiRoundingPrecision);
                $sku = $line['sku'];

                // Calculate VAT
                $totalAmount = round($unitPrice * $quantity, $apiRoundingPrecision);
                $actualVatRate = round(($unitPrice * $quantity - $unitPriceNoTax * $quantity) / ($unitPriceNoTax * $quantity) * 100, $apiRoundingPrecision);
                $vatAmount = $totalAmount * ($actualVatRate / ($actualVatRate + 100));

                return array(
                    'name'        => $line['name'],
                    'quantity'    => (int) $quantity,
                    'sku'         => $sku,
                    'unitPrice'   => $unitPrice,
                    'totalAmount' => $totalAmount,
                    'vatRate'     => round($actualVatRate, $apiRoundingPrecision),
                    'vatAmount'   => round($vatAmount, $apiRoundingPrecision),
                );
            }, $aItem);
        }

        $averageProductTaxRate = 0;
        $averageProductTaxQuantity = 0;
        foreach ($aItems as $group) {
            foreach ($group as $item) {
                $averageProductTaxRate += $item['vatRate'] * $item['quantity'];
                $averageProductTaxQuantity += $item['quantity'];
            }
        }
        $averageProductTaxRate = round($averageProductTaxRate / $averageProductTaxQuantity, $apiRoundingPrecision);

        // Add shipping
        if (round($shipping, 2) > 0) {
            $aItems['shipping'] = array(
                array(
                    'name'        => $mollie->l('Shipping'),
                    'quantity'    => 1,
                    'unitPrice'   => round($shipping, $apiRoundingPrecision),
                    'totalAmount' => round($shipping, $apiRoundingPrecision),
                    'vatAmount'   => round($shipping * $averageProductTaxRate / ($averageProductTaxRate + 100), static::API_ROUNDING_PRECISION),
                    'vatRate'     => $averageProductTaxRate,
                ),
            );
        }

        // Add wrapping
        if (round($wrapping, 2) > 0) {
            $aItems['wrapping'] = array(
                array(
                    'name'        => $mollie->l('Gift wrapping'),
                    'quantity'    => 1,
                    'unitPrice'   => round($wrapping, $apiRoundingPrecision),
                    'totalAmount' => round($wrapping, $apiRoundingPrecision),
                    'vatAmount'   => round($wrapping * $averageProductTaxRate / ($averageProductTaxRate + 100), static::API_ROUNDING_PRECISION),
                    'vatRate'     => $averageProductTaxRate,
                ),
            );
        }

        // Add discount if applicable
        $mollieOrderLinesAmount = array_sum(array_map(function ($line) {
            return array_sum(array_column(array_column($line, 'totalAmount'), 'value'));
        }, $aItems));
        $difference = $mollieOrderLinesAmount - round($amount, $apiRoundingPrecision);
        if ($difference >= 0.01) {
            $aItems['discount'] = array(
                array(
                    'name'        => 'Discount',
                    'type'        => 'discount',
                    'quantity'    => 1,
                    'unitPrice'   => '-'.number_format($difference, $apiRoundingPrecision, '.', ''),
                    'totalAmount' => '-'.number_format($difference, $apiRoundingPrecision, '.', ''),
                    'vatAmount'   => '-'.number_format(round($difference, $apiRoundingPrecision) * (($averageProductTaxRate / 100) / (1 + ($averageProductTaxRate/ 100))), $apiRoundingPrecision, '.', ''),
                    'vatRate'     => $averageProductTaxRate,
                ),
            );
        }

        // Ungroup all the cart lines, just one level
        $newItems = array();
        foreach ($aItems as &$items) {
            foreach ($items as &$item) {
                $newItems[] = $item;
            }
        }

        // Convert floats to strings for the Mollie API and add additional info
        foreach ($newItems as $index => $item) {
            $newItems[$index] = array(
                'name'        => (string) $item['name'],
                'quantity'    => (int) $item['quantity'],
                'sku'         => (string) (isset($item['sku']) ? $item['sku'] : ''),
                'unitPrice'   => array(
                    'currency' => Tools::strtoupper($oCurrency->iso_code),
                    'value'    => number_format($item['unitPrice'], $apiRoundingPrecision, '.', ''),
                ),
                'totalAmount' => array(
                    'currency' => Tools::strtoupper($oCurrency->iso_code),
                    'value'    => number_format($item['totalAmount'], $apiRoundingPrecision, '.', ''),
                ),
                'vatAmount'   => array(
                    'currency' => Tools::strtoupper($oCurrency->iso_code),
                    'value'    => number_format($item['vatAmount'], $apiRoundingPrecision, '.', ''),
                ),
                'vatRate'     => number_format($item['vatRate'], $apiRoundingPrecision, '.', ''),
            );
        }

        return $newItems;
    }

    /**
     * Spread the amount evenly
     *
     * @param float $amount
     * @param int   $qty
     *
     * @return array Spread amounts
     *
     * @since 3.3.3
     */
    public static function spreadAmountEvenly($amount, $qty)
    {
        // Start with a freshly rounded amount
        $amount = (float) round($amount, static::API_ROUNDING_PRECISION);
        // Estimate a target spread amount to begin with
        $spreadTotals = array_fill(1, $qty, round($amount / $qty, static::API_ROUNDING_PRECISION));
        $newTotal = $spreadTotals[1] * $qty;
        // Calculate the difference between applying this amount only and the total amount given
        $difference = abs(round($newTotal - $amount, static::API_ROUNDING_PRECISION));
        // Start at the last index
        $index = $qty;
        // Keep going until there's no longer a difference
        while ($difference > 0) {
            // Go for a new pass if there's still a difference after the current one
            $index = $index > 0 ? $index : $qty;
            // Difference is going to be decreased by 0.01
            $difference -= 0.01;
            // Apply the rounding difference at the current index
            $spreadTotals[$index--] += $newTotal < $amount ? 0.01 : -0.01;
        }
        // At the end, compensate for floating point inaccuracy and apply to the last index (points at the lowest amount)
        if (round(abs($amount - array_sum($spreadTotals)), static::API_ROUNDING_PRECISION) >= 0.01) {
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
     * @param float   $newTotal
     *
     * @return array[]
     *
     * @since 3.2.2
     * @since 3.3.3 Omits VAT details
     */
    public static function spreadCartLineGroup($cartLineGroup, $newTotal)
    {
        $apiRoundingPrecision = static::API_ROUNDING_PRECISION;
        $newTotal = round($newTotal, $apiRoundingPrecision);
        $quantity = array_sum(array_column($cartLineGroup, 'quantity'));
        $newCartLineGroup = array();
        $spread = static::spreadAmountEvenly($newTotal, $quantity);
        foreach ($spread as $unitPrice => $qty) {
            $newCartLineGroup[] = array(
                'name'        => $cartLineGroup[0]['name'],
                'quantity'    => $qty,
                'unitPrice'   => (float) $unitPrice,
                'totalAmount' => (float) $unitPrice * $qty,
                'sku'         => $cartLineGroup[0]['sku'],
                'targetVat'   => $cartLineGroup[0]['targetVat'],
            );
        }

        return $newCartLineGroup;
    }

    /**
     * Generate a description from the Cart
     *
     * @param Cart|int $cartId         Cart or Cart ID
     * @param string   $orderReference Order reference
     *
     * @return string Description
     *
     * @throws PrestaShopException
     * @throws \PrestaShop\PrestaShop\Adapter\CoreException
     * @since 3.0.0
     */
    public static function generateDescriptionFromCart($cartId, $orderReference = '')
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

        $filters = array(
            '%'                    => $cartId,
            '{cart.id}'            => $cartId,
            '{order.reference}'    => $orderReference,
            '{customer.firstname}' => $buyer == null ? '' : $buyer->firstname,
            '{customer.lastname}'  => $buyer == null ? '' : $buyer->lastname,
            '{customer.company}'   => $buyer == null ? '' : $buyer->company,
        );

        $content = str_ireplace(
            array_keys($filters),
            array_values($filters),
            Configuration::get(static::MOLLIE_DESCRIPTION)
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
        $supportedLanguages = array(
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
        );

        $supportedLocales = array(
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
        );

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
     * @since 3.0.0
     * @throws ErrorException
     */
    public function ajaxProcessDownloadUpdate()
    {
        header('Content-Type: application/json;charset=UTF-8');
        try {
            $latestVersion = $this->getLatestVersion();
        } catch (PrestaShopException $e) {
            die(json_encode(array(
                'success' => false,
                'message' => $this->l('Unable to retieve info about the latest version'),
            )));
        } catch (SmartyException $e) {
            die(json_encode(array(
                'success' => false,
                'message' => $this->l('Unable to retieve info about the latest version'),
            )));
        }
        if (version_compare(
            Tools::substr($latestVersion['version'], 1, Tools::strlen($latestVersion['version']) - 1),
            $this->version,
            '>'
        )) {
            // Then update
            die(json_encode(array(
                'success' => $this->downloadModuleFromLocation($this->name, $latestVersion['download']),
            )));
        } else {
            die(json_encode(array(
                'success' => false,
                'message' => $this->l('You are already running the latest version!'),
            )));
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

        die(json_encode(array(
            'success' => $result,
            'message' => isset($this->context->controller->errors[0]) ? $this->context->controller->errors[0] : '',
        )));
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

        die(json_encode(array(
            'success' => $result,
            'message' => isset($error) ? $error : '',
        )));
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
        $zipLocation = _PS_MODULE_DIR_.$moduleName.'.zip';
        if (@!file_exists($zipLocation)) {
            $curl = new \MollieModule\Curl\Curl();
            $curl->setOpt(CURLOPT_ENCODING, '');
            $curl->setOpt(CURLOPT_FOLLOWLOCATION, 1);
            if (!$curl->download($location, _PS_MODULE_DIR_.'mollie-update.zip')) {
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
        if (@file_exists(_PS_MODULE_DIR_.'mollie-update.zip')) {
            return $this->extractModuleArchive($this->name, _PS_MODULE_DIR_.'mollie-update.zip');
        }

        return false;
    }

    /**
     * Extracts a module archive to the `modules` folder
     *
     * @param string $moduleName Module name
     * @param string $file       File source location
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
        $zipFolders = array();
        $tmpFolder = _PS_MODULE_DIR_.$moduleName.md5(time());

        if (@!file_exists($file)) {
            $this->context->controller->errors[] = $this->l('Module archive could not be downloaded');

            return false;
        }

        $success = false;
        if (Tools::substr($file, -4) === '.zip') {
            if (Tools::ZipExtract($file, $tmpFolder) && file_exists($tmpFolder.DIRECTORY_SEPARATOR.$moduleName)) {
                if (file_exists(_PS_MODULE_DIR_.$moduleName)) {
                    $report = '';
                    if (!static::testDir(_PS_MODULE_DIR_.$moduleName, true, $report, true)) {
                        $this->recursiveDeleteOnDisk($tmpFolder);
                        @unlink(_PS_MODULE_DIR_.$moduleName.'.zip');

                        return false;
                    }
                    $this->recursiveDeleteOnDisk(_PS_MODULE_DIR_.$moduleName);
                }
                if (@rename($tmpFolder.DIRECTORY_SEPARATOR.$moduleName, _PS_MODULE_DIR_.$moduleName)) {
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
                if (!in_array($folder, array('.', '..', '.svn', '.git', '__MACOSX')) && !Module::getInstanceByName($folder)) {
                    $this->recursiveDeleteOnDisk(_PS_MODULE_DIR_.$folder);
                }
            }
        }

        @unlink($file);
        @unlink(_PS_MODULE_DIR_.$moduleName.'backup');
        $this->recursiveDeleteOnDisk($tmpFolder);

        die(json_encode(array(
            'success' => $success,
        )));
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
                    if (filetype($dir.'/'.$object) === 'dir') {
                        $this->recursiveDeleteOnDisk($dir.'/'.$object);
                    } else {
                        @unlink($dir.'/'.$object);
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
     * @since 3.0.0
     */
    protected function getMethodsForCheckout()
    {
        if (!Configuration::get(static::MOLLIE_API_KEY)) {
            return array();
        }

        $iso = Tools::strtolower($this->context->currency->iso_code);
        $methods = @json_decode(Configuration::get(static::METHODS_CONFIG), true);
        if (empty($methods)) {
            $methods = array();
        }
        $countryCode = Tools::strtolower($this->context->country->iso_code);
        $unavailableMethods = array();
        foreach (static::$defaultMethodAvailability as $methodName => $countries) {
            if (!in_array($methodName, array('klarnapaylater', 'klarnasliceit'))) {
                continue;
            }
            if (!in_array($countryCode, $countries)) {
                $unavailableMethods[] = $methodName;
            }
        }

        foreach ($methods as $index => $method) {
            if (!isset(static::$methodCurrencies[$method['id']])
                || !in_array($iso, static::$methodCurrencies[$method['id']])
                || empty($method['enabled'])
                || isset($method['available']) && !$method['available']
                || in_array($method['id'], $unavailableMethods)
            ) {
                unset($methods[$index]);
            }
        }

        return $methods;
    }

    /**
     * Get payment methods to show on the configuration page
     *
     * @param bool $active Active methods only
     *
     * @return array
     *
     * @throws PrestaShopException
     *
     * @throws \MollieModule\Mollie\Api\Exceptions\ApiException
     *
     * @since 3.0.0
     */
    protected function getMethodsForConfig($active = false)
    {
        $notAvailable = array();
        $apiMethods = $this->api->methods->all(array('resource' => 'orders', 'include' => 'issuers'))->getArrayCopy();
        if (static::selectedApi() === static::MOLLIE_PAYMENTS_API) {
            $paymentApiMethods = array_map(function ($item) {
                return $item->id;
            }, $this->api->methods->all()->getArrayCopy());
            $orderApiMethods = array_map(function ($item) {
                return $item->id;
            }, $apiMethods);
            $notAvailable = array_diff($orderApiMethods, $paymentApiMethods);
        }
        if (!count($apiMethods)) {
            return array();
        }

        $dbMethods = @json_decode(Configuration::get(static::METHODS_CONFIG), true);
        $keys = array('id', 'name', 'enabled', 'image', 'issuers', 'position');
        foreach ($dbMethods as $index => $dbMethod) {
            if (count(array_intersect($keys, array_keys($dbMethod))) !== count($keys)) {
                unset($dbMethods[$index]);
            }
        }
        
        if (!is_array($dbMethods)) {
            $dbMethods = array();
            $configMethods = array();
        } else {
            $configMethods = array();
            foreach ($dbMethods as $dbMethod) {
                $configMethods[$dbMethod['id']] = $dbMethod;
            }
        }

        $methodsFromDb = array_keys($configMethods);
        $methods = array();
        $deferredMethods = array();
        foreach ($apiMethods as $apiMethod) {
            if (!in_array($apiMethod->id, $methodsFromDb) || !isset($configMethods[$apiMethod->id]['position'])) {
                $deferredMethods[] = array(
                    'id'        => $apiMethod->id,
                    'name'      => $apiMethod->description,
                    'enabled'   => true,
                    'available' => !in_array($apiMethod->id, $notAvailable),
                    'image'     => (array) $apiMethod->image,
                    'issuers'   => $apiMethod->issuers,
                );
            } else {
                $methods[$configMethods[$apiMethod->id]['position']] = array(
                    'id'        => $apiMethod->id,
                    'name'      => $apiMethod->description,
                    'enabled'   => $configMethods[$apiMethod->id]['enabled'],
                    'available' => !in_array($apiMethod->id, $notAvailable),
                    'image'     => (array) $apiMethod->image,
                    'issuers'   => $apiMethod->issuers,
                );
            }
        }
        $availableApiMethods = array_column(array_map(function ($apiMethod) {
            return (array) $apiMethod;
        }, $apiMethods), 'id');
        if (in_array('creditcard', $availableApiMethods)) {
            foreach (array('cartasi' => 'CartaSi', 'cartesbancaires' => 'Cartes Bancaires') as $id => $name) {
                if (!in_array($id, array_column($dbMethods, 'id'))) {
                    $deferredMethods[] = array(
                        'id'        => $id,
                        'name'      => $name,
                        'enabled'   => true,
                        'available' => !in_array($id, $notAvailable),
                        'image'     => array(
                            'size1x' => static::getMediaPath("{$this->_path}views/img/{$id}_small.png"),
                            'size2x' => static::getMediaPath("{$this->_path}views/img/{$id}.png"),
                            'svg'    => static::getMediaPath("{$this->_path}views/img/{$id}.svg"),
                        ),
                        'issuers'   => null,
                    );
                } else {
                    $cc = $dbMethods[array_search('creditcard', array_column($dbMethods, 'id'))];
                    $thisMethod = $dbMethods[array_search($id, array_column($dbMethods, 'id'))];
                    $methods[$configMethods[$id]['position']] = array(
                        'id'        => $id,
                        'name'      => $name,
                        'enabled'   => !empty($thisMethod['enabled']) && !empty($cc['enabled']),
                        'available' => !in_array($id, $notAvailable),
                        'image'     => array(
                            'size1x' => static::getMediaPath("{$this->_path}views/img/{$id}_small.png"),
                            'size2x' => static::getMediaPath("{$this->_path}views/img/{$id}.png"),
                            'svg'    => static::getMediaPath("{$this->_path}views/img/{$id}.svg"),
                        ),
                        'issuers'   => null,
                    );
                }
            }
        }
        ksort($methods);
        $methods = array_values($methods);
        foreach ($deferredMethods as $deferredMethod) {
            $methods[] = $deferredMethod;
        }
        if ($active) {
            foreach ($methods as $index => $method) {
                if (!$method['enabled']) {
                    unset($methods[$index]);
                }
            }
        }

        return $methods;
    }

    /**
     * Test if directory is writable
     *
     * @param string $dir      Directory path, absolute or relative
     * @param bool   $recursive
     * @param null   $fullReport
     * @param bool   $absolute Is absolute path to directory
     *
     * @return bool
     *
     * @since 3.0.2
     */
    public static function testDir($dir, $recursive = false, &$fullReport = null, $absolute = false)
    {
        if ($absolute) {
            $absoluteDir = $dir;
        } else {
            $absoluteDir = rtrim(_PS_ROOT_DIR_, '\\/').DIRECTORY_SEPARATOR.trim($dir, '\\/');
        }

        if (!file_exists($absoluteDir)) {
            $fullReport = sprintf('Directory %s does not exist.', $absoluteDir);

            return false;
        }

        if (!is_writable($absoluteDir)) {
            $fullReport = sprintf('Directory %s is not writable.', $absoluteDir);

            return false;
        }

        if ($recursive) {
            foreach (scandir($absoluteDir, SCANDIR_SORT_NONE) as $item) {
                $path = $absoluteDir.DIRECTORY_SEPARATOR.$item;

                if (in_array($item, array('.', '..', '.git'))
                    || is_link($path)) {
                    continue;
                }

                if (is_dir($path)) {
                    if (!static::testDir($path, $recursive, $fullReport, true)) {
                        return false;
                    }
                }

                if (!is_writable($path)) {
                    $fullReport = sprintf('File %s is not writable.', $path);

                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Post process tags in (translated) strings
     *
     * @param string $string
     * @param array  $tags
     *
     * @return string
     *
     * @since 3.2.0
     */
    public static function ppTags($string, $tags = array())
    {
        // If tags were explicitly provided, we want to use them *after* the translation string is escaped.
        if (!empty($tags)) {
            foreach ($tags as $index => $tag) {
                // Make positions start at 1 so that it behaves similar to the %1$d etc. sprintf positional params
                $position = $index + 1;
                // extract tag name
                $match = array();
                if (preg_match('/^\s*<\s*(\w+)/', $tag, $match)) {
                    $opener = $tag;
                    $closer = '</'.$match[1].'>';

                    $string = str_replace('['.$position.']', $opener, $string);
                    $string = str_replace('[/'.$position.']', $closer, $string);
                    $string = str_replace('['.$position.'/]', $opener.$closer, $string);
                }
            }
        }

        return $string;
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
     * @param string $user
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
            static::MOLLIE_RESELLER_PARTNER_ID,
            static::MOLLIE_RESELLER_PROFILE_KEY,
            static::MOLLIE_RESELLER_APP_SECRET
        );
        $simplexml = $mollie->accountCreate(
            $email,
            array(
                'name'         => $name,
                'company_name' => $company,
                'address'      => $address,
                'zipcode'      => $zipcode,
                'city'         => $city,
                'country'      => $country,
                'email'        => $email,
            )
        );

        if (empty($simplexml->success) && isset($simplexml->resultmessage) && isset($simplexml->resultcode)) {
            throw new Mollie_Exception($simplexml->resultmessage, $simplexml->resultcode);
        }

        return !empty($simplexml->success);
    }

    /**
     * Validate an order in database
     * Function called from a payment module.
     *
     * @param int    $idCart
     * @param int    $idOrderState
     * @param float  $amountPaid    Amount really paid by customer (in the default currency)
     * @param string $paymentMethod Payment method (eg. 'Credit card')
     * @param null   $message       Message to attach to order
     * @param array  $extraVars
     * @param null   $currencySpecial
     * @param bool   $dontTouchAmount
     * @param bool   $secureKey
     * @param Shop   $shop
     *
     * @return bool
     *
     * @since 3.3.0
     *
     * @throws PrestaShopException
     * @throws Adapter_Exception
     * @throws SmartyException
     * @throws \PrestaShop\PrestaShop\Adapter\CoreException
     *
     * This function replaces the PaymentModule::validateOrder method in order to support the new Cart => Order flow.
     * This flow is applicable only to the Orders API.
     *
     * PrestaShop 1.5 function
     */
    public function validateMollieOrderLegacy(
        $idCart,
        $idOrderState,
        $amountPaid,
        $paymentMethod = 'Unknown',
        $message = null,
        $extraVars = array(),
        $currencySpecial = null,
        $dontTouchAmount = false,
        $secureKey = false,
        Shop $shop = null
    ) {
        $this->context->cart = new Cart($idCart);
        $this->context->customer = new Customer($this->context->cart->id_customer);
        $this->context->language = new Language($this->context->cart->id_lang);
        $this->context->shop = ($shop ? $shop : new Shop($this->context->cart->id_shop));
        if (method_exists('ShopUrl', 'resetMainDomainCache')) {
            ShopUrl::resetMainDomainCache();
        }
        $idCurrency = $currencySpecial ? (int) $currencySpecial : (int) $this->context->cart->id_currency;
        $this->context->currency = new Currency($idCurrency, null, $this->context->shop->id);
        if (Configuration::get('PS_TAX_ADDRESS_TYPE') == 'id_address_delivery') {
            $context_country = $this->context->country;
        }
        $orderStatus = new OrderState((int) $idOrderState, (int) $this->context->language->id);
        if (!Validate::isLoadedObject($orderStatus)) {
            throw new PrestaShopException('Can\'t load Order state status');
        }
        if (!$this->active) {
            die(Tools::displayError());
        }
        // Does order already exists ?
        if (Validate::isLoadedObject($this->context->cart) && $this->context->cart->OrderExists() == false) {
            if ($secureKey !== false && $secureKey != $this->context->cart->secure_key) {
                die(Tools::displayError());
            }
            // For each package, generate an order
            $deliveryOptionList = $this->context->cart->getDeliveryOptionList();
            $packageList = $this->context->cart->getPackageList();
            $cartDeliveryOption = $this->context->cart->getDeliveryOption();
            // If some delivery options are not defined, or not valid, use the first valid option
            foreach ($deliveryOptionList as $idAddress => $package) {
                if (!isset($cartDeliveryOption[$idAddress]) || !array_key_exists($cartDeliveryOption[$idAddress], $package)) {
                    foreach ($package as $key => $val) {
                        $cartDeliveryOption[$idAddress] = $key;
                        break;
                    }
                }
            }
            $orderList = array();
            $orderDetailList = array();
            $reference = Order::generateReference();
            $this->currentOrderReference = $reference;
            $orderCreationFailed = false;
            $cartTotalPaid = (float) Tools::ps_round((float) $this->context->cart->getOrderTotal(true, Cart::BOTH), static::API_ROUNDING_PRECISION);
            if (method_exists('Cart', 'getPackageIdWarehouse')) {
                foreach ($cartDeliveryOption as $idAddress => $keyCarriers) {
                    foreach ($deliveryOptionList[$idAddress][$keyCarriers]['carrier_list'] as $idCarrier => $data) {
                        foreach ($data['package_list'] as $idPackage) {
                            // Rewrite the id_warehouse
                            $packageList[$idAddress][$idPackage]['id_warehouse'] = (int) $this->context->cart->getPackageIdWarehouse($packageList[$idAddress][$idPackage], (int) $idCarrier);
                            $packageList[$idAddress][$idPackage]['id_carrier'] = $idCarrier;
                        }
                    }
                }
            }
            // Make sure CarRule caches are empty
            if (method_exists('CartRule', 'cleanCache')) {
                CartRule::cleanCache();
            }

            foreach ($packageList as $idAddress => $packageByAddress) {
                foreach ($packageByAddress as $idPackage => $package) {
                    $order = new Order();
                    $order->product_list = $package['product_list'];

                    if (Configuration::get('PS_TAX_ADDRESS_TYPE') == 'id_address_delivery') {
                        $address = new Address($idAddress);
                        $this->context->country = new Country($address->id_country, $this->context->cart->id_lang);
                    }

                    $carrier = null;
                    if (!$this->context->cart->isVirtualCart() && isset($package['id_carrier'])) {
                        $carrier = new Carrier($package['id_carrier'], $this->context->cart->id_lang);
                        $order->id_carrier = (int) $carrier->id;
                        $idCarrier = (int) $carrier->id;
                    } else {
                        $order->id_carrier = 0;
                        $idCarrier = 0;
                    }

                    $order->id_customer = (int) $this->context->cart->id_customer;
                    $order->id_address_invoice = (int) $this->context->cart->id_address_invoice;
                    $order->id_address_delivery = (int) $idAddress;
                    $order->id_currency = $this->context->currency->id;
                    $order->id_lang = (int) $this->context->cart->id_lang;
                    $order->id_cart = (int) $this->context->cart->id;
                    $order->reference = $reference;
                    $order->id_shop = (int) $this->context->shop->id;
                    $order->id_shop_group = (int) $this->context->shop->id_shop_group;
                    $order->secure_key = ($secureKey ? pSQL($secureKey) : pSQL($this->context->customer->secure_key));
                    $order->payment = $paymentMethod;
                    if (isset($this->name)) {
                        $order->module = $this->name;
                    }
                    $order->recyclable = $this->context->cart->recyclable;
                    $order->gift = (int) $this->context->cart->gift;
                    $order->gift_message = $this->context->cart->gift_message;
                    $order->mobile_theme = $this->context->cart->mobile_theme;
                    $order->conversion_rate = $this->context->currency->conversion_rate;
                    $amountPaid = !$dontTouchAmount ? Tools::ps_round((float) $amountPaid, 2) : $amountPaid;
                    $order->total_paid_real = 0;

                    $order->total_products = (float) $this->context->cart->getOrderTotal(false, Cart::ONLY_PRODUCTS, $order->product_list, $idCarrier);
                    $order->total_products_wt = (float) $this->context->cart->getOrderTotal(true, Cart::ONLY_PRODUCTS, $order->product_list, $idCarrier);
                    $order->total_discounts_tax_excl = (float) abs($this->context->cart->getOrderTotal(false, Cart::ONLY_DISCOUNTS, $order->product_list, $idCarrier));
                    $order->total_discounts_tax_incl = (float) abs($this->context->cart->getOrderTotal(true, Cart::ONLY_DISCOUNTS, $order->product_list, $idCarrier));
                    $order->total_discounts = $order->total_discounts_tax_incl;
                    $order->total_shipping_tax_excl = (float) $this->context->cart->getPackageShippingCost((int) $idCarrier, false, null, $order->product_list);
                    $order->total_shipping_tax_incl = (float) $this->context->cart->getPackageShippingCost((int) $idCarrier, true, null, $order->product_list);
                    $order->total_shipping = $order->total_shipping_tax_incl;

                    if (!is_null($carrier) && Validate::isLoadedObject($carrier)) {
                        $order->carrier_tax_rate = $carrier->getTaxesRate(new Address($this->context->cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')}));
                    }
                    $order->total_wrapping_tax_excl = (float) abs($this->context->cart->getOrderTotal(false, Cart::ONLY_WRAPPING, $order->product_list, $idCarrier));
                    $order->total_wrapping_tax_incl = (float) abs($this->context->cart->getOrderTotal(true, Cart::ONLY_WRAPPING, $order->product_list, $idCarrier));
                    $order->total_wrapping = $order->total_wrapping_tax_incl;
                    $order->total_paid_tax_excl = (float) Tools::ps_round((float) $this->context->cart->getOrderTotal(false, Cart::BOTH, $order->product_list, $idCarrier), 2);
                    $order->total_paid_tax_incl = (float) Tools::ps_round((float) $this->context->cart->getOrderTotal(true, Cart::BOTH, $order->product_list, $idCarrier), 2);
                    $order->total_paid = $order->total_paid_tax_incl;
                    $order->invoice_date = '0000-00-00 00:00:00';
                    $order->delivery_date = '0000-00-00 00:00:00';
                    // Creating order
                    $result = $order->add();
                    if (!$result) {
                        throw new PrestaShopException('Can\'t save Order');
                    }
                    // Amount paid by customer is not the right one -> Status = payment error
                    // We don't use the following condition to avoid the float precision issues : http://www.php.net/manual/en/language.types.float.php
                    // if ($order->total_paid != $order->total_paid_real)
                    // We use number_format in order to compare two string
                    if ($orderStatus->logable
                        && number_format($cartTotalPaid, 2) != number_format($amountPaid, 2)
                    ) {
                        $idOrderState = Configuration::get('PS_OS_ERROR');
                    }
                    $orderList[] = $order;
                    // Insert new Order detail list using cart for the current order
                    $orderDetail = new OrderDetail(null, null, $this->context);
                    $orderDetail->createList($order, $this->context->cart, $idOrderState, $order->product_list, 0, true, $packageList[$idAddress][$idPackage]['id_warehouse']);
                    $orderDetailList[] = $orderDetail;
                    // Adding an entry in order_carrier table
                    if (!is_null($carrier)) {
                        $orderCarrier = new OrderCarrier();
                        $orderCarrier->id_order = (int) $order->id;
                        $orderCarrier->id_carrier = (int) $idCarrier;
                        $orderCarrier->weight = (float) $order->getTotalWeight();
                        $orderCarrier->shipping_cost_tax_excl = (float) $order->total_shipping_tax_excl;
                        $orderCarrier->shipping_cost_tax_incl = (float) $order->total_shipping_tax_incl;
                        $orderCarrier->add();
                    }
                }
            }

            // The country can only change if the address used for the calculation is the delivery address, and if multi-shipping is activated
            if (Configuration::get('PS_TAX_ADDRESS_TYPE') == 'id_address_delivery') {
                $this->context->country = $context_country;
            }
            // Register Payment only if the order status validate the order
            if ($orderStatus->logable) {
                // $order is the last order loop in the foreach
                // The method addOrderPayment of the class Order make a create a paymentOrder
                //     linked to the order reference and not to the order id
                if (isset($extraVars['transaction_id'])) {
                    $transactionId = $extraVars['transaction_id'];
                } else {
                    $transactionId = null;
                }

                if (!$order->addOrderPayment($amountPaid, null, $transactionId)) {
                    throw new PrestaShopException('Can\'t save Order Payment');
                }
            }
            // Next !
            $onlyOneGift = false;
            $cartRuleUsed = array();
            $products = $this->context->cart->getProducts();
            $cartRules = $this->context->cart->getCartRules();

            // Make sure CarRule caches are empty
            CartRule::cleanCache();

            foreach ($orderDetailList as $key => $orderDetail) {
                $order = $orderList[$key];
                if (!$orderCreationFailed && isset($order->id)) {
                    if (!$secureKey) {
                        $message .= '<br />'.Tools::displayError('Warning: the secure key is empty, check your payment account before validation');
                    }
                    // Optional message to attach to this order
                    if (isset($message) & !empty($message)) {
                        $msg = new Message();
                        $message = strip_tags($message, '<br>');
                        if (Validate::isCleanHtml($message)) {
                            $msg->message = $message;
                            $msg->id_order = (int) $order->id;
                            $msg->private = 1;
                            $msg->add();
                        }
                    }
                    // Insert new Order detail list using cart for the current order
                    //$orderDetail = new OrderDetail(null, null, $this->context);
                    //$orderDetail->createList($order, $this->context->cart, $id_order_state);
                    // Construct order detail table for the email
                    $productsList = '';
                    $virtualProduct = true;
                    foreach ($order->product_list as $key => $product) {
                        $price = Product::getPriceStatic((int) $product['id_product'], false, ($product['id_product_attribute'] ? (int) $product['id_product_attribute'] : null), 6, null, false, true, $product['cart_quantity'], false, (int) $order->id_customer, (int) $order->id_cart, (int) $order->{Configuration::get('PS_TAX_ADDRESS_TYPE')});
                        $priceWt = Product::getPriceStatic((int) $product['id_product'], true, ($product['id_product_attribute'] ? (int) $product['id_product_attribute'] : null), 2, null, false, true, $product['cart_quantity'], false, (int) $order->id_customer, (int) $order->id_cart, (int) $order->{Configuration::get('PS_TAX_ADDRESS_TYPE')});
                        $customizationQuantity = 0;
                        $customizedDatas = Product::getAllCustomizedDatas((int) $order->id_cart);
                        if (isset($customizedDatas[$product['id_product']][$product['id_product_attribute']])) {
                            $customizationText = '';
                            foreach ($customizedDatas[$product['id_product']][$product['id_product_attribute']][$order->id_address_delivery] as $customization) {
                                if (isset($customization['datas'][Product::CUSTOMIZE_TEXTFIELD])) {
                                    foreach ($customization['datas'][Product::CUSTOMIZE_TEXTFIELD] as $text) {
                                        $customizationText .= $text['name'].': '.$text['value'].'<br />';
                                    }
                                }
                                if (isset($customization['datas'][Product::CUSTOMIZE_FILE])) {
                                    $customizationText .= sprintf(Tools::displayError('%d image(s)'), count($customization['datas'][Product::CUSTOMIZE_FILE])).'<br />';
                                }
                                $customizationText .= '---<br />';
                            }
                            $customizationText = Tools::rtrimString($customizationText, '---<br />');
                            $customizationQuantity = (int) $product['customization_quantity'];
                            $productsList .=
                                '<tr style="background-color: '.($key % 2 ? '#DDE2E6' : '#EBECEE').';">
								<td style="padding: 0.6em 0.4em;width: 15%;">'.$product['reference'].'</td>
								<td style="padding: 0.6em 0.4em;width: 30%;"><strong>'.$product['name'].(isset($product['attributes']) ? ' - '.$product['attributes'] : '').' - '.Tools::displayError('Customized').(!empty($customizationText) ? ' - '.$customizationText : '').'</strong></td>
								<td style="padding: 0.6em 0.4em; width: 20%;">'.Tools::displayPrice(Product::getTaxCalculationMethod() == PS_TAX_EXC ? Tools::ps_round($price, 2) : $priceWt, $this->context->currency, false).'</td>
								<td style="padding: 0.6em 0.4em; width: 15%;">'.$customizationQuantity.'</td>
								<td style="padding: 0.6em 0.4em; width: 20%;">'.Tools::displayPrice($customizationQuantity * (Product::getTaxCalculationMethod() == PS_TAX_EXC ? Tools::ps_round($price, 2) : $priceWt), $this->context->currency, false).'</td>
							</tr>';
                        }
                        if (!$customizationQuantity || (int) $product['cart_quantity'] > $customizationQuantity) {
                            $productsList .=
                                '<tr style="background-color: '.($key % 2 ? '#DDE2E6' : '#EBECEE').';">
								<td style="padding: 0.6em 0.4em;width: 15%;">'.$product['reference'].'</td>
								<td style="padding: 0.6em 0.4em;width: 30%;"><strong>'.$product['name'].(isset($product['attributes']) ? ' - '.$product['attributes'] : '').'</strong></td>
								<td style="padding: 0.6em 0.4em; width: 20%;">'.Tools::displayPrice(Product::getTaxCalculationMethod((int) $this->context->customer->id) == PS_TAX_EXC ? Tools::ps_round($price, 2) : $priceWt, $this->context->currency, false).'</td>
								<td style="padding: 0.6em 0.4em; width: 15%;">'.((int) $product['cart_quantity'] - $customizationQuantity).'</td>
								<td style="padding: 0.6em 0.4em; width: 20%;">'.Tools::displayPrice(((int) $product['cart_quantity'] - $customizationQuantity) * (Product::getTaxCalculationMethod() == PS_TAX_EXC ? Tools::ps_round($price, 2) : $priceWt), $this->context->currency, false).'</td>
							</tr>';
                        }
                        // Check if is not a virutal product for the displaying of shipping
                        if (!$product['is_virtual']) {
                            $virtualProduct &= false;
                        }
                    } // end foreach ($products)
                    $cartRulesList = '';
                    $totalReductionValueTi = 0;
                    $totalReductionValueTex = 0;
                    foreach ($cartRules as $cartRule) {
                        $package = array('id_carrier' => $order->id_carrier, 'id_address' => $order->id_address_delivery, 'products' => $order->product_list);
                        $values = array(
                            'tax_incl' => $cartRule['obj']->getContextualValue(true, $this->context, CartRule::FILTER_ACTION_ALL_NOCAP, $package),
                            'tax_excl' => $cartRule['obj']->getContextualValue(false, $this->context, CartRule::FILTER_ACTION_ALL_NOCAP, $package),
                        );
                        // If the reduction is not applicable to this order, then continue with the next one
                        if (!$values['tax_excl']) {
                            continue;
                        }
                        /* IF
                         * - This is not multi-shipping
                         * - The value of the voucher is greater than the total of the order
                         * - Partial use is allowed
                         * - This is an "amount" reduction, not a reduction in % or a gift
                         * THEN
                         * The voucher is cloned with a new value corresponding to the remainder
                         */
                        if (count($orderList) == 1
                            && $values['tax_incl'] > ($order->total_products_wt - $totalReductionValueTi)
                            && $cartRule['obj']->partial_use == 1
                            && $cartRule['obj']->reduction_amount > 0
                        ) {
                            // Create a new voucher from the original
                            $voucher = new CartRule($cartRule['obj']->id); // We need to instantiate the CartRule without lang parameter to allow saving it
                            unset($voucher->id);
                            // Set a new voucher code
                            $voucher->code = empty($voucher->code) ? Tools::substr(md5($order->id.'-'.$order->id_customer.'-'.$cartRule['obj']->id), 0, 16) : $voucher->code.'-2';
                            if (preg_match('/\-([0-9]{1,2})\-([0-9]{1,2})$/', $voucher->code, $matches) && $matches[1] == $matches[2]) {
                                $voucher->code = preg_replace('/'.$matches[0].'$/', '-'.((int) ($matches[1]) + 1), $voucher->code);
                            }
                            // Set the new voucher value
                            if ($voucher->reduction_tax) {
                                $voucher->reduction_amount = $values['tax_incl'] - ($order->total_products_wt - $totalReductionValueTi);
                                // Add total shipping amout only if reduction amount > total shipping
                                if ($voucher->free_shipping == 1
                                    && $voucher->reduction_amount >= $order->total_shipping_tax_incl
                                ) {
                                    $voucher->reduction_amount -= $order->total_shipping_tax_incl;
                                }
                            } else {
                                $voucher->reduction_amount = $values['tax_excl'] - ($order->total_products - $totalReductionValueTex);
                                // Add total shipping amout only if reduction amount > total shipping
                                if ($voucher->free_shipping == 1
                                    && $voucher->reduction_amount >= $order->total_shipping_tax_excl
                                ) {
                                    $voucher->reduction_amount -= $order->total_shipping_tax_excl;
                                }
                            }
                            $voucher->id_customer = $order->id_customer;
                            $voucher->quantity = 1;
                            $voucher->quantity_per_user = 1;
                            $voucher->free_shipping = 0;
                            if ($voucher->add()) {
                                // If the voucher has conditions, they are now copied to the new voucher
                                CartRule::copyConditions($cartRule['obj']->id, $voucher->id);
                                $params = array(
                                    '{voucher_amount}' => Tools::displayPrice($voucher->reduction_amount, $this->context->currency, false),
                                    '{voucher_num}'    => $voucher->code,
                                    '{firstname}'      => $this->context->customer->firstname,
                                    '{lastname}'       => $this->context->customer->lastname,
                                    '{id_order}'       => $order->reference,
                                    '{order_name}'     => $order->getUniqReference(),
                                );
                                Mail::Send(
                                    (int) $order->id_lang,
                                    'voucher',
                                    sprintf(Mail::l('New voucher regarding your order %s', (int) $order->id_lang), $order->reference),
                                    $params,
                                    $this->context->customer->email,
                                    $this->context->customer->firstname.' '.$this->context->customer->lastname,
                                    null, null, null, null, _PS_MAIL_DIR_, false, (int) $order->id_shop
                                );
                            }
                            $values['tax_incl'] -= $values['tax_incl'] - $order->total_products_wt;
                            $values['tax_excl'] -= $values['tax_excl'] - $order->total_products;
                        }
                        $totalReductionValueTi += $values['tax_incl'];
                        $totalReductionValueTex += $values['tax_excl'];
                        $order->addCartRule($cartRule['obj']->id, $cartRule['obj']->name, $values, 0, $cartRule['obj']->free_shipping);
                        if ($idOrderState != Configuration::get('PS_OS_ERROR')
                            && $idOrderState != Configuration::get('PS_OS_CANCELED')
                            && !in_array($cartRule['obj']->id, $cartRuleUsed)
                        ) {
                            $cartRuleUsed[] = $cartRule['obj']->id;
                            // Create a new instance of Cart Rule without id_lang, in order to update its quantity
                            $cartRuleToUpdate = new CartRule($cartRule['obj']->id);
                            $cartRuleToUpdate->quantity = max(0, $cartRuleToUpdate->quantity - 1);
                            $cartRuleToUpdate->update();
                        }
                        $cartRulesList .= '
						<tr>
							<td colspan="4" style="padding:0.6em 0.4em;text-align:right">'.Tools::displayError('Voucher name:').' '.$cartRule['obj']->name.'</td>
							<td style="padding:0.6em 0.4em;text-align:right">'.($values['tax_incl'] != 0.00 ? '-' : '').Tools::displayPrice($values['tax_incl'], $this->context->currency, false).'</td>
						</tr>';
                    }
                    // Specify order id for message
                    $oldMessage = Message::getMessageByCartId((int) $this->context->cart->id);
                    if ($oldMessage) {
                        $updateMessage = new Message((int) $oldMessage['id_message']);
                        $updateMessage->id_order = (int) $order->id;
                        $updateMessage->update();
                        // Add this message in the customer thread
                        $customerThread = new CustomerThread();
                        $customerThread->id_contact = 0;
                        $customerThread->id_customer = (int) $order->id_customer;
                        $customerThread->id_shop = (int) $this->context->shop->id;
                        $customerThread->id_order = (int) $order->id;
                        $customerThread->id_lang = (int) $this->context->language->id;
                        $customerThread->email = $this->context->customer->email;
                        $customerThread->status = 'open';
                        $customerThread->token = Tools::passwdGen(12);
                        $customerThread->add();
                        $customerMessage = new CustomerMessage();
                        $customerMessage->id_customer_thread = $customerThread->id;
                        $customerMessage->id_employee = 0;
                        $customerMessage->message = $updateMessage->message;
                        $customerMessage->private = 0;
                        if (!$customerMessage->add()) {
                            $this->context->controller->errors[] = Tools::displayError('An error occurred while saving message');
                        }
                    }
                    // Hook validate order
                    Hook::exec('actionValidateOrder', array(
                        'cart'        => $this->context->cart,
                        'order'       => $order,
                        'customer'    => $this->context->customer,
                        'currency'    => $this->context->currency,
                        'orderStatus' => $orderStatus,
                    ));
                    foreach ($this->context->cart->getProducts() as $product) {
                        if ($orderStatus->logable) {
                            ProductSale::addProductSale((int) $product['id_product'], (int) $product['cart_quantity']);
                        }
                    }
                    // Set the order state
                    $this->setOrderStatus($order, $idOrderState, true);
                    // Switch to back order if needed
                    if (Configuration::get('PS_STOCK_MANAGEMENT') && $orderDetail->getStockState()) {
                        $this->setOrderStatus($order, Configuration::get('PS_OS_OUTOFSTOCK'), true);
                    }
                    unset($orderDetail);
                    // Order is reloaded because the status just changed
                    $order = new Order($order->id);
                    // Send an e-mail to customer (one order = one email)
                    if ($idOrderState != Configuration::get('PS_OS_ERROR') && $idOrderState != Configuration::get('PS_OS_CANCELED') && $this->context->customer->id) {
                        $invoice = new Address($order->id_address_invoice);
                        $delivery = new Address($order->id_address_delivery);
                        $delivery_state = $delivery->id_state ? new State($delivery->id_state) : false;
                        $invoice_state = $invoice->id_state ? new State($invoice->id_state) : false;
                        $data = array(
                            '{firstname}'            => $this->context->customer->firstname,
                            '{lastname}'             => $this->context->customer->lastname,
                            '{email}'                => $this->context->customer->email,
                            '{delivery_block_txt}'   => $this->_getFormatedAddress($delivery, "\n"),
                            '{invoice_block_txt}'    => $this->_getFormatedAddress($invoice, "\n"),
                            '{delivery_block_html}'  => $this->_getFormatedAddress($delivery, '<br />', array(
                                'firstname' => '<span style="font-weight:bold;">%s</span>',
                                'lastname'  => '<span style="font-weight:bold;">%s</span>',
                            )),
                            '{invoice_block_html}'   => $this->_getFormatedAddress($invoice, '<br />', array(
                                'firstname' => '<span style="font-weight:bold;">%s</span>',
                                'lastname'  => '<span style="font-weight:bold;">%s</span>',
                            )),
                            '{delivery_company}'     => $delivery->company,
                            '{delivery_firstname}'   => $delivery->firstname,
                            '{delivery_lastname}'    => $delivery->lastname,
                            '{delivery_address1}'    => $delivery->address1,
                            '{delivery_address2}'    => $delivery->address2,
                            '{delivery_city}'        => $delivery->city,
                            '{delivery_postal_code}' => $delivery->postcode,
                            '{delivery_country}'     => $delivery->country,
                            '{delivery_state}'       => $delivery->id_state ? $delivery_state->name : '',
                            '{delivery_phone}'       => ($delivery->phone) ? $delivery->phone : $delivery->phone_mobile,
                            '{delivery_other}'       => $delivery->other,
                            '{invoice_company}'      => $invoice->company,
                            '{invoice_vat_number}'   => $invoice->vat_number,
                            '{invoice_firstname}'    => $invoice->firstname,
                            '{invoice_lastname}'     => $invoice->lastname,
                            '{invoice_address2}'     => $invoice->address2,
                            '{invoice_address1}'     => $invoice->address1,
                            '{invoice_city}'         => $invoice->city,
                            '{invoice_postal_code}'  => $invoice->postcode,
                            '{invoice_country}'      => $invoice->country,
                            '{invoice_state}'        => $invoice->id_state ? $invoice_state->name : '',
                            '{invoice_phone}'        => ($invoice->phone) ? $invoice->phone : $invoice->phone_mobile,
                            '{invoice_other}'        => $invoice->other,
                            '{order_name}'           => $order->getUniqReference(),
                            '{date}'                 => Tools::displayDate(date('Y-m-d H:i:s'), null, 1),
                            '{carrier}'              => $virtualProduct ? Tools::displayError('No carrier') : $carrier->name,
                            '{payment}'              => Tools::substr($order->payment, 0, 32),
                            '{products}'             => $this->formatProductAndVoucherForEmail($productsList),
                            '{discounts}'            => $this->formatProductAndVoucherForEmail($cartRulesList),
                            '{total_paid}'           => Tools::displayPrice($order->total_paid, $this->context->currency, false),
                            '{total_products}'       => Tools::displayPrice($order->total_paid - $order->total_shipping - $order->total_wrapping + $order->total_discounts, $this->context->currency, false),
                            '{total_discounts}'      => Tools::displayPrice($order->total_discounts, $this->context->currency, false),
                            '{total_shipping}'       => Tools::displayPrice($order->total_shipping, $this->context->currency, false),
                            '{total_wrapping}'       => Tools::displayPrice($order->total_wrapping, $this->context->currency, false),
                            '{total_tax_paid}'       => Tools::displayPrice(($order->total_products_wt - $order->total_products) + ($order->total_shipping_tax_incl - $order->total_shipping_tax_excl), $this->context->currency, false),
                        );
                        if (is_array($extraVars)) {
                            $data = array_merge($data, $extraVars);
                        }
                        // Join PDF invoice
                        if ((int) Configuration::get('PS_INVOICE') && $orderStatus->invoice && $order->invoice_number) {
                            $pdf = new PDF($order->getInvoicesCollection(), PDF::TEMPLATE_INVOICE, $this->context->smarty);
                            $fileAttachement = array();
                            $fileAttachement['content'] = $pdf->render(false);
                            $fileAttachement['name'] = Configuration::get('PS_INVOICE_PREFIX', (int) $order->id_lang, null, $order->id_shop).sprintf('%06d', $order->invoice_number).'.pdf';
                            $fileAttachement['mime'] = 'application/pdf';
                        } else {
                            $fileAttachement = null;
                        }
                        if (Validate::isEmail($this->context->customer->email)) {
                            Mail::Send(
                                (int) $order->id_lang,
                                'order_conf',
                                Mail::l('Order confirmation', (int) $order->id_lang),
                                $data,
                                $this->context->customer->email,
                                $this->context->customer->firstname.' '.$this->context->customer->lastname,
                                null,
                                null,
                                $fileAttachement,
                                null, _PS_MAIL_DIR_, false, (int) $order->id_shop
                            );
                        }
                    }
                    // updates stock in shops
                    if (Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) {
                        $product_list = $order->getProducts();
                        foreach ($product_list as $product) {
                            // if the available quantities depends on the physical stock
                            if (StockAvailable::dependsOnStock($product['product_id'])) {
                                // synchronizes
                                StockAvailable::synchronize($product['product_id'], $order->id_shop);
                            }
                        }
                    }
                } else {
                    $error = Tools::displayError('Order creation failed');
                    Logger::addLog($error, 4, '0000002', 'Cart', (int) $order->id_cart);
                    die($error);
                }
            } // End foreach $order_detail_list
            // Use the last order as currentOrder
            $this->currentOrder = (int) $order->id;

            return true;
        } else {
            $error = Tools::displayError('Cart cannot be loaded or an order has already been placed using this cart');
            Logger::addLog($error, 4, '0000001', 'Cart', (int) $this->context->cart->id);
            die($error);
        }
    }

    /**
     * Validate an order in database
     * Function called from a payment module.
     *
     * @param int    $idCart
     * @param int    $idOrderState
     * @param float  $amountPaid    Amount really paid by customer (in the default currency)
     * @param string $paymentMethod Payment method (eg. 'Credit card')
     * @param null   $message       Message to attach to order
     * @param array  $extraVars
     * @param null   $currencySpecial
     * @param bool   $dontTouchAmount
     * @param bool   $secureKey
     * @param Shop   $shop
     *
     * @return bool
     *
     * @since 3.3.0
     *
     * @throws PrestaShopException
     * @throws Adapter_Exception
     * @throws SmartyException
     * @throws \PrestaShop\PrestaShop\Adapter\CoreException
     *
     * This function replaces the PaymentModule::validateOrder method in order to support the new Cart => Order flow.
     * This flow is applicable only to the Orders API.
     *
     * Hybrid PrestaShop 1.6/1.7 and thirty bees 1.0 function
     */
    public function validateMollieOrder(
        $idCart,
        $idOrderState,
        $amountPaid,
        $paymentMethod = 'Unknown',
        $message = null,
        $extraVars = array(),
        $currencySpecial = null,
        $dontTouchAmount = false,
        $secureKey = false,
        Shop $shop = null
    ) {
        if (version_compare(_PS_VERSION_, '1.6.0.7', '<')) {
            return $this->validateMollieOrderLegacy($idCart, $idOrderState, $amountPaid, $paymentMethod, $message, $extraVars, $currencySpecial, $dontTouchAmount, $secureKey, $shop);
        }

        if (version_compare(_PS_VERSION_, '1.6.0.9', '>=') && static::DEBUG_MODE) {
            Logger::addLog(__CLASS__.'::validateMollieOrder - Function called', 1, null, 'Cart', (int) $idCart, true);
        }
        if (!isset($this->context)) {
            $this->context = Context::getContext();
        }
        $this->context->cart = new Cart((int) $idCart);
        $this->context->customer = new Customer((int) $this->context->cart->id_customer);
        // The tax cart is loaded before the customer so re-cache the tax calculation method
        if (method_exists($this->context->cart, 'setTaxCalculationMethod')) {
            $this->context->cart->setTaxCalculationMethod();
        }
        $this->context->language = new Language((int) $this->context->cart->id_lang);
        $this->context->shop = ($shop ? $shop : new Shop((int) $this->context->cart->id_shop));
        if (method_exists('ShopUrl', 'resetMainDomainCache')) {
            ShopUrl::resetMainDomainCache();
        }
        $idCurrency = $currencySpecial ? (int) $currencySpecial : (int) $this->context->cart->id_currency;
        $this->context->currency = new Currency((int) $idCurrency, null, (int) $this->context->shop->id);
        if (Configuration::get('PS_TAX_ADDRESS_TYPE') == 'id_address_delivery') {
            $context_country = $this->context->country;
        }
        $orderStatus = new OrderState((int) $idOrderState, (int) $this->context->language->id);
        if (!Validate::isLoadedObject($orderStatus)) {
            Logger::addLog(__CLASS__.'::validateMollieOrder - Order Status cannot be loaded', 3, null, 'Cart', (int) $idCart, true);
            throw new PrestaShopException('Can\'t load Order status');
        }
        if (!$this->active) {
            Logger::addLog(__CLASS__.'::validateMollieOrder - Module is not active', 3, null, 'Cart', (int) $idCart, true);
            die(Tools::displayError());
        }
        // Does order already exists ?
        if (Validate::isLoadedObject($this->context->cart) && $this->context->cart->OrderExists() == false) {
            if ($secureKey !== false && $secureKey != $this->context->cart->secure_key) {
                Logger::addLog(__CLASS__.'::validateMollieOrder - Secure key does not match', 3, null, 'Cart', (int) $idCart, true);
                die(Tools::displayError());
            }
            // For each package, generate an order
            $deliveryOptionList = $this->context->cart->getDeliveryOptionList();
            $packageList = $this->context->cart->getPackageList();
            $cartDeliveryOption = $this->context->cart->getDeliveryOption();
            // If some delivery options are not defined, or not valid, use the first valid option
            foreach ($deliveryOptionList as $idAddress => $package) {
                if (!isset($cartDeliveryOption[$idAddress]) || !array_key_exists($cartDeliveryOption[$idAddress], $package)) {
                    foreach ($package as $key => $val) {
                        $cartDeliveryOption[$idAddress] = $key;
                        break;
                    }
                }
            }
            $orderList = array();
            $orderDetailList = array();

            if (!$this->currentOrderReference || Order::getByReference($this->currentOrderReference)->count()) {
                Logger::addLog(__CLASS__.'::validateMollieOrder - Order cannot be created', 3, null, 'Cart', (int) $idCart, true);
                throw new PrestaShopException('Order reference not set before call to '.__CLASS__.'::validateMollieOrder');
            }
            $cartTotalPaid = (float) Tools::ps_round((float) $this->context->cart->getOrderTotal(true, Cart::BOTH), 2);
            if (method_exists('Cart', 'getPackageIdWarehouse')) {
                foreach ($cartDeliveryOption as $idAddress => $keyCarriers) {
                    foreach ($deliveryOptionList[$idAddress][$keyCarriers]['carrier_list'] as $idCarrier => $data) {
                        foreach ($data['package_list'] as $idPackage) {
                            // Rewrite the id_warehouse
                            $packageList[$idAddress][$idPackage]['id_warehouse'] = (int) $this->context->cart->getPackageIdWarehouse($packageList[$idAddress][$idPackage], (int) $idCarrier);
                            $packageList[$idAddress][$idPackage]['id_carrier'] = $idCarrier;
                        }
                    }
                }
            }
            // Make sure CartRule caches are empty
            if (method_exists('CartRule', 'cleanCache')) {
                CartRule::cleanCache();
            }
            $cartRules = $this->context->cart->getCartRules();
            foreach ($cartRules as $cartRule) {
                if (($rule = new CartRule((int) $cartRule['obj']->id)) && Validate::isLoadedObject($rule)) {
                    if ($error = $rule->checkValidity($this->context, true, true)) {
                        $this->context->cart->removeCartRule((int) $rule->id);
                        if (isset($this->context->cookie) && isset($this->context->cookie->id_customer) && $this->context->cookie->id_customer && !empty($rule->code)) {
                            if (version_compare(_PS_VERSION_, '1.7.0.0', '<') && Configuration::get('PS_ORDER_PROCESS_TYPE') == 1) {
                                Tools::redirect('index.php?controller=order-opc&submitAddDiscount=1&discount_name='.urlencode($rule->code));
                            }
                            Tools::redirect('index.php?controller=order&submitAddDiscount=1&discount_name='.urlencode($rule->code));
                        } else {
                            $ruleName = isset($rule->name[(int) $this->context->cart->id_lang]) ? $rule->name[(int) $this->context->cart->id_lang] : $rule->code;
                            if (version_compare(_PS_VERSION_, '1.7.0.0', '<')) {
                                $error = sprintf(Tools::displayError('CartRule ID %1s (%2s) used in this cart is not valid and has been withdrawn from cart'), (int) $rule->id, $ruleName);
                            } else {
                                $error = $this->l('The cart rule named "%1s" (ID %2s) used in this cart is not valid and has been withdrawn from cart', array($ruleName, (int) $rule->id), 'Admin.Payment.Notification');
                            }
                            Logger::addLog($error, 3, '0000002', 'Cart', (int) $this->context->cart->id);
                        }
                    }
                }
            }
            foreach ($packageList as $idAddress => $packageByAddress) {
                foreach ($packageByAddress as $idPackage => $package) {
                    /** @var Order $order */
                    $order = new Order();
                    $order->product_list = $package['product_list'];
                    if (Configuration::get('PS_TAX_ADDRESS_TYPE') == 'id_address_delivery') {
                        $address = new Address((int) $idAddress);
                        $this->context->country = new Country((int) $address->id_country, (int) $this->context->cart->id_lang);
                        if (!$this->context->country->active) {
                            throw new PrestaShopException('The delivery address country is not active.');
                        }
                    }
                    $carrier = null;
                    if (!$this->context->cart->isVirtualCart() && isset($package['id_carrier'])) {
                        $carrier = new Carrier((int) $package['id_carrier'], (int) $this->context->cart->id_lang);
                        $order->id_carrier = (int) $carrier->id;
                        $idCarrier = (int) $carrier->id;
                    } else {
                        $order->id_carrier = 0;
                        $idCarrier = 0;
                    }
                    $order->id_customer = (int) $this->context->cart->id_customer;
                    $order->id_address_invoice = (int) $this->context->cart->id_address_invoice;
                    $order->id_address_delivery = (int) $idAddress;
                    $order->id_currency = $this->context->currency->id;
                    $order->id_lang = (int) $this->context->cart->id_lang;
                    $order->id_cart = (int) $this->context->cart->id;
                    $order->reference = $this->currentOrderReference;
                    $order->id_shop = (int) $this->context->shop->id;
                    $order->id_shop_group = (int) $this->context->shop->id_shop_group;
                    $order->secure_key = ($secureKey ? pSQL($secureKey) : pSQL($this->context->customer->secure_key));
                    $order->payment = $paymentMethod;
                    if (isset($this->name)) {
                        $order->module = $this->name;
                    }
                    $order->recyclable = $this->context->cart->recyclable;
                    $order->gift = (int) $this->context->cart->gift;
                    $order->gift_message = $this->context->cart->gift_message;
                    $order->mobile_theme = $this->context->cart->mobile_theme;
                    $order->conversion_rate = $this->context->currency->conversion_rate;
                    $amountPaid = !$dontTouchAmount ? Tools::ps_round((float) $amountPaid, 2) : $amountPaid;
                    $order->total_paid_real = 0;
                    $order->total_products = (float) $this->context->cart->getOrderTotal(false, Cart::ONLY_PRODUCTS, $order->product_list, $idCarrier);
                    $order->total_products_wt = (float) $this->context->cart->getOrderTotal(true, Cart::ONLY_PRODUCTS, $order->product_list, $idCarrier);
                    $order->total_discounts_tax_excl = (float) abs($this->context->cart->getOrderTotal(false, Cart::ONLY_DISCOUNTS, $order->product_list, $idCarrier));
                    $order->total_discounts_tax_incl = (float) abs($this->context->cart->getOrderTotal(true, Cart::ONLY_DISCOUNTS, $order->product_list, $idCarrier));
                    $order->total_discounts = $order->total_discounts_tax_incl;
                    $order->total_shipping_tax_excl = (float) $this->context->cart->getPackageShippingCost((int) $idCarrier, false, null, $order->product_list);
                    $order->total_shipping_tax_incl = (float) $this->context->cart->getPackageShippingCost((int) $idCarrier, true, null, $order->product_list);
                    $order->total_shipping = $order->total_shipping_tax_incl;
                    if (!is_null($carrier) && Validate::isLoadedObject($carrier)) {
                        $order->carrier_tax_rate = $carrier->getTaxesRate(new Address((int) $this->context->cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')}));
                    }
                    $order->total_wrapping_tax_excl = (float) abs($this->context->cart->getOrderTotal(false, Cart::ONLY_WRAPPING, $order->product_list, $idCarrier));
                    $order->total_wrapping_tax_incl = (float) abs($this->context->cart->getOrderTotal(true, Cart::ONLY_WRAPPING, $order->product_list, $idCarrier));
                    $order->total_wrapping = $order->total_wrapping_tax_incl;
                    $order->total_paid_tax_excl = (float) Tools::ps_round((float) $this->context->cart->getOrderTotal(false, Cart::BOTH, $order->product_list, $idCarrier), _PS_PRICE_COMPUTE_PRECISION_);
                    $order->total_paid_tax_incl = (float) Tools::ps_round((float) $this->context->cart->getOrderTotal(true, Cart::BOTH, $order->product_list, $idCarrier), _PS_PRICE_COMPUTE_PRECISION_);
                    $order->total_paid = $order->total_paid_tax_incl;
                    $order->round_mode = Configuration::get('PS_PRICE_ROUND_MODE');
                    $order->round_type = Configuration::get('PS_ROUND_TYPE');
                    $order->invoice_date = '0000-00-00 00:00:00';
                    $order->delivery_date = '0000-00-00 00:00:00';
                    if (version_compare(_PS_VERSION_, '1.6.0.9', '>=') && static::DEBUG_MODE) {
                        Logger::addLog(__CLASS__.'::validateMollieOrder - Order is about to be added', 1, null, 'Cart', (int) $idCart, true);
                    }
                    // Creating order
                    $result = $order->add();
                    if (!$result) {
                        Logger::addLog(__CLASS__.'::validateMollieOrder - Order cannot be created', 3, null, 'Cart', (int) $idCart, true);
                        throw new PrestaShopException('Can\'t save Order');
                    }
                    // Amount paid by customer is not the right one -> Status = payment error
                    // We don't use the following condition to avoid the float precision issues : http://www.php.net/manual/en/language.types.float.php
                    // if ($order->total_paid != $order->total_paid_real)
                    // We use number_format in order to compare two string
                    if ($orderStatus->logable && number_format($cartTotalPaid, _PS_PRICE_COMPUTE_PRECISION_) != number_format($amountPaid, _PS_PRICE_COMPUTE_PRECISION_)) {
                        $idOrderState = Configuration::get('PS_OS_ERROR');
                    }
                    $orderList[] = $order;
                    if (version_compare(_PS_VERSION_, '1.6.0.9', '>=') && static::DEBUG_MODE) {
                        Logger::addLog(__CLASS__.'::validateMollieOrder - OrderDetail is about to be added', 1, null, 'Cart', (int) $idCart, true);
                    }
                    // Insert new Order detail list using cart for the current order
                    $orderDetail = new OrderDetail(null, null, $this->context);
                    $orderDetail->createList($order, $this->context->cart, $idOrderState, $order->product_list, 0, true, $packageList[$idAddress][$idPackage]['id_warehouse']);
                    $orderDetailList[] = $orderDetail;
                    if (version_compare(_PS_VERSION_, '1.6.0.9', '>=') && static::DEBUG_MODE) {
                        Logger::addLog(__CLASS__.'::validateMollieOrder - OrderCarrier is about to be added', 1, null, 'Cart', (int) $idCart, true);
                    }
                    // Adding an entry in order_carrier table
                    if (!is_null($carrier)) {
                        $orderCarrier = new OrderCarrier();
                        $orderCarrier->id_order = (int) $order->id;
                        $orderCarrier->id_carrier = (int) $idCarrier;
                        $orderCarrier->weight = (float) $order->getTotalWeight();
                        $orderCarrier->shipping_cost_tax_excl = (float) $order->total_shipping_tax_excl;
                        $orderCarrier->shipping_cost_tax_incl = (float) $order->total_shipping_tax_incl;
                        $orderCarrier->add();
                    }
                }
            }
            // The country can only change if the address used for the calculation is the delivery address, and if multi-shipping is activated
            if (isset($context_country) && Configuration::get('PS_TAX_ADDRESS_TYPE') == 'id_address_delivery') {
                $this->context->country = $context_country;
            }
            if (!$this->context->country->active) {
                Logger::addLog(__CLASS__.'::validateMollieOrder - Country is not active', 3, null, 'Cart', (int) $idCart, true);
                throw new PrestaShopException('The order address country is not active.');
            }
            if (version_compare(_PS_VERSION_, '1.6.0.9', '>=') && static::DEBUG_MODE) {
                Logger::addLog(__CLASS__.'::validateMollieOrder - Payment is about to be added', 1, null, 'Cart', (int) $idCart, true);
            }
            // Register Payment only if the order status validate the order
            if ($orderStatus->logable) {
                // $order is the last order loop in the foreach
                // The method addOrderPayment of the class Order make a create a paymentOrder
                // linked to the order reference and not to the order id
                if (isset($extraVars['transaction_id'])) {
                    $transaction_id = $extraVars['transaction_id'];
                } else {
                    $transaction_id = null;
                }
                if (isset($order) && !$order->addOrderPayment($amountPaid, null, $transaction_id)) {
                    Logger::addLog(__CLASS__.'::validateMollieOrder - Cannot save Order Payment', 3, null, 'Cart', (int) $idCart, true);
                    throw new PrestaShopException('Can\'t save Order Payment');
                }
            }

            $cartRuleUsed = array();
            // Make sure CartRule caches are empty
            CartRule::cleanCache();
            foreach ($orderDetailList as $key => $orderDetail) {
                /** @var OrderDetail $orderDetail */
                $order = $orderList[$key];
                if (isset($order->id)) {
                    if (!$secureKey) {
                        $message .= '<br />'.$this->l('Warning: the secure key is empty, check your payment account before validation');
                    }
                    // Optional message to attach to this order
                    if (isset($message) & !empty($message)) {
                        $msg = new Message();
                        $message = strip_tags($message, '<br>');
                        if (Validate::isCleanHtml($message)) {
                            if (version_compare(_PS_VERSION_, '1.6.0.9', '>=') && static::DEBUG_MODE) {
                                Logger::addLog(__CLASS__.'::validateMollieOrder - Message is about to be added', 1, null, 'Cart', (int) $idCart, true);
                            }
                            $msg->message = $message;
                            $msg->id_cart = (int) $idCart;
                            $msg->id_customer = (int) ($order->id_customer);
                            $msg->id_order = (int) $order->id;
                            $msg->private = 1;
                            $msg->add();
                        }
                    }
                    // Insert new Order detail list using cart for the current order
                    //$orderDetail = new OrderDetail(null, null, $this->context);
                    //$orderDetail->createList($order, $this->context->cart, $id_order_state);
                    // Construct order detail table for the email
                    $virtualProduct = true;
                    $productVarTplList = array();
                    $specificPrice = null;
                    foreach ($order->product_list as $product) {
                        if (version_compare(_PS_VERSION_, '1.7.0.0', '<')) {
                            $price = Product::getPriceStatic(
                                (int) $product['id_product'],
                                false,
                                ($product['id_product_attribute'] ? (int) $product['id_product_attribute'] : null),
                                6,
                                null,
                                false,
                                true,
                                $product['cart_quantity'],
                                false,
                                (int) $order->id_customer,
                                (int) $order->id_cart,
                                (int) $order->{Configuration::get('PS_TAX_ADDRESS_TYPE')},
                                $specificPrice,
                                true,
                                true,
                                null,
                                true
                            );
                            $priceWt = Product::getPriceStatic(
                                (int) $product['id_product'],
                                true,
                                ($product['id_product_attribute'] ? (int) $product['id_product_attribute'] : null),
                                2,
                                null,
                                false,
                                true,
                                $product['cart_quantity'],
                                false,
                                (int) $order->id_customer,
                                (int) $order->id_cart,
                                (int) $order->{Configuration::get('PS_TAX_ADDRESS_TYPE')},
                                $specificPrice,
                                true,
                                true,
                                null,
                                true
                            );
                        } else {
                            $price = Product::getPriceStatic(
                                (int) $product['id_product'],
                                false,
                                ($product['id_product_attribute'] ? (int) $product['id_product_attribute'] : null),
                                6,
                                null,
                                false,
                                true,
                                $product['cart_quantity'],
                                false,
                                (int) $order->id_customer,
                                (int) $order->id_cart,
                                (int) $order->{Configuration::get('PS_TAX_ADDRESS_TYPE')},
                                $specificPrice,
                                true,
                                true,
                                null,
                                true,
                                $product['id_customization']
                            );
                            $priceWt = Product::getPriceStatic(
                                (int) $product['id_product'],
                                true,
                                ($product['id_product_attribute'] ? (int) $product['id_product_attribute'] : null),
                                2,
                                null,
                                false,
                                true,
                                $product['cart_quantity'],
                                false,
                                (int) $order->id_customer,
                                (int) $order->id_cart,
                                (int) $order->{Configuration::get('PS_TAX_ADDRESS_TYPE')},
                                $specificPrice,
                                true,
                                true,
                                null,
                                true,
                                $product['id_customization']
                            );
                        }

                        $productPrice = Product::getTaxCalculationMethod() == PS_TAX_EXC ? Tools::ps_round($price, 2) : $priceWt;
                        $productVarTpl = array(
                            'id_product'    => $product['id_product'],
                            'reference'     => $product['reference'],
                            'name'          => $product['name'].(isset($product['attributes']) ? ' - '.$product['attributes'] : ''),
                            'unit_price'    => Tools::displayPrice($productPrice, $this->context->currency, false),
                            'price'         => Tools::displayPrice($productPrice * $product['quantity'], $this->context->currency, false),
                            'quantity'      => $product['quantity'],
                            'customization' => array(),
                        );
                        if (isset($product['price']) && $product['price']) {
                            $productVarTpl['unit_price'] = Tools::displayPrice($productPrice, $this->context->currency, false);
                            $productVarTpl['unit_price_full'] = Tools::displayPrice($productPrice, $this->context->currency, false)
                                .' '.$product['unity'];
                        } else {
                            $productVarTpl['unit_price'] = $productVarTpl['unit_price_full'] = '';
                        }
                        $customizedDatas = Product::getAllCustomizedDatas((int) $order->id_cart, null, true, null, (int) $product['id_customization']);
                        if (isset($customizedDatas[$product['id_product']][$product['id_product_attribute']])) {
                            $productVarTpl['customization'] = array();
                            foreach ($customizedDatas[$product['id_product']][$product['id_product_attribute']][$order->id_address_delivery] as $customization) {
                                $customizationText = '';
                                if (isset($customization['datas'][Product::CUSTOMIZE_TEXTFIELD])) {
                                    foreach ($customization['datas'][Product::CUSTOMIZE_TEXTFIELD] as $text) {
                                        $customizationText .= '<strong>'.$text['name'].'</strong>: '.$text['value'].'<br />';
                                    }
                                }
                                if (isset($customization['datas'][Product::CUSTOMIZE_FILE])) {
                                    $customizationText .= sprintf($this->l('%d image(s)'), count($customization['datas'][Product::CUSTOMIZE_FILE])).'<br />';
                                }
                                $customizationQuantity = (int) $customization['quantity'];
                                $productVarTpl['customization'][] = array(
                                    'customization_text'     => $customizationText,
                                    'customization_quantity' => $customizationQuantity,
                                    'quantity'               => Tools::displayPrice($customizationQuantity * $productPrice, $this->context->currency, false),
                                );
                            }
                        }
                        $productVarTplList[] = $productVarTpl;
                        // Check if is not a virtual product for the displaying of shipping
                        if (!$product['is_virtual']) {
                            $virtualProduct &= false;
                        }
                    } // end foreach ($products)
                    $productListTxt = '';
                    $productListHtml = '';
                    if (count($productVarTplList) > 0) {
                        $productListTxt = $this->getEmailTemplateContent('order_conf_product_list.txt', Mail::TYPE_TEXT, $productVarTplList);
                        $productListHtml = $this->getEmailTemplateContent('order_conf_product_list.tpl', Mail::TYPE_HTML, $productVarTplList);
                    }
                    $cartRulesList = array();
                    $totalReductionValueTi = 0;
                    $totalReductionValueTex = 0;
                    foreach ($cartRules as $cartRule) {
                        $package = array('id_carrier' => $order->id_carrier, 'id_address' => $order->id_address_delivery, 'products' => $order->product_list);
                        $values = array(
                            'tax_incl' => $cartRule['obj']->getContextualValue(true, $this->context, CartRule::FILTER_ACTION_ALL_NOCAP, $package),
                            'tax_excl' => $cartRule['obj']->getContextualValue(false, $this->context, CartRule::FILTER_ACTION_ALL_NOCAP, $package),
                        );
                        // If the reduction is not applicable to this order, then continue with the next one
                        if (!$values['tax_excl']) {
                            continue;
                        }
                        // IF
                        //  This is not multi-shipping
                        //  The value of the voucher is greater than the total of the order
                        //  Partial use is allowed
                        //  This is an "amount" reduction, not a reduction in % or a gift
                        // THEN
                        //  The voucher is cloned with a new value corresponding to the remainder
                        if (count($orderList) == 1 && $values['tax_incl'] > ($order->total_products_wt - $totalReductionValueTi) && $cartRule['obj']->partial_use == 1 && $cartRule['obj']->reduction_amount > 0) {
                            // Create a new voucher from the original
                            $voucher = new CartRule((int) $cartRule['obj']->id); // We need to instantiate the CartRule without lang parameter to allow saving it
                            unset($voucher->id);
                            // Set a new voucher code
                            $voucher->code = empty($voucher->code) ? Tools::substr(md5($order->id.'-'.$order->id_customer.'-'.$cartRule['obj']->id), 0, 16) : $voucher->code.'-2';
                            if (preg_match('/\-([0-9]{1,2})\-([0-9]{1,2})$/', $voucher->code, $matches) && $matches[1] == $matches[2]) {
                                $voucher->code = preg_replace('/'.$matches[0].'$/', '-'.((int) ($matches[1]) + 1), $voucher->code);
                            }
                            // Set the new voucher value
                            if ($voucher->reduction_tax) {
                                $voucher->reduction_amount = ($totalReductionValueTi + $values['tax_incl']) - $order->total_products_wt;
                                // Add total shipping amout only if reduction amount > total shipping
                                if ($voucher->free_shipping == 1 && $voucher->reduction_amount >= $order->total_shipping_tax_incl) {
                                    $voucher->reduction_amount -= $order->total_shipping_tax_incl;
                                }
                            } else {
                                $voucher->reduction_amount = ($totalReductionValueTex + $values['tax_excl']) - $order->total_products;
                                // Add total shipping amout only if reduction amount > total shipping
                                if ($voucher->free_shipping == 1 && $voucher->reduction_amount >= $order->total_shipping_tax_excl) {
                                    $voucher->reduction_amount -= $order->total_shipping_tax_excl;
                                }
                            }
                            if ($voucher->reduction_amount <= 0) {
                                continue;
                            }
                            if ($this->context->customer->isGuest()) {
                                $voucher->id_customer = 0;
                            } else {
                                $voucher->id_customer = $order->id_customer;
                            }
                            $voucher->quantity = 1;
                            $voucher->reduction_currency = $order->id_currency;
                            $voucher->quantity_per_user = 1;
                            if ($voucher->add()) {
                                // If the voucher has conditions, they are now copied to the new voucher
                                CartRule::copyConditions($cartRule['obj']->id, $voucher->id);
                                $orderLanguage = new Language((int) $order->id_lang);
                                $params = array(
                                    '{voucher_amount}' => Tools::displayPrice($voucher->reduction_amount, $this->context->currency, false),
                                    '{voucher_num}'    => $voucher->code,
                                    '{firstname}'      => $this->context->customer->firstname,
                                    '{lastname}'       => $this->context->customer->lastname,
                                    '{id_order}'       => $order->reference,
                                    '{order_name}'     => $order->getUniqReference(),
                                );
                                Mail::Send(
                                    (int) $order->id_lang,
                                    'voucher',
                                    version_compare(_PS_VERSION_, '1.7.0.0', '<')
                                    ? sprintf(Mail::l('New voucher for your order %s'), $order->reference)
                                    : $this->trans(
                                        'New voucher for your order %s',
                                        array($order->reference),
                                        'Emails.Subject',
                                        isset($orderLanguage->locale) ? $orderLanguage->locale : null
                                    ),
                                    $params,
                                    $this->context->customer->email,
                                    $this->context->customer->firstname.' '.$this->context->customer->lastname,
                                    null, null, null, null, _PS_MAIL_DIR_, false, (int) $order->id_shop
                                );
                            }
                            $values['tax_incl'] = $order->total_products_wt - $totalReductionValueTi;
                            $values['tax_excl'] = $order->total_products - $totalReductionValueTex;
                            if (1 == $voucher->free_shipping) {
                                $values['tax_incl'] += $order->total_shipping_tax_incl;
                                $values['tax_excl'] += $order->total_shipping_tax_excl;
                            }
                        }
                        $totalReductionValueTi += $values['tax_incl'];
                        $totalReductionValueTex += $values['tax_excl'];
                        $order->addCartRule($cartRule['obj']->id, $cartRule['obj']->name, $values, 0, $cartRule['obj']->free_shipping);
                        if ($idOrderState != Configuration::get('PS_OS_ERROR') && $idOrderState != Configuration::get('PS_OS_CANCELED') && !in_array($cartRule['obj']->id, $cartRuleUsed)) {
                            $cartRuleUsed[] = $cartRule['obj']->id;
                            // Create a new instance of Cart Rule without id_lang, in order to update its quantity
                            $cartRuleToUpdate = new CartRule((int) $cartRule['obj']->id);
                            $cartRuleToUpdate->quantity = max(0, $cartRuleToUpdate->quantity - 1);
                            $cartRuleToUpdate->update();
                        }
                        $cartRulesList[] = array(
                            'voucher_name'      => $cartRule['obj']->name,
                            'voucher_reduction' => ($values['tax_incl'] != 0.00 ? '-' : '').Tools::displayPrice($values['tax_incl'], $this->context->currency, false),
                        );
                    }
                    $cartRulesListTxt = '';
                    $cartRulesListHtml = '';
                    if (count($cartRulesList) > 0) {
                        $cartRulesListTxt = $this->getEmailTemplateContent('order_conf_cart_rules.txt', Mail::TYPE_TEXT, $cartRulesList);
                        $cartRulesListHtml = $this->getEmailTemplateContent('order_conf_cart_rules.tpl', Mail::TYPE_HTML, $cartRulesList);
                    }

                    // Specify order id for message
                    $oldMessage = Message::getMessageByCartId((int) $this->context->cart->id);
                    if ($oldMessage && !$oldMessage['private']) {
                        $updateMessage = new Message((int) $oldMessage['id_message']);
                        $updateMessage->id_order = (int) $order->id;
                        $updateMessage->update();
                        // Add this message in the customer thread
                        $customerThread = new CustomerThread();
                        $customerThread->id_contact = 0;
                        $customerThread->id_customer = (int) $order->id_customer;
                        $customerThread->id_shop = (int) $this->context->shop->id;
                        $customerThread->id_order = (int) $order->id;
                        $customerThread->id_lang = (int) $this->context->language->id;
                        $customerThread->email = $this->context->customer->email;
                        $customerThread->status = 'open';
                        $customerThread->token = Tools::passwdGen(12);
                        $customerThread->add();
                        $customerMessage = new CustomerMessage();
                        $customerMessage->id_customer_thread = $customerThread->id;
                        $customerMessage->id_employee = 0;
                        $customerMessage->message = $updateMessage->message;
                        $customerMessage->private = 1;
                        if (!$customerMessage->add()) {
                            $this->context->controller->errors[] = $this->l('An error occurred while saving message', array(), 'Admin.Payment.Notification');
                        }
                    }
                    if (version_compare(_PS_VERSION_, '1.6.0.9', '>=') && static::DEBUG_MODE) {
                        Logger::addLog(__CLASS__.'::validateMollieOrder - Hook validateOrder is about to be called', 1, null, 'Cart', (int) $idCart, true);
                    }
                    // Hook validate order
                    Hook::exec('actionValidateOrder', array(
                        'cart'        => $this->context->cart,
                        'order'       => $order,
                        'customer'    => $this->context->customer,
                        'currency'    => $this->context->currency,
                        'orderStatus' => $orderStatus,
                    ));
                    foreach ($this->context->cart->getProducts() as $product) {
                        if ($orderStatus->logable) {
                            ProductSale::addProductSale((int) $product['id_product'], (int) $product['cart_quantity']);
                        }
                    }
                    if (version_compare(_PS_VERSION_, '1.6.0.9', '>=') && static::DEBUG_MODE) {
                        Logger::addLog(__CLASS__.'::validateMollieOrder - Order Status is about to be added', 1, null, 'Cart', (int) $idCart, true);
                    }
                    // Set the order status
                    $this->setOrderStatus($order->id, $idOrderState, true);
                    // Switch to back order if needed
                    if (Configuration::get('PS_STOCK_MANAGEMENT') &&
                        ($orderDetail->getStockState() || $orderDetail->product_quantity_in_stock < 0)
                    ) {
                        $this->setOrderStatus($order, Configuration::get($order->valid ? 'PS_OS_OUTOFSTOCK_PAID' : 'PS_OS_OUTOFSTOCK_UNPAID'), true);
                    }
                    unset($orderDetail);
                    // Order is reloaded because the status just changed
                    $order = new Order((int) $order->id);
                    // Send an e-mail to customer (one order = one email)
                    if ($idOrderState != Configuration::get('PS_OS_ERROR') && $idOrderState != Configuration::get('PS_OS_CANCELED') && $this->context->customer->id) {
                        $invoice = new Address((int) $order->id_address_invoice);
                        $delivery = new Address((int) $order->id_address_delivery);
                        $deliveryState = $delivery->id_state ? new State((int) $delivery->id_state) : false;
                        $invoiceState = $invoice->id_state ? new State((int) $invoice->id_state) : false;
                        $data = array(
                            '{firstname}'            => $this->context->customer->firstname,
                            '{lastname}'             => $this->context->customer->lastname,
                            '{email}'                => $this->context->customer->email,
                            '{delivery_block_txt}'   => $this->_getFormatedAddress($delivery, "\n"),
                            '{invoice_block_txt}'    => $this->_getFormatedAddress($invoice, "\n"),
                            '{delivery_block_html}'  => $this->_getFormatedAddress($delivery, '<br />', array(
                                'firstname' => '<span style="font-weight:bold;">%s</span>',
                                'lastname'  => '<span style="font-weight:bold;">%s</span>',
                            )),
                            '{invoice_block_html}'   => $this->_getFormatedAddress($invoice, '<br />', array(
                                'firstname' => '<span style="font-weight:bold;">%s</span>',
                                'lastname'  => '<span style="font-weight:bold;">%s</span>',
                            )),
                            '{delivery_company}'     => $delivery->company,
                            '{delivery_firstname}'   => $delivery->firstname,
                            '{delivery_lastname}'    => $delivery->lastname,
                            '{delivery_address1}'    => $delivery->address1,
                            '{delivery_address2}'    => $delivery->address2,
                            '{delivery_city}'        => $delivery->city,
                            '{delivery_postal_code}' => $delivery->postcode,
                            '{delivery_country}'     => $delivery->country,
                            '{delivery_state}'       => $delivery->id_state ? $deliveryState->name : '',
                            '{delivery_phone}'       => ($delivery->phone) ? $delivery->phone : $delivery->phone_mobile,
                            '{delivery_other}'       => $delivery->other,
                            '{invoice_company}'      => $invoice->company,
                            '{invoice_vat_number}'   => $invoice->vat_number,
                            '{invoice_firstname}'    => $invoice->firstname,
                            '{invoice_lastname}'     => $invoice->lastname,
                            '{invoice_address2}'     => $invoice->address2,
                            '{invoice_address1}'     => $invoice->address1,
                            '{invoice_city}'         => $invoice->city,
                            '{invoice_postal_code}'  => $invoice->postcode,
                            '{invoice_country}'      => $invoice->country,
                            '{invoice_state}'        => $invoice->id_state ? $invoiceState->name : '',
                            '{invoice_phone}'        => ($invoice->phone) ? $invoice->phone : $invoice->phone_mobile,
                            '{invoice_other}'        => $invoice->other,
                            '{order_name}'           => $order->getUniqReference(),
                            '{date}'                 => Tools::displayDate(date('Y-m-d H:i:s'), null, 1),
                            '{carrier}'              => ($virtualProduct || !isset($carrier->name)) ? $this->l('No carrier', array(), 'Admin.Payment.Notification') : $carrier->name,
                            '{payment}'              => Tools::substr($order->payment, 0, 255),
                            '{products}'             => $productListHtml,
                            '{products_txt}'         => $productListTxt,
                            '{discounts}'            => $cartRulesListHtml,
                            '{discounts_txt}'        => $cartRulesListTxt,
                            '{total_paid}'           => Tools::displayPrice($order->total_paid, $this->context->currency, false),
                            '{total_products}'       => Tools::displayPrice(Product::getTaxCalculationMethod() == PS_TAX_EXC ? $order->total_products : $order->total_products_wt, $this->context->currency, false),
                            '{total_discounts}'      => Tools::displayPrice($order->total_discounts, $this->context->currency, false),
                            '{total_shipping}'       => Tools::displayPrice($order->total_shipping, $this->context->currency, false),
                            '{total_wrapping}'       => Tools::displayPrice($order->total_wrapping, $this->context->currency, false),
                            '{total_tax_paid}'       => Tools::displayPrice(($order->total_products_wt - $order->total_products) + ($order->total_shipping_tax_incl - $order->total_shipping_tax_excl), $this->context->currency, false),
                        );
                        if (is_array($extraVars)) {
                            $data = array_merge($data, $extraVars);
                        }
                        // Join PDF invoice
                        if ((int) Configuration::get('PS_INVOICE') && $orderStatus->invoice && $order->invoice_number) {
                            $orderInvoiceList = $order->getInvoicesCollection();
                            Hook::exec('actionPDFInvoiceRender', array('order_invoice_list' => $orderInvoiceList));
                            $pdf = new PDF($orderInvoiceList, PDF::TEMPLATE_INVOICE, $this->context->smarty);
                            $fileAttachment = array();
                            $fileAttachment['content'] = $pdf->render(false);
                            $fileAttachment['name'] = Configuration::get('PS_INVOICE_PREFIX', (int) $order->id_lang, null, $order->id_shop).sprintf('%06d', $order->invoice_number).'.pdf';
                            $fileAttachment['mime'] = 'application/pdf';
                        } else {
                            $fileAttachment = null;
                        }
                        if (version_compare(_PS_VERSION_, '1.6.0.9', '>=') && static::DEBUG_MODE) {
                            Logger::addLog(__CLASS__.'::validateMollieOrder - Mail is about to be sent', 1, null, 'Cart', (int) $idCart, true);
                        }
                        $orderLanguage = new Language((int) $order->id_lang);
                        if (Validate::isEmail($this->context->customer->email)) {
                            Mail::Send(
                                (int) $order->id_lang,
                                'order_conf',
                                version_compare(_PS_VERSION_, '1.7.0.0', '<')
                                    ? Mail::l('Order confirmation', (int) $order->id_lang)
                                    : $this->trans(
                                    'Order confirmation',
                                    array(),
                                    'Emails.Subject',
                                    isset($orderLanguage->locale) ? $orderLanguage->locale : null
                                ),
                                $data,
                                $this->context->customer->email,
                                $this->context->customer->firstname.' '.$this->context->customer->lastname,
                                null,
                                null,
                                $fileAttachment,
                                null, _PS_MAIL_DIR_, false, (int) $order->id_shop
                            );
                        }
                    }

                    // updates stock in shops
                    if (Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) {
                        $productList = $order->getProducts();
                        foreach ($productList as $product) {
                            // if the available quantities depends on the physical stock
                            if (StockAvailable::dependsOnStock($product['product_id'])) {
                                // synchronizes
                                StockAvailable::synchronize($product['product_id'], $order->id_shop);
                            }
                        }
                    }
                    if (method_exists('Order', 'updateOrderDetailTax')) {
                        $order->updateOrderDetailTax();
                    }
                    // sync all stock
                    if (class_exists('StockManager') && method_exists('StockManager', 'updatePhysicalProductQuantity')) {
                        $stockManager = new StockManager();
                        $stockManager->updatePhysicalProductQuantity(
                            (int) $order->id_shop,
                            (int) Configuration::get('PS_OS_ERROR'),
                            (int) Configuration::get('PS_OS_CANCELED'),
                            null,
                            (int) $order->id
                        );
                    }
                } else {
                    $error = $this->l('Order creation failed', array(), 'Admin.Payment.Notification');
                    Logger::addLog($error, 4, '0000002', 'Cart', (int) $order->id_cart);
                    die($error);
                }
            } // End foreach $order_detail_list
            // Use the last order as currentOrder
            if (isset($order) && $order->id) {
                $this->currentOrder = (int) $order->id;
            }
            if (version_compare(_PS_VERSION_, '1.6.0.9', '>=') && static::DEBUG_MODE) {
                Logger::addLog(__CLASS__.'::validateMollieOrder - End of validateOrder', 1, null, 'Cart', (int) $idCart, true);
            }

            return true;
        } else {
            $error = $this->l('Cart cannot be loaded or an order has already been placed using this cart', array(), 'Admin.Payment.Notification');
            Logger::addLog($error, 4, '0000001', 'Cart', (int) $this->context->cart->id);
            die($error);
        }
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
                return $override['module'];
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
        return (int) Configuration::get('PS_PRICE_ROUND_MODE') !== 2;
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
        $overrides = array();

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
                $overrideFile = array_diff($overrideFile, array("\n"));
            } else {
                $overrideFile = array();
            }
            foreach ($reflectionMethods as $reflectionMethod) {
                /** @var ReflectionMethod $reflectionMethod */
                $idOverride = Tools::substr(sha1($reflectionMethod->class.'::'.$reflectionMethod->name), 0, 10);
                $overriddenMethod = array(
                    'id_override' => $idOverride,
                    'override'    => $reflectionMethod->class.'::'.$reflectionMethod->name,
                    'module_code' => $this->l('Unknown'),
                    'module_name' => $this->l('Unknown'),
                    'date'        => $this->l('Unknown'),
                    'version'     => $this->l('Unknown'),
                    'deleted'     => (Tools::isSubmit('deletemodule') && Tools::getValue( 'id_override') === $idOverride)
                        || (Tools::isSubmit('overrideBox') && in_array($idOverride, Tools::getValue('overrideBox'))),
                );
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
        $classes = array();
        $rootDir = $this->normalizeDirectory(_PS_ROOT_DIR_);

        foreach (scandir($rootDir.$path) as $file) {
            if ($file[0] != '.') {
                if (is_dir($rootDir.$path.$file)) {
                    $classes = array_merge($classes, $this->getClassesFromDir($path.$file.'/'));
                } elseif (Tools::substr($file, -4) == '.php') {
                    $content = Tools::file_get_contents($rootDir.$path.$file);

                    $namespacePattern = '[\\a-z0-9_]*[\\]';
                    $pattern = '#\W((abstract\s+)?class|interface)\s+(?P<classname>'.basename($file, '.php').'(?:Core)?)'.'(?:\s+extends\s+'.$namespacePattern.'[a-z][a-z0-9_]*)?(?:\s+implements\s+'.$namespacePattern.'[a-z][\\a-z0-9_]*(?:\s*,\s*'.$namespacePattern.'[a-z][\\a-z0-9_]*)*)?\s*\{#i';

                    if (preg_match($pattern, $content, $m)) {
                        $classes[$m['classname']] = array(
                            'path'     => $path.$file,
                            'type'     => trim($m[1]),
                            'override' => true,
                        );

                        if (Tools::substr($m['classname'], -4) == 'Core') {
                            $classes[Tools::substr($m['classname'], 0, -4)] = array(
                                'path'     => '',
                                'type'     => $classes[$m['classname']]['type'],
                                'override' => true,
                            );
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
        return rtrim($directory, '/\\').DIRECTORY_SEPARATOR;
    }

    /**
     * Get the selected API
     *
     * @since 3.3.0
     * @throws PrestaShopException
     *
     * @since 3.3.0
     */
    public static function selectedApi()
    {
        /** @var static $mollie */
        $mollie = Module::getInstanceByName('mollie');
        if (!in_array(static::$selectedApi, array(static::MOLLIE_ORDERS_API, static::MOLLIE_PAYMENTS_API))) {
            static::$selectedApi = Configuration::get(static::MOLLIE_API);
            if (!static::$selectedApi
                || !in_array(static::$selectedApi, array(static::MOLLIE_ORDERS_API, static::MOLLIE_PAYMENTS_API))
                || $mollie->checkRoundingMode()
            ) {
                static::$selectedApi = static::MOLLIE_PAYMENTS_API;
            }
        }

        return static::$selectedApi;
    }

    /**
     * @param string $transactionId
     * @param bool   $process Process the new payment/order status
     *
     * @return array|null
     *
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     * @throws \MollieModule\Mollie\Api\Exceptions\ApiException
     * @throws \PrestaShop\PrestaShop\Adapter\CoreException
     *
     * @since 3.3.0
     * @since 3.3.2 $process option
     */
    public function getFilteredApiPayment($transactionId, $process = false)
    {
        /** @var \MollieModule\Mollie\Api\Resources\Payment $payment */
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
                $refunds = array();
            }
            $refunds = array_map(function ($refund) {
                return array_intersect_key(
                    (array) $refund,
                    array_flip(array(
                        'resource',
                        'id',
                        'amount',
                        'createdAt',
                    )));
            }, (array) $refunds);
            $payment = array_intersect_key(
                (array) $payment,
                array_flip(array(
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
                ))
            );
            $payment['refunds'] = (array) $refunds;
        } else {
            $payment = null;
        }

        return $payment;
    }

    /**
     * @param string $transactionId
     * @param bool   $process       Process the new payment/order status
     *
     * @return array|null
     *
     * @throws Adapter_Exception
     * @throws ErrorException
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     * @throws \MollieModule\Mollie\Api\Exceptions\ApiException
     * @throws \PrestaShop\PrestaShop\Adapter\CoreException
     *
     * @since 3.3.0
     * @since 3.3.2 $process option
     */
    public function getFilteredApiOrder($transactionId, $process = false)
    {
        /** @var \MollieModule\Mollie\Api\Resources\Order $order */
        $order = $this->api->orders->get($transactionId);
        if ($process) {
            if (!Tools::isSubmit('module')) {
                $_GET['module'] = $this->name;
            }
            $webhookController = new MollieWebhookModuleFrontController();
            $webhookController->processTransaction($order);
        }

        if ($order && method_exists($order, 'refunds')) {
            $refunds = $order->refunds();
            if (empty($refunds)) {
                $refunds = array();
            }
            $refunds = array_map(function ($refund) {
                return array_intersect_key(
                    (array) $refund,
                    array_flip(array(
                        'resource',
                        'id',
                        'amount',
                        'createdAt',
                    )));
            }, (array) $refunds);
            $order = array_intersect_key(
                (array) $order,
                array_flip(array(
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
                ))
            );
            $order['refunds'] = (array) $refunds;
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
            $methodsForConfig = $this->getMethodsForConfig();
        } catch (\MollieModule\Mollie\Api\Exceptions\ApiException $e) {
            return array(
                'success' => false,
                'methods' => null,
                'message' => $e->getMessage(),
            );
        } catch (PrestaShopException $e) {
            return array(
                'success' => false,
                'methods' => null,
                'message' => $e->getMessage(),
            );
        }
        Configuration::updateValue(static::MOLLIE_METHODS_LAST_CHECK, time());
        if (!is_array($methodsForConfig)) {
            return array(
                'success' => false,
                'methods' => null,
                'message' => $this->l('No payment methods found'),
            );
        }

        $dbMethods = @json_decode(Configuration::get(static::METHODS_CONFIG), true);

        // Auto update images and issuers
        $shouldSave = false;
        if (is_array($dbMethods)) {
            foreach ($dbMethods as $index => &$dbMethod) {
                $found = false;
                foreach ($methodsForConfig as $methodForConfig) {
                    if ($dbMethod['id'] === $methodForConfig['id']) {
                        $found = true;
                        foreach (array('issuers', 'image', 'name', 'available') as $prop) {
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
            $dbMethods = array();
            foreach ($methodsForConfig as $index => $method) {
                $dbMethods[] = array_merge(
                    $method,
                    array(
                        'position' => $index,
                    )
                );
            }
        }

        if ($shouldSave && !empty($dbMethods)) {
            Configuration::updateValue(static::METHODS_CONFIG, json_encode($dbMethods));
        }

        return array(
            'success' => true,
            'methods' => $methodsForConfig,
        );
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

        return array('success' => true, 'carriers' => static::carrierConfig());
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
                            return array(
                                'success' => false,
                                'message' => $this->l('You do not have permission to refund payments'),
                            );
                        }
                        if (!isset($input['amount']) || empty($input['amount'])) {
                            // No amount = full refund
                            $status = $this->doPaymentRefund($mollieData['order_id'], $mollieData['transaction_id']);
                        } else {
                            $status = $this->doPaymentRefund($mollieData['order_id'], $mollieData['transaction_id'], $input['amount']);
                        }

                        return array(
                            'success' => isset($status['status']) && $status['status'] === 'success',
                            'payment' => static::getFilteredApiPayment($input['transactionId'], static::isLocalEnvironment()),
                        );
                    case 'retrieve':
                        // Check order view permissions
                        if (!$access || empty($access['view'])) {
                            return array(
                                'success' => false,
                                'message' => sprintf($this->l('You do not have permission to %s payments'), $this->l('view')),
                            );
                        }
                        return array(
                            'success' => true,
                            'payment' => static::getFilteredApiPayment($input['transactionId'], static::isLocalEnvironment())
                        );
                    default:
                        return array('success' => false);
                }
            } elseif ($input['resource'] === 'orders') {
                switch ($input['action']) {
                    case 'retrieve':
                        // Check order edit permissions
                        if (!$access || empty($access['view'])) {
                            return array(
                                'success' => false,
                                'message' => sprintf($this->l('You do not have permission to %s payments'), $this->l('edit')),
                            );
                        }
                        $info = static::getPaymentBy('transaction_id', $input['transactionId']);
                        if (!$info) {
                            return array('success' => false);
                        }
                        $tracking = static::getShipmentInformation($info['order_id']);

                        return array(
                            'success'  => true,
                            'order'    => static::getFilteredApiOrder($input['transactionId'], static::isLocalEnvironment()),
                            'tracking' => $tracking,
                        );
                    case 'ship':
                        // Check order edit permissions
                        if (!$access || empty($access['edit'])) {
                            return array(
                                'success' => false,
                                'message' => sprintf($this->l('You do not have permission to %s payments'), $this->l('ship')),
                            );
                        }
                        $status = $this->doShipOrderLines($input['transactionId'], isset($input['orderLines']) ? $input['orderLines'] : array(), isset($input['tracking']) ? $input['tracking'] : null);
                        return array_merge($status, array('order' => static::getFilteredApiOrder($input['transactionId'], static::isLocalEnvironment())));
                    case 'refund':
                        // Check order edit permissions
                        if (!$access || empty($access['edit'])) {
                            return array(
                                'success' => false,
                                'message' => sprintf($this->l('You do not have permission to %s payments'), $this->l('refund')),
                            );
                        }
                        $status = $this->doRefundOrderLines($input['transactionId'], isset($input['orderLines']) ? $input['orderLines'] : array());
                        return array_merge($status, array('order' => static::getFilteredApiOrder($input['transactionId'], static::isLocalEnvironment())));
                    case 'cancel':
                        // Check order edit permissions
                        if (!$access || empty($access['edit'])) {
                            return array(
                                'success' => false,
                                'message' => sprintf($this->l('You do not have permission to %s payments'), $this->l('cancel')),
                            );
                        }
                        $status = $this->doCancelOrderLines($input['transactionId'], isset($input['orderLines']) ? $input['orderLines'] : array());
                        return array_merge($status, array('order' => static::getFilteredApiOrder($input['transactionId'], static::isLocalEnvironment())));
                    default:
                        return array('success' => false);
                }
            }
        } catch (Exception $e) {
            Logger::addLog("Mollie module error: {$e->getMessage()}");
            return array('success' => false);
        }

        return array('success' => false);
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
        $sql->where('`name` = \''.pSQL($module).'\'');

        return (string) Db::getInstance()->getValue($sql);
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
                WHERE TABLE_SCHEMA = \''._DB_NAME_.'\'
                AND TABLE_NAME = \''._DB_PREFIX_.'mollie_payments\'
                AND COLUMN_NAME = \'order_reference\'')
            ) {
                return Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'mollie_payments` ADD `order_reference` varchar(191)');
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
        $sorter = array();
        $ret = array();
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
     * Check the order status hook
     *
     * @since 3.3.0
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    protected function checkOrderStatusHook()
    {
        $shouldHook = false;
        if (!$idHook = Hook::getIdByName('actionOrderStatusUpdate')) {
            $shouldHook = true;
        }
        $modules = Hook::getModulesFromHook($idHook);
        if (!is_array($modules)) {
            $modules = array();
        }
        $modules = array_map('intval', array_keys($modules));
        if (!in_array((int) $this->id, $modules)) {
            $shouldHook = true;
        }

        if ($shouldHook) {
            $this->registerHook('actionOrderStatusUpdate');
        }
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
    public function hookActionOrderStatusUpdate($params = array())
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
        $checkStatuses = array();
        if (Configuration::get(static::MOLLIE_AUTO_SHIP_STATUSES)) {
            $checkStatuses = @json_decode(Configuration::get(static::MOLLIE_AUTO_SHIP_STATUSES));
        }
        if (!is_array($checkStatuses)) {
            $checkStatuses = array();
        }
        $shipmentInfo = static::getShipmentInformation($idOrder);

        if (!(Configuration::get(static::MOLLIE_AUTO_SHIP_MAIN) && $orderStatus->shipped
                || in_array($orderStatusNumber, $checkStatuses)
            ) || $shipmentInfo === null) {
            return;
        }

        try {
            $dbPayment = static::getPaymentBy('order_id', (int) $idOrder);
        } catch (PrestaShopDatabaseException $e) {
            Logger::addLog("Mollie module error: {$e->getMessage()}");
            return;
        } catch (PrestaShopException $e) {
            Logger::addLog("Mollie module error: {$e->getMessage()}");
            return;
        }
        if (empty($dbPayment) || !isset($dbPayment['transaction_id'])) {
            // No transaction found
            return;
        }

        $length = Tools::strlen(\MollieModule\Mollie\Api\Endpoints\OrderEndpoint::RESOURCE_ID_PREFIX);
        if (Tools::substr($dbPayment['transaction_id'], 0, $length) !== \MollieModule\Mollie\Api\Endpoints\OrderEndpoint::RESOURCE_ID_PREFIX) {
            // No need to check regular payments
            return;
        }

        try {
            $apiPayment = $this->api->orders->get($dbPayment['transaction_id']);
            $shippableItems = 0;
            foreach ($apiPayment->lines as $line) {
                $shippableItems += $line->shippableQuantity;
            }
            if ($shippableItems <= 0) {
                return;
            }

            $apiPayment->shipAll(array('tracking' => $shipmentInfo));
        } catch (\MollieModule\Mollie\Api\Exceptions\ApiException $e) {
            Logger::addLog("Mollie module error: {$e->getMessage()}");
            return;
        } catch (Exception $e) {
            Logger::addLog("Mollie module error: {$e->getMessage()}");
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
     * @since 3.3.0
     * @throws PrestaShopException
     * @throws Adapter_Exception
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
            return null;
        }

        if ($carrierConfig['source'] === static::MOLLIE_CARRIER_NO_TRACKING_INFO) {
            return array();
        }

        if ($carrierConfig['source'] === static::MOLLIE_CARRIER_MODULE) {
            $carrier = new Carrier($order->id_carrier);
            if (in_array($carrier->external_module_name, array('postnl', 'myparcel'))) {
                if (version_compare(static::getDatabaseVersion($carrier->external_module_name), '2.1.0', '>=')) {
                    $table = $carrier->external_module_name === 'postnl' ? 'postnlmod_order' : 'myparcel_order';
                    $sql = new DbQuery();
                    $sql->select('`tracktrace`, `postcode`');
                    $sql->from(bqSQL($table));
                    $sql->where('`id_order` = \''.pSQL($idOrder).'\'');

                    try {
                        $info = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
                        if ($info['tracktrace'] && $info['postcode']) {
                            $postcode = Tools::strtoupper(str_replace(' ', '', $info['postcode']));
                            $langIso = Tools::strtoupper(Language::getIsoById($order->id_lang));
                            $countryIso = Tools::strtoupper(Country::getIsoById($deliveryAddress->id_country));
                            $tracktrace = $info['tracktrace'];

                            return array(
                                'carrier' => 'PostNL',
                                'code'    => $info['tracktrace'],
                                'url'     => "http://postnl.nl/tracktrace/?L={$langIso}&B={$tracktrace}&P={$postcode}&D={$countryIso}&T=C",
                            );
                        }
                    } catch (PrestaShopDatabaseException $e) {
                        return array();
                    }
                }
            }
            return array();
        }

        if ($carrierConfig['source'] === static::MOLLIE_CARRIER_CARRIER) {
            $carrier = new Carrier($order->id_carrier);
            $shippingNumber = $order->shipping_number;
            if (!$shippingNumber && method_exists($order, 'getIdOrderCarrier')) {
                $orderCarrier = new OrderCarrier($order->getIdOrderCarrier());
                $shippingNumber = $orderCarrier->tracking_number;
            }

            if (!$shippingNumber || !$carrier->name) {
                return array();
            }

            return array(
                'carrier' => $carrier->name,
                'code'    => $shippingNumber,
                'url'     => str_replace('@', $shippingNumber, $carrier->url),
            );
        }

        if ($carrierConfig['source'] === static::MOLLIE_CARRIER_CUSTOM) {
            $carrier = new Carrier($order->id_carrier);
            $shippingNumber = $order->shipping_number;
            if (!$shippingNumber && method_exists($order, 'getIdOrderCarrier')) {
                $orderCarrier = new OrderCarrier($order->getIdOrderCarrier());
                $shippingNumber = $orderCarrier->tracking_number;
            }

            if (!$shippingNumber || !$carrier->name) {
                return array();
            }

            $invoicePostcode = Tools::strtoupper(str_replace(' ', '', $invoiceAddress->postcode));
            $invoiceCountryIso = Tools::strtoupper(Country::getIsoById($invoiceAddress->id_country));
            $deliveryPostcode = Tools::strtoupper(str_replace(' ', '', $deliveryAddress->postcode));
            $deliveryCountryIso = Tools::strtoupper(Country::getIsoById($deliveryAddress->id_country));

            $langIso = Tools::strtoupper(Language::getIsoById($order->id_lang));

            $info = array(
                '@'                        => $shippingNumber,
                '%%shipping_number%%'      => $shippingNumber,
                '%%invoice.country_iso%%'  => $invoiceCountryIso,
                '%%invoice.postcode%%'     => $invoicePostcode,
                '%%delivery.country_iso%%' => $deliveryCountryIso,
                '%%delivery.postcode%%'    => $deliveryPostcode,
                '%%lang_iso%%'             => $langIso,
            );

            return array(
                'carrier' => $carrier->name,
                'code'    => $shippingNumber,
                'url'     => str_ireplace(
                    array_keys($info),
                    array_values($info),
                    $carrierConfig['custom_url']
                ),
            );
        }

        return null;
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

        if (!$carrierConfig = @json_decode(Configuration::get(static::MOLLIE_TRACKING_URLS), true)) {
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
     * Set default carrier statuses
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @since 3.3.0
     */
    public function setDefaultCarrierStatuses()
    {
        $sql = new DbQuery();
        $sql->select('`'.bqSQL(OrderState::$definition['primary']).'`');
        $sql->from(bqSQL(OrderState::$definition['table']));
        $sql->where('`shipped` = 1');

        $defaultStatuses = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        if (!is_array($defaultStatuses)) {
            return;
        }
        $defaultStatuses = array_map('intval', array_column($defaultStatuses, OrderState::$definition['primary']));
        Configuration::updateValue(static::MOLLIE_AUTO_SHIP_STATUSES, json_encode($defaultStatuses));
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
     * @since 3.3.0
     */
    protected function getStatusesValue($key)
    {
        $statesEnabled = array();
        foreach (OrderState::getOrderStates($this->context->language->id) as $state) {
            if (Tools::isSubmit($key.'_'.$state['id_order_state'])) {
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
     * @param string   $class
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
        $tabs = array();
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
     * @param string   $class
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

        return in_array($tld, array('localhost', 'test', 'dev', 'app', 'local', 'invalid', 'example'))
            || (filter_var($host, FILTER_VALIDATE_IP)
                && !filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE));
    }
}
