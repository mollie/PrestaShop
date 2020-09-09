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

namespace Mollie\Config;

use _PhpScoper5eddef0da618a\Mollie\Api\Types\OrderStatus;
use _PhpScoper5eddef0da618a\Mollie\Api\Types\PaymentStatus;
use _PhpScoper5eddef0da618a\Mollie\Api\Types\RefundStatus;
use Configuration;
use Mollie\Utility\EnvironmentUtility;

class Config
{
    /**
     * Currency restrictions per payment method
     *
     * @var array
     */
    public static $methodCurrencies = [
        'banktransfer' => ['eur'],
        'belfius' => ['eur'],
        'bitcoin' => ['eur'],
        'cartesbancaires' => ['eur'],
        'creditcard' => ['aud', 'bgn', 'cad', 'chf', 'czk', 'dkk', 'eur', 'gbp', 'hkd', 'hrk', 'huf', 'ils', 'isk', 'jpy', 'pln', 'ron', 'sek', 'usd'],
        'directdebit' => ['eur'],
        'eps' => ['eur'],
        'giftcard' => ['eur'],
        'giropay' => ['eur'],
        'ideal' => ['eur'],
        'applepay' => ['aud', 'bgn', 'cad', 'chf', 'czk', 'dkk', 'eur', 'gbp', 'hkd', 'hrk', 'huf', 'ils', 'isk', 'jpy', 'pln', 'ron', 'sek', 'usd'],
        'inghomepay' => ['eur'],
        'kbc' => ['eur'],
        'bancontact' => ['eur'],
        'paypal' => ['aud', 'brl', 'cad', 'chf', 'czk', 'dkk', 'eur', 'gbp', 'hkd', 'huf', 'ils', 'jpy', 'mxn', 'myr', 'nok', 'nzd', 'php', 'pln', 'rub', 'sek', 'sgd', 'thb', 'twd', 'usd'],
        'paysafecard' => ['eur'],
        'sofort' => ['eur'],
        'klarnapaylater' => ['eur'],
        'klarnasliceit' => ['eur'],
        'mybank' => ['eur'],

    ];


    /**
     * Default payment method availability
     *
     * empty array is global availability
     *
     * @var array
     *
     * @since 3.3.2
     */
    public static $defaultMethodAvailability = [
        'creditcard' => [],
        'klarnapaylater' => ['nl', 'de', 'at', 'fi'],
        'klarnasliceit' => ['de', 'at', 'fi'],
        'ideal' => ['nl'],
        'bancontact' => ['be'],
        'paypal' => [],
        'giropay' => ['de'],
        'eps' => ['at'],
        'belfius' => ['be'],
        'inghomepay' => ['be'],
        'kbc' => ['be'],
        'sofort' => ['de', 'at', 'ch', 'pl', 'it', 'es', 'be', 'nl'],
        'giftcard' => ['nl'],
        'bitcoin' => [],
        'paysafecard' => [],
        'banktransfer' => [],
        'cartesbancaires' => ['fr'],
        'directdebit' => [
            'fi', 'at', 'pt', 'be', 'bg', 'es', 'hr', 'cy', 'cz', 'dk', 'ee', 'fr', 'gf', 'de', 'gi', 'gr', 'gp', 'gg', 'hu',
            'is', 'ie', 'im', 'it', 'je', 'lv', 'li', 'lt', 'lu', 'pt', 'mt', 'mq', 'yt', 'mc', 'nl', 'no', 'pl', 'pt', 're',
            'ro', 'bl', 'mf', 'pm', 'sm', 'sk', 'sl', 'es', 'se', 'ch', 'gb', 'uk',
        ],
        'mybank' => [],
    ];

    const SUPPORTED_PHP_VERSION = '5.6';
    const NOTICE = 1;
    const WARNING = 2;
    const ERROR = 3;
    const CRASH = 4;

    const NAME = 'mollie';

    const PAYMENTSCREEN_LOCALE_BROWSER_LOCALE = 'browser_locale';
    const PAYMENTSCREEN_LOCALE_SEND_WEBSITE_LOCALE = 'website_locale';
    const DEFAULT_EMAIL_LANGUAGE_ISO_CODE = 'en';

    const LOGOS_BIG = 'big';
    const LOGOS_NORMAL = 'normal';
    const LOGOS_HIDE = 'hide';

