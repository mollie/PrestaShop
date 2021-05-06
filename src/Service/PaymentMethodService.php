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

use Address;
use Cart;
use Configuration;
use Context;
use Country;
use Currency;
use Customer;
use Mollie;
use Mollie\Api\Resources\BaseCollection;
use Mollie\Api\Resources\MethodCollection;
use Mollie\Api\Types\PaymentMethod;
use Mollie\Config\Config;
use Mollie\DTO\Object\Amount;
use Mollie\DTO\OrderData;
use Mollie\DTO\PaymentData;
use Mollie\Provider\CreditCardLogoProvider;
use Mollie\Provider\PhoneNumberProviderInterface;
use Mollie\Repository\MethodCountryRepository;
use Mollie\Repository\PaymentMethodRepository;
use Mollie\Service\PaymentMethod\PaymentMethodRestrictionValidationInterface;
use Mollie\Service\PaymentMethod\PaymentMethodSortProviderInterface;
use Mollie\Utility\CustomLogoUtility;
use Mollie\Utility\EnvironmentUtility;
use Mollie\Utility\LocaleUtility;
use Mollie\Utility\PaymentFeeUtility;
use Mollie\Utility\TextFormatUtility;
use Mollie\Utility\TextGeneratorUtility;
use MolPaymentMethod;
use Order;
use PrestaShopDatabaseException;
use PrestaShopException;
use Shop;
use Tools;

class PaymentMethodService
{
	/**
	 * @var Mollie
	 */
	private $module;

	/**
	 * @var PaymentMethodRepository
	 */
	private $methodRepository;

	/**
	 * @var MethodCountryRepository
	 */
	private $methodCountryRepository;

	/**
	 * @var CartLinesService
	 */
	private $cartLinesService;

	/**
	 * @var PaymentsTranslationService
	 */
	private $paymentsTranslationService;

	/**
	 * @var CustomerService
	 */
	private $customerService;

	/**
	 * @var CreditCardLogoProvider
	 */
	private $creditCardLogoProvider;

	private $paymentMethodSortProvider;

	private $countryRepository;

	private $phoneNumberProvider;

	/**
	 * @var PaymentMethodRestrictionValidationInterface
	 */
	private $paymentMethodRestrictionValidation;

	/**
	 * @var \Country
	 */
	private $country;

	/**
	 * @var Shop
	 */
	private $shop;

	public function __construct(
		Mollie $module,
		PaymentMethodRepository $methodRepository,
		MethodCountryRepository $methodCountryRepository,
		CartLinesService $cartLinesService,
		PaymentsTranslationService $paymentsTranslationService,
		CustomerService $customerService,
		CreditCardLogoProvider $creditCardLogoProvider,
		PaymentMethodSortProviderInterface $paymentMethodSortProvider,
		PhoneNumberProviderInterface $phoneNumberProvider,
		PaymentMethodRestrictionValidationInterface $paymentMethodRestrictionValidation,
		Country $country,
		Shop $shop
	) {
		$this->module = $module;
		$this->methodRepository = $methodRepository;
		$this->methodCountryRepository = $methodCountryRepository;
		$this->cartLinesService = $cartLinesService;
		$this->paymentsTranslationService = $paymentsTranslationService;
		$this->customerService = $customerService;
		$this->creditCardLogoProvider = $creditCardLogoProvider;
		$this->paymentMethodSortProvider = $paymentMethodSortProvider;
		$this->phoneNumberProvider = $phoneNumberProvider;
		$this->paymentMethodRestrictionValidation = $paymentMethodRestrictionValidation;
		$this->country = $country;
		$this->shop = $shop;
	}

