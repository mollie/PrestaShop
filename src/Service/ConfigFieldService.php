<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 *
 * @see        https://github.com/mollie/PrestaShop
 *
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
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
			Config::MOLLIE_SHOW_RESEND_PAYMENT_LINK => Configuration::get(Config::MOLLIE_SHOW_RESEND_PAYMENT_LINK),
			Config::MOLLIE_ISSUERS => Configuration::get(Config::MOLLIE_ISSUERS),

			Config::MOLLIE_METHOD_COUNTRIES => Configuration::get(Config::MOLLIE_METHOD_COUNTRIES),
			Config::MOLLIE_METHOD_COUNTRIES_DISPLAY => Configuration::get(Config::MOLLIE_METHOD_COUNTRIES_DISPLAY),

			Config::MOLLIE_STATUS_OPEN => Configuration::get(Config::MOLLIE_STATUS_OPEN),
			Config::MOLLIE_STATUS_AWAITING => Configuration::get(Config::MOLLIE_STATUS_AWAITING),
			Config::MOLLIE_STATUS_PAID => Configuration::get(Config::MOLLIE_STATUS_PAID),
			Config::MOLLIE_STATUS_COMPLETED => Configuration::get(Config::MOLLIE_STATUS_COMPLETED),
			Config::MOLLIE_STATUS_CANCELED => Configuration::get(Config::MOLLIE_STATUS_CANCELED),
			Config::MOLLIE_STATUS_EXPIRED => Configuration::get(Config::MOLLIE_STATUS_EXPIRED),
			Config::MOLLIE_STATUS_PARTIAL_REFUND => Configuration::get(Config::MOLLIE_STATUS_PARTIAL_REFUND),
			Config::MOLLIE_STATUS_REFUNDED => Configuration::get(Config::MOLLIE_STATUS_REFUNDED),
			Config::MOLLIE_MAIL_WHEN_OPEN => Configuration::get(Config::MOLLIE_MAIL_WHEN_OPEN),
			Config::MOLLIE_MAIL_WHEN_AWAITING => Configuration::get(Config::MOLLIE_MAIL_WHEN_AWAITING),
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
			Config::MOLLIE_KLARNA_INVOICE_ON => Configuration::get(Config::MOLLIE_KLARNA_INVOICE_ON),
		];

		if (Mollie\Utility\EnvironmentUtility::getApiKey() && $this->module->api !== null) {
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
			$checkStatuses[Config::MOLLIE_AUTO_SHIP_STATUSES . '_' . (int) $conf] = true;
		}

		$configFields = array_merge($configFields, $checkStatuses);

		return $configFields;
	}
}