    const ISSUERS_ON_CLICK = 'on-click';
    const ISSUERS_PAYMENT_PAGE = 'payment-page';
    const METHODS_CONFIG = 'MOLLIE_METHODS_CONFIG';

    const ENVIRONMENT_TEST = 0;
    const ENVIRONMENT_LIVE = 1;

    const DEBUG_LOG_NONE = 0;
    const DEBUG_LOG_ERRORS = 1;
    const DEBUG_LOG_ALL = 2;

    const MOLLIE_ENVIRONMENT = 'MOLLIE_ENVIRONMENT';
    const MOLLIE_API_KEY = 'MOLLIE_API_KEY';
    const MOLLIE_API_KEY_TEST = 'MOLLIE_API_KEY_TEST';
    const MOLLIE_API_KEY_TESTING_BUTTON = 'MOLLIE_API_KEY_TESTING_BUTTON';
    const MOLLIE_PROFILE_ID = 'MOLLIE_PROFILE_ID';
    const MOLLIE_ACCOUNT_SWITCH = 'MOLLIE_ACCOUNT_SWITCH';
    const MOLLIE_PAYMENTSCREEN_LOCALE = 'MOLLIE_PAYMENTSCREEN_LOCALE';
    const MOLLIE_SEND_ORDER_CONFIRMATION = 'MOLLIE_SEND_ORDER_CONFIRMATION';
    const MOLLIE_SEND_NEW_ORDER = 'MOLLIE_SEND_NEW_ORDER';
    const MOLLIE_IFRAME = 'MOLLIE_IFRAME';
    const MOLLIE_SINGLE_CLICK_PAYMENT = 'MOLLIE_SINGLE_CLICK_PAYMENT';
    const MOLLIE_IMAGES = 'MOLLIE_IMAGES';
    const MOLLIE_ISSUERS = 'MOLLIE_ISSUERS';
    const MOLLIE_CSS = 'MOLLIE_CSS';
    const MOLLIE_DEBUG_LOG = 'MOLLIE_DEBUG_LOG';
    const MOLLIE_QRENABLED = 'MOLLIE_QRENABLED';
    const MOLLIE_METHOD_COUNTRIES = 'MOLLIE_METHOD_COUNTRIES';
    const MOLLIE_METHOD_COUNTRIES_DISPLAY = 'MOLLIE_METHOD_COUNTRIES_DISPLAY';
    const MOLLIE_DISPLAY_ERRORS = 'MOLLIE_DISPLAY_ERRORS';
    const MOLLIE_TRACKING_URLS = 'MOLLIE_TRACKING_URLS_';
    const MOLLIE_AUTO_SHIP_MAIN = 'MOLLIE_AS_MAIN';
    const MOLLIE_AUTO_SHIP_STATUSES = 'MOLLIE_AS_STATUSES';
    const MOLLIE_STATUS_OPEN = 'MOLLIE_STATUS_OPEN';
    const MOLLIE_STATUS_PAID = 'MOLLIE_STATUS_PAID';
    const MOLLIE_STATUS_COMPLETED = 'MOLLIE_STATUS_COMPLETED';
    const MOLLIE_STATUS_CANCELED = 'MOLLIE_STATUS_CANCELED';
    const MOLLIE_STATUS_EXPIRED = 'MOLLIE_STATUS_EXPIRED';
    const MOLLIE_STATUS_PARTIAL_REFUND = 'MOLLIE_STATUS_PARTIAL_REFUND';
    const MOLLIE_STATUS_REFUNDED = 'MOLLIE_STATUS_REFUNDED';
    const MOLLIE_STATUS_SHIPPING = 'MOLLIE_STATUS_SHIPPING';
    const MOLLIE_MAIL_WHEN_SHIPPING = 'MOLLIE_MAIL_WHEN_SHIPPING';
    const MOLLIE_MAIL_WHEN_OPEN = 'MOLLIE_MAIL_WHEN_OPEN';
    const MOLLIE_MAIL_WHEN_PAID = 'MOLLIE_MAIL_WHEN_PAID';
    const MOLLIE_MAIL_WHEN_COMPLETED = 'MOLLIE_MAIL_WHEN_COMPLETED';
    const MOLLIE_MAIL_WHEN_CANCELED = 'MOLLIE_MAIL_WHEN_CANCELED';
    const MOLLIE_MAIL_WHEN_EXPIRED = 'MOLLIE_MAIL_WHEN_EXPIRED';
    const MOLLIE_MAIL_WHEN_REFUNDED = 'MOLLIE_MAIL_WHEN_REFUNDED';
    const PARTIAL_REFUND_CODE = 'partial_refund';
    const MOLLIE_STATUS_INITIATED = 'MOLLIE_STATUS_INITIATED';
    const MOLLIE_STATUS_PARTIALLY_SHIPPED = 'MOLLIE_PARTIALLY_SHIPPED';
    const MOLLIE_STATUS_ORDER_COMPLETED = 'MOLLIE_STATUS_ORDER_COMPLETED';