	public function savePaymentMethod($method)
	{
		$shopId = \Context::getContext()->shop->id;
		$environment = Tools::getValue(Mollie\Config\Config::MOLLIE_ENVIRONMENT);
		$paymentId = $this->methodRepository->getPaymentMethodIdByMethodId($method['id'], $environment, $shopId);
		$paymentMethod = new MolPaymentMethod();
		if ($paymentId) {
			$paymentMethod = new MolPaymentMethod((int) $paymentId);
		}
		$paymentMethod->id_method = $method['id'];
		$paymentMethod->method_name = $method['name'];
		$paymentMethod->enabled = Tools::getValue(Mollie\Config\Config::MOLLIE_METHOD_ENABLED . $method['id']);
		$paymentMethod->title = Tools::getValue(Mollie\Config\Config::MOLLIE_METHOD_TITLE . $method['id']);
		$paymentMethod->method = Tools::getValue(Mollie\Config\Config::MOLLIE_METHOD_API . $method['id']);
		$paymentMethod->description = Tools::getValue(Mollie\Config\Config::MOLLIE_METHOD_DESCRIPTION . $method['id']);
		$paymentMethod->is_countries_applicable = Tools::getValue(Mollie\Config\Config::MOLLIE_METHOD_APPLICABLE_COUNTRIES . $method['id']);
		$paymentMethod->minimal_order_value = Tools::getValue(Mollie\Config\Config::MOLLIE_METHOD_MINIMUM_ORDER_VALUE . $method['id']);
		$paymentMethod->max_order_value = Tools::getValue(Mollie\Config\Config::MOLLIE_METHOD_MAX_ORDER_VALUE . $method['id']);
		$paymentMethod->surcharge = Tools::getValue(Mollie\Config\Config::MOLLIE_METHOD_SURCHARGE_TYPE . $method['id']);
		$paymentMethod->surcharge_fixed_amount = Tools::getValue(Mollie\Config\Config::MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT . $method['id']);
		$paymentMethod->surcharge_percentage = Tools::getValue(Mollie\Config\Config::MOLLIE_METHOD_SURCHARGE_PERCENTAGE . $method['id']);
		$paymentMethod->surcharge_limit = Tools::getValue(Mollie\Config\Config::MOLLIE_METHOD_SURCHARGE_LIMIT . $method['id']);
		$paymentMethod->images_json = json_encode($method['image']);
		$paymentMethod->live_environment = $environment;
		$paymentMethod->id_shop = $shopId;

		$paymentMethod->save();

		return $paymentMethod;
	}

	/**
	 * Get payment methods to show on the checkout.
	 *
	 * @return array
	 *
	 * @throws PrestaShopDatabaseException
	 * @throws PrestaShopException
	 *
	 * @since 3.0.0
	 * @since 3.4.0 public
	 *
	 * @public ✓ This method is part of the public API
	 */
	public function getMethodsForCheckout()
	{
		$apiKey = EnvironmentUtility::getApiKey();
		if (!$apiKey || $this->module->api === null) {
			return [];
		}
		/* @phpstan-ignore-next-line */
		if (false === Configuration::get(Config::MOLLIE_STATUS_AWAITING)) {
			return [];
		}
		$apiEnvironment = Configuration::get(Config::MOLLIE_ENVIRONMENT);
		$methods = $this->methodRepository->getMethodsForCheckout($apiEnvironment, $this->shop->id) ?: [];

		$mollieMethods = $this->getSupportedMollieMethods();
		$methods = $this->removeNotSupportedMethods($methods, $mollieMethods);

		foreach ($methods as $index => $method) {
			/** @var MolPaymentMethod|null $paymentMethod */
			$paymentMethod = $this->methodRepository->findOneBy(['id_payment_method' => (int) $method['id_payment_method']]);

			if (!$paymentMethod || !$this->paymentMethodRestrictionValidation->isPaymentMethodValid($paymentMethod)) {
				unset($methods[$index]);
				continue;
			}

			$image = json_decode($method['images_json'], true);
			$methods[$index]['image'] = $image;
			if (CustomLogoUtility::isCustomLogoEnabled($method['id_method'])) {
				if ($this->creditCardLogoProvider->logoExists()) {
					$methods[$index]['image']['custom_logo'] = $this->creditCardLogoProvider->getLogoPathUri();
				}
			}
		}

		$methods = $this->paymentsTranslationService->getTranslatedPaymentMethods($methods);
		$methods = $this->paymentMethodSortProvider->getSortedInAscendingWayForCheckout($methods);

		return $methods;
	}

