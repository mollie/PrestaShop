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

use Carrier;
use Configuration;
use Context;
use Exception;
use Mollie;
use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\Types\PaymentStatus;
use Mollie\Config\Config;
use Mollie\Exception\MollieException;
use Mollie\Handler\OrderTotal\OrderTotalUpdaterHandlerInterface;
use Mollie\Handler\Settings\PaymentMethodPositionHandlerInterface;
use Mollie\Repository\CountryRepository;
use Mollie\Repository\PaymentMethodRepository;
use MolPaymentMethodIssuer;
use OrderState;
use PrestaShopDatabaseException;
use PrestaShopException;
use Tools;

class SettingsSaveService
{
	/**
	 * @var Mollie
	 */
	private $module;

	/**
	 * @var CountryRepository
	 */
	private $countryRepository;

	/**
	 * @var PaymentMethodRepository
	 */
	private $paymentMethodRepository;

	/**
	 * @var PaymentMethodService
	 */
	private $paymentMethodService;

	/**
	 * @var ApiKeyService
	 */
	private $apiKeyService;

	/**
	 * @var MolCarrierInformationService
	 */
	private $carrierInformationService;

	/**
	 * @var PaymentMethodPositionHandlerInterface
	 */
	private $paymentMethodPositionHandler;

	/**
	 * @var ApiService
	 */
	private $apiService;

	/**
	 * @var OrderTotalUpdaterHandlerInterface
	 */
	private $orderTotalRestrictionService;

	public function __construct(
		Mollie $module,
		CountryRepository $countryRepository,
		PaymentMethodRepository $paymentMethodRepository,
		PaymentMethodService $paymentMethodService,
		ApiService $apiService,
		MolCarrierInformationService $carrierInformationService,
		PaymentMethodPositionHandlerInterface $paymentMethodPositionHandler,
		ApiKeyService $apiKeyService,
		OrderTotalUpdaterHandlerInterface $orderTotalRestrictionService
	) {
		$this->module = $module;
		$this->countryRepository = $countryRepository;
		$this->paymentMethodRepository = $paymentMethodRepository;
		$this->paymentMethodService = $paymentMethodService;
		$this->apiKeyService = $apiKeyService;
		$this->carrierInformationService = $carrierInformationService;
		$this->paymentMethodPositionHandler = $paymentMethodPositionHandler;
		$this->apiService = $apiService;
		$this->orderTotalRestrictionService = $orderTotalRestrictionService;
	}

