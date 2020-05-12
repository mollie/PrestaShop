<?php

namespace Mollie\Install;

use Configuration;
use Mollie;
use Mollie\Config\Config;

class Uninstall
{
    /**
     * @var array
     */
    private $errors = [];

    /**
     * @var Mollie
     */
    private $module;

    public function __construct(Mollie $module)
    {
        $this->module = $module;
    }

    public function uninstall()
    {
        $this->deleteConfig();

        include(dirname(__FILE__) . '/../../sql/uninstall.php');

        return true;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    private function deleteConfig()
    {
        Configuration::deleteByName(Config::MOLLIE_API_KEY);
        Configuration::deleteByName(Config::MOLLIE_PROFILE_ID);
        Configuration::deleteByName(Config::MOLLIE_PAYMENTSCREEN_LOCALE);
        Configuration::deleteByName(Config::MOLLIE_IFRAME);
        Configuration::deleteByName(Config::MOLLIE_IMAGES);
        Configuration::deleteByName(Config::MOLLIE_ISSUERS);
        Configuration::deleteByName(Config::MOLLIE_CSS);
        Configuration::deleteByName(Config::MOLLIE_DEBUG_LOG);
        Configuration::deleteByName(Config::MOLLIE_QRENABLED);
        Configuration::deleteByName(Config::MOLLIE_DISPLAY_ERRORS);
        Configuration::deleteByName(Config::MOLLIE_STATUS_OPEN);
        Configuration::deleteByName(Config::MOLLIE_STATUS_PAID);
        Configuration::deleteByName(Config::MOLLIE_STATUS_CANCELED);
        Configuration::deleteByName(Config::MOLLIE_STATUS_EXPIRED);
        Configuration::deleteByName(Config::MOLLIE_STATUS_PARTIAL_REFUND);
        Configuration::deleteByName(Config::MOLLIE_STATUS_REFUNDED);
        Configuration::deleteByName(Config::MOLLIE_MAIL_WHEN_OPEN);
        Configuration::deleteByName(Config::MOLLIE_MAIL_WHEN_PAID);
        Configuration::deleteByName(Config::MOLLIE_MAIL_WHEN_CANCELED);
        Configuration::deleteByName(Config::MOLLIE_MAIL_WHEN_EXPIRED);
        Configuration::deleteByName(Config::MOLLIE_MAIL_WHEN_REFUNDED);
        Configuration::deleteByName(Config::MOLLIE_ACCOUNT_SWITCH);
        Configuration::deleteByName(Config::MOLLIE_METHOD_COUNTRIES);
        Configuration::deleteByName(Config::MOLLIE_METHOD_COUNTRIES_DISPLAY);
        Configuration::deleteByName(Config::MOLLIE_API);
        Configuration::deleteByName(Config::MOLLIE_AUTO_SHIP_STATUSES);
        Configuration::deleteByName(Config::MOLLIE_TRACKING_URLS);
        Configuration::deleteByName(Config::MOLLIE_METHODS_LAST_CHECK);
        Configuration::deleteByName(Config::METHODS_CONFIG);
    }
}