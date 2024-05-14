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

namespace Mollie\Service;

use Mollie;
use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Config\Config;
use Mollie\Repository\CountryRepository;
use Mollie\Utility\EnvironmentUtility;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ConfigFieldService
{
    /**
     * @var Mollie
     */
    private $module;
    /**
     * @var ApiService
     */
    private $apiService;
    /**
     * @var CountryRepository
     */
    private $countryRepository;
    /** @var ConfigurationAdapter */
    private $configurationAdapter;

    public function __construct(
        Mollie $module,
        ApiService $apiService,
        CountryRepository $countryRepository,
        ConfigurationAdapter $configurationAdapter
    ) {
        $this->module = $module;
        $this->apiService = $apiService;
        $this->countryRepository = $countryRepository;
        $this->configurationAdapter = $configurationAdapter;
    }

    /**
     * @return array
     */
    public function getConfigFieldsValues()
    {
        $configFields = [
            Config::MOLLIE_ENVIRONMENT => $this->configurationAdapter->get(Config::MOLLIE_ENVIRONMENT),
            Config::MOLLIE_API_KEY => $this->configurationAdapter->get(Config::MOLLIE_API_KEY),
            Config::MOLLIE_API_KEY_TEST => $this->configurationAdapter->get(Config::MOLLIE_API_KEY_TEST),
            Config::MOLLIE_PAYMENTSCREEN_LOCALE => $this->configurationAdapter->get(Config::MOLLIE_PAYMENTSCREEN_LOCALE),
            Config::MOLLIE_SEND_ORDER_CONFIRMATION => $this->configurationAdapter->get(Config::MOLLIE_SEND_ORDER_CONFIRMATION),
            Config::MOLLIE_IFRAME[(int) $this->configurationAdapter->get(Config::MOLLIE_ENVIRONMENT) ? 'production' : 'sandbox'] => $this->configurationAdapter->get(Config::MOLLIE_IFRAME),
            Config::MOLLIE_SINGLE_CLICK_PAYMENT[(int) $this->configurationAdapter->get(Config::MOLLIE_ENVIRONMENT) ? 'production' : 'sandbox'] => $this->configurationAdapter->get(Config::MOLLIE_SINGLE_CLICK_PAYMENT),

            Config::MOLLIE_CSS => $this->configurationAdapter->get(Config::MOLLIE_CSS),
            Config::MOLLIE_IMAGES => $this->configurationAdapter->get(Config::MOLLIE_IMAGES),
            Config::MOLLIE_SHOW_RESEND_PAYMENT_LINK => $this->configurationAdapter->get(Config::MOLLIE_SHOW_RESEND_PAYMENT_LINK),
            Config::MOLLIE_ISSUERS[(int) $this->configurationAdapter->get(Config::MOLLIE_ENVIRONMENT) ? 'production' : 'sandbox'] => $this->configurationAdapter->get(Config::MOLLIE_ISSUERS),

            Config::MOLLIE_METHOD_COUNTRIES => $this->configurationAdapter->get(Config::MOLLIE_METHOD_COUNTRIES),
            Config::MOLLIE_METHOD_COUNTRIES_DISPLAY => $this->configurationAdapter->get(Config::MOLLIE_METHOD_COUNTRIES_DISPLAY),

            Config::MOLLIE_STATUS_OPEN => $this->configurationAdapter->get(Config::MOLLIE_STATUS_OPEN),
            Config::MOLLIE_STATUS_AWAITING => $this->configurationAdapter->get(Config::MOLLIE_STATUS_AWAITING),
            Config::MOLLIE_STATUS_PAID => $this->configurationAdapter->get(Config::MOLLIE_STATUS_PAID),
            Config::MOLLIE_STATUS_COMPLETED => $this->configurationAdapter->get(Config::MOLLIE_STATUS_COMPLETED),
            Config::MOLLIE_STATUS_CANCELED => $this->configurationAdapter->get(Config::MOLLIE_STATUS_CANCELED),
            Config::MOLLIE_STATUS_EXPIRED => $this->configurationAdapter->get(Config::MOLLIE_STATUS_EXPIRED),
            Config::MOLLIE_STATUS_PARTIAL_REFUND => $this->configurationAdapter->get(Config::MOLLIE_STATUS_PARTIAL_REFUND),
            Config::MOLLIE_STATUS_REFUNDED => $this->configurationAdapter->get(Config::MOLLIE_STATUS_REFUNDED),
            Config::MOLLIE_STATUS_CHARGEBACK => $this->configurationAdapter->get(Config::MOLLIE_STATUS_CHARGEBACK),
            Config::MOLLIE_MAIL_WHEN_OPEN => $this->configurationAdapter->get(Config::MOLLIE_MAIL_WHEN_OPEN),
            Config::MOLLIE_MAIL_WHEN_AWAITING => $this->configurationAdapter->get(Config::MOLLIE_MAIL_WHEN_AWAITING),
            Config::MOLLIE_MAIL_WHEN_PAID => $this->configurationAdapter->get(Config::MOLLIE_MAIL_WHEN_PAID),
            Config::MOLLIE_MAIL_WHEN_COMPLETED => $this->configurationAdapter->get(Config::MOLLIE_MAIL_WHEN_COMPLETED),
            Config::MOLLIE_MAIL_WHEN_CANCELED => $this->configurationAdapter->get(Config::MOLLIE_MAIL_WHEN_CANCELED),
            Config::MOLLIE_MAIL_WHEN_EXPIRED => $this->configurationAdapter->get(Config::MOLLIE_MAIL_WHEN_EXPIRED),
            Config::MOLLIE_MAIL_WHEN_REFUNDED => $this->configurationAdapter->get(Config::MOLLIE_MAIL_WHEN_REFUNDED),
            Config::MOLLIE_MAIL_WHEN_CHARGEBACK => $this->configurationAdapter->get(Config::MOLLIE_MAIL_WHEN_CHARGEBACK),
            Config::MOLLIE_ACCOUNT_SWITCH => $this->configurationAdapter->get(Config::MOLLIE_ACCOUNT_SWITCH),

            Config::MOLLIE_DISPLAY_ERRORS => $this->configurationAdapter->get(Config::MOLLIE_DISPLAY_ERRORS),
            Config::MOLLIE_DEBUG_LOG => $this->configurationAdapter->get(Config::MOLLIE_DEBUG_LOG),
            Config::MOLLIE_API => $this->configurationAdapter->get(Config::MOLLIE_API),

            Config::MOLLIE_AUTO_SHIP_MAIN => $this->configurationAdapter->get(Config::MOLLIE_AUTO_SHIP_MAIN),

            Config::MOLLIE_STATUS_SHIPPING => $this->configurationAdapter->get(Config::MOLLIE_STATUS_SHIPPING),
            Config::MOLLIE_MAIL_WHEN_SHIPPING => $this->configurationAdapter->get(Config::MOLLIE_MAIL_WHEN_SHIPPING),
            Config::MOLLIE_AUTHORIZABLE_PAYMENT_INVOICE_ON_STATUS => $this->configurationAdapter->get(Config::MOLLIE_AUTHORIZABLE_PAYMENT_INVOICE_ON_STATUS),
        ];

        if (EnvironmentUtility::getApiKey() && $this->module->getApiClient() !== null) {
            foreach ($this->apiService->getMethodsForConfig($this->module->getApiClient()) as $method) {
                $countryIds = $this->countryRepository->getMethodCountryIds($method['id']);

                if ($countryIds) {
                    $configFields[Config::MOLLIE_COUNTRIES . $method['id'] . '[]'] = $countryIds;

                    continue;
                }

                $configFields[Config::MOLLIE_COUNTRIES . $method['id'] . '[]'] = [];
            }
        }

        $checkStatuses = [];

        if ($this->configurationAdapter->get(Config::MOLLIE_AUTO_SHIP_STATUSES)) {
            $checkConfs = @json_decode($this->configurationAdapter->get(Config::MOLLIE_AUTO_SHIP_STATUSES), true);
        }

        if (!isset($checkConfs) || !is_array($checkConfs)) {
            $checkConfs = [];
        }

        foreach ($checkConfs as $conf) {
            $checkStatuses[Config::MOLLIE_AUTO_SHIP_STATUSES . '_' . (int) $conf] = true;
        }

        return array_merge($configFields, $checkStatuses);
    }
}
