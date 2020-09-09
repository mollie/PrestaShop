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
            Config::MOLLIE_ENVIRONMENT => Configuration::get(Config::MOLLIE_ENVIRONMENT),
            Config::MOLLIE_API_KEY => Configuration::get(Config::MOLLIE_API_KEY),
            Config::MOLLIE_API_KEY_TEST => Configuration::get(Config::MOLLIE_API_KEY_TEST),
            Config::MOLLIE_PROFILE_ID => Configuration::get(Config::MOLLIE_PROFILE_ID),
            Config::MOLLIE_PAYMENTSCREEN_LOCALE => Configuration::get(Config::MOLLIE_PAYMENTSCREEN_LOCALE),
            Config::MOLLIE_SEND_ORDER_CONFIRMATION => Configuration::get(Config::MOLLIE_SEND_ORDER_CONFIRMATION),
            Config::MOLLIE_SEND_NEW_ORDER => Configuration::get(Config::MOLLIE_SEND_NEW_ORDER),
            Config::MOLLIE_IFRAME => Configuration::get(Config::MOLLIE_IFRAME),
            Config::MOLLIE_SINGLE_CLICK_PAYMENT => Configuration::get(Config::MOLLIE_SINGLE_CLICK_PAYMENT),

            Config::MOLLIE_CSS => Configuration::get(Config::MOLLIE_CSS),
            Config::MOLLIE_IMAGES => Configuration::get(Config::MOLLIE_IMAGES),
            Config::MOLLIE_ISSUERS => Configuration::get(Config::MOLLIE_ISSUERS),

            Config::MOLLIE_QRENABLED => Configuration::get(Config::MOLLIE_QRENABLED),
            Config::MOLLIE_METHOD_COUNTRIES => Configuration::get(Config::MOLLIE_METHOD_COUNTRIES),
            Config::MOLLIE_METHOD_COUNTRIES_DISPLAY => Configuration::get(Config::MOLLIE_METHOD_COUNTRIES_DISPLAY),

            Config::MOLLIE_STATUS_OPEN => Configuration::get(Config::MOLLIE_STATUS_OPEN),
            Config::MOLLIE_STATUS_PAID => Configuration::get(Config::MOLLIE_STATUS_PAID),
            Config::MOLLIE_STATUS_COMPLETED => Configuration::get(Config::MOLLIE_STATUS_COMPLETED),
            Config::MOLLIE_STATUS_CANCELED => Configuration::get(Config::MOLLIE_STATUS_CANCELED),
            Config::MOLLIE_STATUS_EXPIRED => Configuration::get(Config::MOLLIE_STATUS_EXPIRED),
            Config::MOLLIE_STATUS_PARTIAL_REFUND => Configuration::get(Config::MOLLIE_STATUS_PARTIAL_REFUND),
            Config::MOLLIE_STATUS_REFUNDED => Configuration::get(Config::MOLLIE_STATUS_REFUNDED),
            Config::MOLLIE_MAIL_WHEN_OPEN => Configuration::get(Config::MOLLIE_MAIL_WHEN_OPEN),
            Config::MOLLIE_MAIL_WHEN_PAID => Configuration::get(Config::MOLLIE_MAIL_WHEN_PAID),
            Config::MOLLIE_MAIL_WHEN_COMPLETED => Configuration::get(Config::MOLLIE_MAIL_WHEN_COMPLETED),
            Config::MOLLIE_MAIL_WHEN_CANCELED => Configuration::get(Config::MOLLIE_MAIL_WHEN_CANCELED),
            Config::MOLLIE_MAIL_WHEN_EXPIRED => Configuration::get(Config::MOLLIE_MAIL_WHEN_EXPIRED),
            Config::MOLLIE_MAIL_WHEN_REFUNDED => Configuration::get(Config::MOLLIE_MAIL_WHEN_REFUNDED),
            Config::MOLLIE_ACCOUNT_SWITCH => Configuration::get(Config::MOLLIE_ACCOUNT_SWITCH),

            Config::MOLLIE_DISPLAY_ERRORS => Configuration::get(Config::MOLLIE_DISPLAY_ERRORS),
            Config::MOLLIE_DEBUG_LOG => Configuration::get(Config::MOLLIE_DEBUG_LOG),
            Config::MOLLIE_API => Configuration::get(Config::MOLLIE_API),

            Config::MOLLIE_AUTO_SHIP_MAIN => Configuration::get(Config::MOLLIE_AUTO_SHIP_MAIN),

            Config::MOLLIE_STATUS_SHIPPING => Configuration::get(Config::MOLLIE_STATUS_SHIPPING),
            Config::MOLLIE_MAIL_WHEN_SHIPPING => Configuration::get(Config::MOLLIE_MAIL_WHEN_SHIPPING),
        ];

        if (Mollie\Utility\EnvironmentUtility::getApiKey()) {
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