    const MOLLIE_CARRIER_URL_SOURCE = 'MOLLIE_CARRIER_URL_SOURCE_';
    const MOLLIE_CARRIER_CUSTOM_URL = 'MOLLIE_CARRIER_CUSTOM_URL_';

    const MOLLIE_METHOD_ENABLED = 'MOLLIE_METHOD_ENABLED_';
    const MOLLIE_METHOD_TITLE = 'MOLLIE_METHOD_TITLE_';
    const MOLLIE_METHOD_API = 'MOLLIE_METHOD_API_';
    const MOLLIE_METHOD_DESCRIPTION = 'MOLLIE_METHOD_DESCRIPTION_';
    const MOLLIE_METHOD_APPLICABLE_COUNTRIES = 'MOLLIE_METHOD_APPLICABLE_COUNTRIES_';
    const MOLLIE_METHOD_CERTAIN_COUNTRIES = 'MOLLIE_METHOD_CERTAIN_COUNTRIES_';
    const MOLLIE_METHOD_EXCLUDE_CERTAIN_COUNTRIES = 'MOLLIE_METHOD_EXCLUDE_CERTAIN_COUNTRIES_';
    const MOLLIE_METHOD_MINIMUM_ORDER_VALUE = 'MOLLIE_METHOD_MINIMUM_ORDER_VALUE_';
    const MOLLIE_METHOD_MAX_ORDER_VALUE = 'MOLLIE_METHOD_MAX_ORDER_VALUE_';
    const MOLLIE_METHOD_SURCHARGE_TYPE = 'MOLLIE_METHOD_SURCHARGE_TYPE_';
    const MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT = 'MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_';
    const MOLLIE_METHOD_SURCHARGE_PERCENTAGE = 'MOLLIE_METHOD_SURCHARGE_PERCENTAGE_';
    const MOLLIE_METHOD_SURCHARGE_LIMIT = 'MOLLIE_METHOD_SURCHARGE_LIMIT_';

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
    const VAT_RATE_ROUNDING_PRECISION = 0;

    const STATUS_PAID_ON_BACKORDER = "paid_backorder";
    const STATUS_PENDING_ON_BACKORDER = "pending_backorder";
    const STATUS_MOLLIE_AWAITING = 'mollie_awaiting';
    const STATUS_ON_BACKORDER = "on_backorder";
    const MOLLIE_AWAITING_PAYMENT = "awaiting";
    const PRICE_DISPLAY_METHOD_NO_TAXES = '1';
    const APPLEPAY = 'applepay';
    const MOLLIE_COUNTRIES = 'country_';

    const STATUS_PS_OS_OUTOFSTOCK_PAID = 'PS_OS_OUTOFSTOCK_PAID';

    const FEE_NO_FEE = 0;
    const FEE_FIXED_FEE = 1;
    const FEE_PERCENTAGE= 2;
    const FEE_FIXED_FEE_AND_PERCENTAGE = 3;

    const MOLLIE_API_STATUS_PAYMENT = "payment";
    const MOLLIE_API_STATUS_ORDER = "order";

    const ORDER_CONF_MAIL_SEND_ON_CREATION = 0;
    const ORDER_CONF_MAIL_SEND_ON_PAID = 1;
    const ORDER_CONF_MAIL_SEND_ON_NEVER = 2;

    const NEW_ORDER_MAIL_SEND_ON_CREATION = 0;
    const NEW_ORDER_MAIL_SEND_ON_PAID = 1;
    const NEW_ORDER_MAIL_SEND_ON_NEVER = 2;