	/**
	 * Get payment data.
	 *
	 * @param float|string $amount
	 * @param string $currency
	 * @param string $method
	 * @param string|null $issuer
	 * @param int|Cart $cartId
	 * @param string $secureKey
	 * @param MolPaymentMethod $molPaymentMethod
	 * @param bool $qrCode
	 * @param string $orderReference
	 * @param string $cardToken
	 *
	 * @return PaymentData|OrderData
	 *
	 * @since 3.3.0 Order reference
	 */
	public function getPaymentData(
		$amount,
		$currency,
		$method,
		$issuer,
		$cartId,
		$secureKey,
		MolPaymentMethod $molPaymentMethod,
		$qrCode = false,
		$orderReference = '',
		$cardToken = ''
	) {
		$totalAmount = TextFormatUtility::formatNumber($amount, 2);
		if (!$orderReference) {
			$this->module->currentOrderReference = $orderReference = Order::generateReference();
		}
		$description = TextGeneratorUtility::generateDescriptionFromCart($molPaymentMethod->description, $cartId, $orderReference);
		$context = Context::getContext();
		$cart = new Cart($cartId);
		$customer = new Customer($cart->id_customer);

		$paymentFee = PaymentFeeUtility::getPaymentFee($molPaymentMethod, $totalAmount);
		$totalAmount += $paymentFee;

		$currency = (string) ($currency ? Tools::strtoupper($currency) : 'EUR');
		$value = (float) TextFormatUtility::formatNumber($totalAmount, 2);
		$amountObj = new Amount($currency, $value);

		$key = Mollie\Utility\SecureKeyUtility::generateReturnKey(
			$secureKey,
			$customer->id,
			$cartId,
			$this->module->name
		);
		$redirectUrl = ($qrCode
			? $context->link->getModuleLink(
				'mollie',
				'qrcode',
				['cart_id' => $cartId, 'done' => 1, 'rand' => time()],
				true
			)
			: $context->link->getModuleLink(
				'mollie',
				'return',
				[
					'cart_id' => $cartId,
					'utm_nooverride' => 1,
					'rand' => time(),
					'key' => $key,
					'customerId' => $customer->id,
				],
				true
			)
		);

		$webhookUrl = null;
		if (!EnvironmentUtility::isLocalEnvironment()) {
			$webhookUrl = $context->link->getModuleLink(
				'mollie',
				'webhook',
				[],
				true
			);
		}

		$metaData = [
			'cart_id' => $cartId,
			'order_reference' => $orderReference,
			'secure_key' => $key,
		];

		if (Mollie\Config\Config::MOLLIE_ORDERS_API !== $molPaymentMethod->method) {
			$paymentData = new PaymentData($amountObj, $description, $redirectUrl, $webhookUrl);

			$paymentData->setMetadata($metaData);
			$paymentData->setLocale($this->getLocale($molPaymentMethod->method));
			$paymentData->setMethod($molPaymentMethod->id_method);

			$description = str_ireplace(
				['%'],
				[$cartId],
				$description
			);
			$paymentData->setDescription($description);
			$paymentData->setIssuer($issuer);

			if (isset($cart->id_address_invoice)) {
				$billing = new Address((int) $cart->id_address_invoice);
				$paymentData->setBillingAddress($billing);
			}
			if (isset($cart->id_address_delivery)) {
				$shipping = new Address((int) $cart->id_address_delivery);
				$paymentData->setShippingAddress($shipping);
			}

			if ($cardToken) {
				$paymentData->setCardToken($cardToken);
			}

			if (PaymentMethod::BANKTRANSFER === $method) {
				$paymentData->setLocale(LocaleUtility::getWebShopLocale());
			}

			$isCreditCardPayment = PaymentMethod::CREDITCARD === $molPaymentMethod->id_method;
			if ($isCreditCardPayment && $this->isCustomerSaveEnabled()) {
				$apiCustomer = $this->customerService->processCustomerCreation($cart, $molPaymentMethod->id_method);
				$paymentData->setCustomerId($apiCustomer->id);
			}

			return $paymentData;
		}

		if (Mollie\Config\Config::MOLLIE_ORDERS_API === $molPaymentMethod->method) {
			$orderData = new OrderData($amountObj, $redirectUrl, $webhookUrl);

			if (isset($cart->id_address_invoice)) {
				$billing = new Address((int) $cart->id_address_invoice);

				$orderData->setBillingAddress($billing);
				$orderData->setBillingPhoneNumber($this->phoneNumberProvider->getFromAddress($billing));
			}
			if (isset($cart->id_address_delivery)) {
				$shipping = new Address((int) $cart->id_address_delivery);
				$orderData->setShippingAddress($shipping);
				$orderData->setDeliveryPhoneNumber($this->phoneNumberProvider->getFromAddress($shipping));
			}
			$orderData->setOrderNumber($orderReference);
			$orderData->setLocale($this->getLocale($molPaymentMethod->method));
			$orderData->setEmail($customer->email);
			$orderData->setMethod($molPaymentMethod->id_method);
			$orderData->setMetadata($metaData);

			$currency = new Currency($cart->id_currency);
			$selectedVoucherCategory = Configuration::get(Config::MOLLIE_VOUCHER_CATEGORY);
			$orderData->setLines(
				$this->cartLinesService->getCartLines(
					$amount,
					$paymentFee,
					$currency->iso_code,
					$cart->getSummaryDetails(),
					$cart->getTotalShippingCost(null, true),
					$cart->getProducts(),
					(bool) Configuration::get('PS_GIFT_WRAPPING'),
					$selectedVoucherCategory
				));
			$payment = [];
			if ($cardToken) {
				$payment['cardToken'] = $cardToken;
			}
			if (!EnvironmentUtility::isLocalEnvironment()) {
				$payment['webhookUrl'] = $context->link->getModuleLink(
					'mollie',
					'webhook',
					[],
					true
				);
			}
			if ($issuer) {
				$payment['issuer'] = $issuer;
			}

			$isCreditCardPayment = PaymentMethod::CREDITCARD === $molPaymentMethod->id_method;
			if ($isCreditCardPayment && $this->isCustomerSaveEnabled()) {
				$apiCustomer = $this->customerService->processCustomerCreation($cart, $molPaymentMethod->id_method);
				$payment['customerId'] = $apiCustomer->id;
			}

			$orderData->setPayment($payment);

			return $orderData;
		}
	}