	/**
	 * @param array $errors
	 *
	 * @return array
	 *
	 * @throws ApiException
	 * @throws PrestaShopDatabaseException
	 * @throws PrestaShopException
	 */
	public function saveSettings(&$errors = [])
	{
		$oldEnvironment = (int) Configuration::get(Config::MOLLIE_ENVIRONMENT);
		$environment = (int) Tools::getValue(Config::MOLLIE_ENVIRONMENT);
		$mollieApiKey = Tools::getValue(Config::MOLLIE_API_KEY);
		$mollieApiKeyTest = Tools::getValue(Config::MOLLIE_API_KEY_TEST);
		$mollieProfileId = Tools::getValue(Config::MOLLIE_PROFILE_ID);
		$paymentOptionPositions = Tools::getValue(Config::MOLLIE_FORM_PAYMENT_OPTION_POSITION);

		if ($paymentOptionPositions) {
			$this->paymentMethodPositionHandler->savePositions($paymentOptionPositions);
		}

		$apiKey = Config::ENVIRONMENT_LIVE === (int) $environment ? $mollieApiKey : $mollieApiKeyTest;
		$isApiKeyIncorrect = 0 !== strpos($apiKey, 'live') && 0 !== strpos($apiKey, 'test');

		if ($isApiKeyIncorrect) {
			$errors[] = $this->module->l('The API key needs to start with test or live.');
		}

		if (Tools::getValue(Config::METHODS_CONFIG) && json_decode(Tools::getValue(Config::METHODS_CONFIG))) {
			Configuration::updateValue(
				Config::METHODS_CONFIG,
				json_encode(@json_decode(Tools::getValue(Config::METHODS_CONFIG)))
			);
		}

		if ($oldEnvironment === $environment && $apiKey && $this->module->api !== null) {
			$savedPaymentMethods = [];
			foreach ($this->apiService->getMethodsForConfig($this->module->api, $this->module->getPathUri()) as $method) {
				try {
					$paymentMethod = $this->paymentMethodService->savePaymentMethod($method);
					$savedPaymentMethods[] = $paymentMethod->id_method;
				} catch (Exception $e) {
					$errors[] = $this->module->l('Something went wrong. Couldn\'t save your payment methods') . ":{$method['id']}";
					continue;
				}

				if (!$this->paymentMethodRepository->deletePaymentMethodIssuersByPaymentMethodId($paymentMethod->id)) {
					$errors[] = $this->module->l('Something went wrong. Couldn\'t delete old payment methods issuers') . ":{$method['id']}";
					continue;
				}

				if ($method['issuers']) {
					$paymentMethodIssuer = new MolPaymentMethodIssuer();
					$paymentMethodIssuer->issuers_json = json_encode($method['issuers']);
					$paymentMethodIssuer->id_payment_method = $paymentMethod->id;
					try {
						$paymentMethodIssuer->add();
					} catch (Exception $e) {
						$errors[] = $this->module->l('Something went wrong. Couldn\'t save your payment methods issuer');
					}
				}

				$countries = Tools::getValue(Config::MOLLIE_METHOD_CERTAIN_COUNTRIES . $method['id']);
				$excludedCountries = Tools::getValue(
					Config::MOLLIE_METHOD_EXCLUDE_CERTAIN_COUNTRIES . $method['id']
				);
				$this->countryRepository->updatePaymentMethodCountries($method['id'], $countries);
				$this->countryRepository->updatePaymentMethodExcludedCountries($method['id'], $excludedCountries);
			}
			$this->paymentMethodRepository->deleteOldPaymentMethods($savedPaymentMethods, $environment);
		}

		$useCustomLogo = Tools::getValue(Config::MOLLIE_SHOW_CUSTOM_LOGO);
		Configuration::updateValue(
			Config::MOLLIE_SHOW_CUSTOM_LOGO,
			$useCustomLogo
		);

		$molliePaymentscreenLocale = Tools::getValue(Config::MOLLIE_PAYMENTSCREEN_LOCALE);
		$mollieOrderConfirmationSand = Tools::getValue(Config::MOLLIE_SEND_ORDER_CONFIRMATION);
		$mollieNewOrderSand = Tools::getValue(Config::MOLLIE_SEND_NEW_ORDER);
		$mollieIFrameEnabled = Tools::getValue(Config::MOLLIE_IFRAME);
		$mollieSingleClickPaymentEnabled = Tools::getValue(Config::MOLLIE_SINGLE_CLICK_PAYMENT);
		$mollieImages = Tools::getValue(Config::MOLLIE_IMAGES);
		$showResentPayment = Tools::getValue(Config::MOLLIE_SHOW_RESEND_PAYMENT_LINK);
		$mollieIssuers = Tools::getValue(Config::MOLLIE_ISSUERS);
		$mollieCss = Tools::getValue(Config::MOLLIE_CSS);
		if (!isset($mollieCss)) {
			$mollieCss = '';
		}
		$mollieLogger = Tools::getValue(Config::MOLLIE_DEBUG_LOG);
		$mollieApi = Tools::getValue(Config::MOLLIE_API);
		$mollieMethodCountriesEnabled = (bool) Tools::getValue(Config::MOLLIE_METHOD_COUNTRIES);
		$mollieMethodCountriesDisplayEnabled = (bool) Tools::getValue(Config::MOLLIE_METHOD_COUNTRIES_DISPLAY);
		$mollieErrors = Tools::getValue(Config::MOLLIE_DISPLAY_ERRORS);
		$voucherCategory = Tools::getValue(Config::MOLLIE_VOUCHER_CATEGORY);

		$mollieShipMain = Tools::getValue(Config::MOLLIE_AUTO_SHIP_MAIN);
		if (!isset($mollieErrors)) {
			$mollieErrors = false;
		} else {
			$mollieErrors = (1 == $mollieErrors);
		}

		$apiKey = Config::ENVIRONMENT_LIVE === (int) $environment ?
			$mollieApiKey : $mollieApiKeyTest;

		if ($apiKey) {
			try {
				$api = $this->apiKeyService->setApiKey($apiKey, $this->module->version);
				if (null === $api) {
					throw new MollieException('Failed to connect to mollie API', MollieException::API_CONNECTION_EXCEPTION);
				}
				$this->module->api = $api;
			} catch (Exception $e) {
				$errors[] = $e->getMessage();
				Configuration::updateValue(Config::MOLLIE_API_KEY, null);

				return [$this->module->l('Wrong API Key!')];
			}
		}
		$this->handleKlarnaInvoiceStatus();

		try {
			if (!$this->orderTotalRestrictionService->handleOrderTotalUpdate()) {
				$resultMessage[] = $this->module->l('Failed to update restrictions for payment methods');
			}
		} catch (Mollie\Exception\OrderTotalRestrictionException $e) {
			$resultMessage[] = $e->getMessage();
		}
		if (empty($errors)) {
			Configuration::updateValue(Config::MOLLIE_API_KEY, $mollieApiKey);
			Configuration::updateValue(Config::MOLLIE_API_KEY_TEST, $mollieApiKeyTest);
			Configuration::updateValue(Config::MOLLIE_ENVIRONMENT, $environment);
			Configuration::updateValue(Config::MOLLIE_PROFILE_ID, $mollieProfileId);
			Configuration::updateValue(Config::MOLLIE_PAYMENTSCREEN_LOCALE, $molliePaymentscreenLocale);
			Configuration::updateValue(Config::MOLLIE_SEND_ORDER_CONFIRMATION, $mollieOrderConfirmationSand);
			Configuration::updateValue(Config::MOLLIE_SEND_NEW_ORDER, $mollieNewOrderSand);
			Configuration::updateValue(Config::MOLLIE_IFRAME, $mollieIFrameEnabled);
			Configuration::updateValue(Config::MOLLIE_SINGLE_CLICK_PAYMENT, $mollieSingleClickPaymentEnabled);
			Configuration::updateValue(Config::MOLLIE_IMAGES, $mollieImages);
			Configuration::updateValue(Config::MOLLIE_SHOW_RESEND_PAYMENT_LINK, $showResentPayment);
			Configuration::updateValue(Config::MOLLIE_ISSUERS, $mollieIssuers);
			Configuration::updateValue(Config::MOLLIE_METHOD_COUNTRIES, (bool) $mollieMethodCountriesEnabled);
			Configuration::updateValue(Config::MOLLIE_METHOD_COUNTRIES_DISPLAY, (bool) $mollieMethodCountriesDisplayEnabled);
			Configuration::updateValue(Config::MOLLIE_CSS, $mollieCss);
			Configuration::updateValue(Config::MOLLIE_DISPLAY_ERRORS, (int) $mollieErrors);
			Configuration::updateValue(Config::MOLLIE_DEBUG_LOG, (int) $mollieLogger);
			Configuration::updateValue(Config::MOLLIE_API, $mollieApi);
			Configuration::updateValue(Config::MOLLIE_VOUCHER_CATEGORY, $voucherCategory);
			Configuration::updateValue(
				Config::MOLLIE_AUTO_SHIP_STATUSES,
				json_encode($this->getStatusesValue(Config::MOLLIE_AUTO_SHIP_STATUSES))
			);
			Configuration::updateValue(Config::MOLLIE_AUTO_SHIP_MAIN, (bool) $mollieShipMain);
			Configuration::updateValue(
				Config::MOLLIE_TRACKING_URLS,
				json_encode(@json_decode(Tools::getValue(Config::MOLLIE_TRACKING_URLS)))
			);
			$carriers = Carrier::getCarriers(
				Context::getContext()->language->id,
				false,
				false,
				false,
				null,
				Carrier::ALL_CARRIERS
			);
			foreach ($carriers as $carrier) {
				$urlSource = Tools::getValue(Config::MOLLIE_CARRIER_URL_SOURCE . $carrier['id_carrier']);
				$customUrl = Tools::getValue(Config::MOLLIE_CARRIER_CUSTOM_URL . $carrier['id_carrier']);
				$this->carrierInformationService->saveMolCarrierInfo($carrier['id_carrier'], $urlSource, $customUrl);
			}

			foreach (array_keys(Config::getStatuses()) as $name) {
				$name = Tools::strtoupper($name);
				if (false === Tools::getValue("MOLLIE_STATUS_{$name}")) {
					continue;
				}
				$new = (int) Tools::getValue("MOLLIE_STATUS_{$name}");
				Configuration::updateValue("MOLLIE_STATUS_{$name}", $new);
				Config::getStatuses()[Tools::strtolower($name)] = $new;

				if (PaymentStatus::STATUS_OPEN != $name) {
					Configuration::updateValue(
						"MOLLIE_MAIL_WHEN_{$name}",
						Tools::getValue("MOLLIE_MAIL_WHEN_{$name}") ? true : false
					);
				}
			}

			$resultMessage[] = $this->module->l('The configuration has been saved!');
		} else {
			$resultMessage = [];
			foreach ($errors as $error) {
				$resultMessage[] = $error;
			}
		}

		return $resultMessage;
	}