    const CARTES_BANCAIRES = 'cartesbancaires';

    const MOLLIE_MODULE_UPGRADE_NOTICE_CLOSE_DATE = 'MOLLIE_MODULE_UPGRADE_NOTICE_CLOSE_DATE';
    const MODULE_UPGRADE_NOTICE_CLOSE_DURATION = 28;

    const MOLLIE_SHOW_CUSTOM_LOGO = 'MOLLIE_SHOW_CUSTOM_LOGO';

    const EMAIL_ALERTS_MODULE_NAME = 'ps_emailalerts';

    /** @var array $methods */
    public static $methods = [
        'banktransfer' => 'Bank',
        'belfius' => 'Belfius',
        'bitcoin' => 'Bitcoin',
        'cartesbancaires' => 'Cartes Bancaires',
        'creditcard' => 'Credit Card',
        'directdebit' => 'Direct Debit',
        'eps' => 'EPS',
        'giftcard' => 'Giftcard',
        'giropay' => 'Giropay',
        'ideal' => 'iDEAL',
        'inghomepay' => 'ING Homepay',
        'kbc' => 'KBC',
        'bancontact' => 'Bancontact',
        'paypal' => 'PayPal',
        'paysafecard' => 'Paysafecard',
        'sofort' => 'Sofort Banking',
        'klarnapaylater' => 'Pay later.',
        'klarnasliceit' => 'Slice it.',
        'applepay' => 'Apple Pay',
        'mybank' => 'MyBank',
    ];

    public static function getStatuses()
    {
        return [
            PaymentStatus::STATUS_PAID => Configuration::get(self::MOLLIE_STATUS_PAID),
            OrderStatus::STATUS_COMPLETED => Configuration::get(self::MOLLIE_STATUS_COMPLETED),
            PaymentStatus::STATUS_AUTHORIZED => Configuration::get(self::MOLLIE_STATUS_PAID),
            PaymentStatus::STATUS_CANCELED => Configuration::get(self::MOLLIE_STATUS_CANCELED),
            PaymentStatus::STATUS_EXPIRED    => Configuration::get(self::MOLLIE_STATUS_EXPIRED),
            RefundStatus::STATUS_REFUNDED => Configuration::get(self::MOLLIE_STATUS_REFUNDED),
            PaymentStatus::STATUS_OPEN => Configuration::get(self::MOLLIE_STATUS_OPEN),
            PaymentStatus::STATUS_FAILED => Configuration::get(self::MOLLIE_STATUS_CANCELED),
            PaymentStatus::STATUS_PENDING => Configuration::get(self::STATUS_MOLLIE_AWAITING),
            OrderStatus::STATUS_SHIPPING => Configuration::get(self::MOLLIE_STATUS_SHIPPING),
            self::MOLLIE_AWAITING_PAYMENT => Configuration::get(self::STATUS_MOLLIE_AWAITING),
            self::PARTIAL_REFUND_CODE => Configuration::get(self::MOLLIE_STATUS_PARTIAL_REFUND),
            'created' => Configuration::get(self::MOLLIE_STATUS_OPEN),
            self::STATUS_PAID_ON_BACKORDER => Configuration::get('PS_OS_OUTOFSTOCK_PAID'),
            self::STATUS_PENDING_ON_BACKORDER => Configuration::get('PS_OS_OUTOFSTOCK_UNPAID'),
            self::STATUS_ON_BACKORDER => Configuration::get('PS_OS_OUTOFSTOCK'),
        ];
    }

    public static function isVersion17()
    {
        return (bool)version_compare(_PS_VERSION_, '1.7', '>=');
    }

    public static function isTestMode()
    {
        $apiKey = EnvironmentUtility::getApiKey();
        if (strpos($apiKey, 'test') === 0) {
            return true;
        }

        return false;
    }

    public static function getMollieOrderStatuses()
    {
        return [
            self::MOLLIE_STATUS_PARTIALLY_SHIPPED,
            self::MOLLIE_STATUS_PARTIAL_REFUND,
            self::STATUS_MOLLIE_AWAITING,
            self::MOLLIE_STATUS_ORDER_COMPLETED,
        ];
    }
}