	private function getLocale($method)
	{
		// Send webshop locale
		if ((Mollie\Config\Config::MOLLIE_PAYMENTS_API === $method
				&& Mollie\Config\Config::PAYMENTSCREEN_LOCALE_SEND_WEBSITE_LOCALE === Configuration::get(Mollie\Config\Config::MOLLIE_PAYMENTSCREEN_LOCALE))
			|| Mollie\Config\Config::MOLLIE_ORDERS_API === $method
		) {
			$locale = LocaleUtility::getWebShopLocale();
			if (preg_match(
				'/^[a-z]{2}(?:[\-_][A-Z]{2})?$/iu',
				$locale
			)) {
				return $locale;
			}
		}
	}

	private function isCustomerSaveEnabled()
	{
		$isComponentsEnabled = Configuration::get(Config::MOLLIE_IFRAME);
		$isSingleClickPaymentEnabled = Configuration::get(Config::MOLLIE_SINGLE_CLICK_PAYMENT);

		return !$isComponentsEnabled && $isSingleClickPaymentEnabled;
	}

	private function removeNotSupportedMethods($methods, $mollieMethods)
	{
		foreach ($methods as $key => $method) {
			$valid = false;
			foreach ($mollieMethods as $mollieMethod) {
				if ($method['id_method'] === $mollieMethod->id) {
					$valid = true;
					continue;
				}
			}
			if (!$valid) {
				unset($methods[$key]);
			}
		}

		return $methods;
	}

	private function getSupportedMollieMethods()
	{
		$addressId = Context::getContext()->cart->id_address_invoice;
		$address = new Address($addressId);
		$country = new Country($address->id_country);

		/** @var BaseCollection|MethodCollection $methods */
		$methods = $this->module->api->methods->allActive(
			[
				'resource' => 'orders',
				'include' => 'issuers',
				'includeWallets' => 'applepay',
				'billingCountry' => $country->iso_code,
			]
		);

		return $methods->getArrayCopy();
	}
}
