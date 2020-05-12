<?php

namespace Mollie\Service;

use Configuration;
use Mollie;
use Mollie\Config\Config;
use Mollie\Repository\CountryRepository;

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

    public function __construct(
        Mollie $module,
        ApiService $apiService,
        CountryRepository $countryRepository
    ) {
        $this->module = $module;
        $this->apiService = $apiService;
        $this->countryRepository = $countryRepository;
    }

    /**
     * @return array
     */
    public function getConfigFieldsValues()
    {
        $configFields = [
            Config::MOLLIE_API_KEY => Configuration::get(Config::MOLLIE_API_KEY),
            Config::MOLLIE_PROFILE_ID => Configuration::get(Config::MOLLIE_PROFILE_ID),
            Config::MOLLIE_PAYMENTSCREEN_LOCALE => Configuration::get(Config::MOLLIE_PAYMENTSCREEN_LOCALE),
            Config::MOLLIE_IFRAME => Configuration::get(Config::MOLLIE_IFRAME),

            Config::MOLLIE_CSS => Configuration::get(Config::MOLLIE_CSS),
            Config::MOLLIE_IMAGES => Configuration::get(Config::MOLLIE_IMAGES),
            Config::MOLLIE_ISSUERS => Configuration::get(Config::MOLLIE_ISSUERS),

            Config::MOLLIE_QRENABLED => Configuration::get(Config::MOLLIE_QRENABLED),
            Config::MOLLIE_METHOD_COUNTRIES => Configuration::get(Config::MOLLIE_METHOD_COUNTRIES),
            Config::MOLLIE_METHOD_COUNTRIES_DISPLAY => Configuration::get(Config::MOLLIE_METHOD_COUNTRIES_DISPLAY),

            Config::MOLLIE_STATUS_OPEN => Configuration::get(Config::MOLLIE_STATUS_OPEN),
            Config::MOLLIE_STATUS_PAID => Configuration::get(Config::MOLLIE_STATUS_PAID),
            Config::MOLLIE_STATUS_CANCELED => Configuration::get(Config::MOLLIE_STATUS_CANCELED),
            Config::MOLLIE_STATUS_EXPIRED => Configuration::get(Config::MOLLIE_STATUS_EXPIRED),
            Config::MOLLIE_STATUS_PARTIAL_REFUND => Configuration::get(Config::MOLLIE_STATUS_PARTIAL_REFUND),
            Config::MOLLIE_STATUS_REFUNDED => Configuration::get(Config::MOLLIE_STATUS_REFUNDED),
            Config::MOLLIE_MAIL_WHEN_OPEN => Configuration::get(Config::MOLLIE_MAIL_WHEN_OPEN),
            Config::MOLLIE_MAIL_WHEN_PAID => Configuration::get(Config::MOLLIE_MAIL_WHEN_PAID),
            Config::MOLLIE_MAIL_WHEN_CANCELED => Configuration::get(Config::MOLLIE_MAIL_WHEN_CANCELED),
            Config::MOLLIE_MAIL_WHEN_EXPIRED => Configuration::get(Config::MOLLIE_MAIL_WHEN_EXPIRED),
            Config::MOLLIE_MAIL_WHEN_REFUNDED => Configuration::get(Config::MOLLIE_MAIL_WHEN_REFUNDED),
            Config::MOLLIE_ACCOUNT_SWITCH => Configuration::get(Config::MOLLIE_ACCOUNT_SWITCH),

            Config::MOLLIE_DISPLAY_ERRORS => Configuration::get(Config::MOLLIE_DISPLAY_ERRORS),
            Config::MOLLIE_DEBUG_LOG => Configuration::get(Config::MOLLIE_DEBUG_LOG),
            Config::MOLLIE_API => Configuration::get(Config::MOLLIE_API),

            Config::MOLLIE_AUTO_SHIP_MAIN => Configuration::get(Config::MOLLIE_AUTO_SHIP_MAIN),
        ];

        if (Configuration::get(Config::MOLLIE_API_KEY)) {
            foreach ($this->apiService->getMethodsForConfig($this->module->api, $this->module->getPathUri()) as $method) {
                $countryIds = $this->countryRepository->getMethodCountryIds($method['id']);
                if ($countryIds) {
                    $configFields = array_merge($configFields, [Config::MOLLIE_COUNTRIES . $method['id'] . '[]' => $countryIds]);
                    continue;
                }
                $configFields = array_merge($configFields, [Config::MOLLIE_COUNTRIES . $method['id'] . '[]' => []]);
            }
        }

        $checkStatuses = [];
        if (Configuration::get(Config::MOLLIE_AUTO_SHIP_STATUSES)) {
            $checkConfs = @json_decode(Configuration::get(Config::MOLLIE_AUTO_SHIP_STATUSES), true);
        }
        if (!isset($checkConfs) || !is_array($checkConfs)) {
            $checkConfs = [];
        }

        foreach ($checkConfs as $conf) {
            $checkStatuses[Config::MOLLIE_AUTO_SHIP_STATUSES . '_' . (int)$conf] = true;
        }

        $configFields = array_merge($configFields, $checkStatuses);

        return $configFields;
    }

}