	/**
	 * Get all status values from the form.
	 *
	 * @param string $key The key that is used in the HelperForm
	 *
	 * @return array Array with statuses
	 *
	 * @since 3.3.0
	 */
	private function getStatusesValue($key)
	{
		$statesEnabled = [];
		$context = Context::getContext();
		foreach (OrderState::getOrderStates($context->language->id) as $state) {
			if (Tools::isSubmit($key . '_' . $state['id_order_state'])) {
				$statesEnabled[] = $state['id_order_state'];
			}
		}

		return $statesEnabled;
	}

	private function handleKlarnaInvoiceStatus()
	{
		$klarnaInvoiceStatus = Tools::getValue(Config::MOLLIE_KLARNA_INVOICE_ON);
		Configuration::updateValue(Config::MOLLIE_KLARNA_INVOICE_ON, $klarnaInvoiceStatus);
		if (Config::MOLLIE_STATUS_KLARNA_SHIPPED === $klarnaInvoiceStatus) {
			$this->updateKlarnaStatuses(true);

			return;
		}

		$this->updateKlarnaStatuses(false);
	}

	private function updateKlarnaStatuses($isShipped = true)
	{
		$klarnaInvoiceShippedId = Configuration::get(Config::MOLLIE_STATUS_KLARNA_SHIPPED);
		$klarnaInvoiceShipped = new OrderState((int) $klarnaInvoiceShippedId);
		$klarnaInvoiceShipped->invoice = $isShipped;
		$klarnaInvoiceShipped->update();

		$klarnaInvoiceAcceptedId = Configuration::get(Config::MOLLIE_STATUS_KLARNA_AUTHORIZED);
		$klarnaInvoiceAccepted = new OrderState((int) $klarnaInvoiceAcceptedId);

		$klarnaInvoiceAccepted->invoice = !$isShipped;
		$klarnaInvoiceAccepted->update();
	}
}
