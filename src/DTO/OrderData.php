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

namespace Mollie\DTO;

use Address;
use Country;
use JsonSerializable;
use Mollie\DTO\Object\Amount;

class OrderData implements JsonSerializable
{
	/**
	 * @var Amount
	 */
	private $amount;

	/**
	 * @var string
	 */
	private $redirectUrl;

	/**
	 * @var string
	 */
	private $webhookUrl;

	/**
	 * @var string
	 */
	private $method;

	/**
	 * @var array
	 */
	private $metadata;

	/**
	 * @var string
	 */
	private $locale;

	/**
	 * @var string
	 */
	private $cardToken;

	/**
	 * @var string
	 */
	private $customerId;

	/**
	 * @var Address
	 */
	private $billingAddress;

	/**
	 * @var Address
	 */
	private $shippingAddress;

	/**
	 * @var string
	 */
	private $orderNumber;

	/**
	 * @var string
	 */
	private $email;

	/**
	 * @var Line[]
	 */
	private $lines;

	/**
	 * @var array
	 */
	private $payment;

	/**
	 * @var string
	 */
	private $billingPhoneNumber;

	/**
	 * @var string
	 */
	private $deliveryPhoneNumber;

	public function __construct(
		Amount $amount,
		$redirectUrl,
		$webhookUrl
	) {
		$this->amount = $amount;
		$this->redirectUrl = $redirectUrl;
		$this->webhookUrl = $webhookUrl;
	}

	/**
	 * @return Amount
	 */
	public function getAmount()
	{
		return $this->amount;
	}

	/**
	 * @param Amount $amount
	 */
	public function setAmount($amount)
	{
		$this->amount = $amount;
	}

	/**
	 * @return mixed
	 */
	public function getRedirectUrl()
	{
		return $this->redirectUrl;
	}

	/**
	 * @param mixed $redirectUrl
	 */
	public function setRedirectUrl($redirectUrl)
	{
		$this->redirectUrl = $redirectUrl;
	}

	/**
	 * @return mixed
	 */
	public function getWebhookUrl()
	{
		return $this->webhookUrl;
	}

	/**
	 * @param mixed $webhookUrl
	 */
	public function setWebhookUrl($webhookUrl)
	{
		$this->webhookUrl = $webhookUrl;
	}

	/**
	 * @return string
	 */
	public function getMethod()
	{
		return $this->method;
	}

	/**
	 * @param string $method
	 */
	public function setMethod($method)
	{
		$this->method = $method;
	}

	/**
	 * @return array
	 */
	public function getMetadata()
	{
		return $this->metadata;
	}

	/**
	 * @param array $metadata
	 */
	public function setMetadata($metadata)
	{
		$this->metadata = $metadata;
	}

	/**
	 * @return string
	 */
	public function getLocale()
	{
		return $this->locale;
	}

	/**
	 * @param string $locale
	 */
	public function setLocale($locale)
	{
		$this->locale = $locale;
	}

	/**
	 * @return string
	 */
	public function getCardToken()
	{
		return $this->cardToken;
	}

	/**
	 * @param string $cardToken
	 */
	public function setCardToken($cardToken)
	{
		$this->cardToken = $cardToken;
	}

	/**
	 * @return string
	 */
	public function getCustomerId()
	{
		return $this->customerId;
	}

	/**
	 * @param string $customerId
	 */
	public function setCustomerId($customerId)
	{
		$this->customerId = $customerId;
	}

	/**
	 * @return Address
	 */
	public function getBillingAddress()
	{
		return $this->billingAddress;
	}

	/**
	 * @param Address $billingAddress
	 */
	public function setBillingAddress($billingAddress)
	{
		$this->billingAddress = $billingAddress;
	}

	/**
	 * @return Address
	 */
	public function getShippingAddress()
	{
		return $this->shippingAddress;
	}

	/**
	 * @return mixed
	 */
	public function getBillingPhoneNumber()
	{
		return $this->billingPhoneNumber;
	}

	/**
	 * @param mixed $billingPhoneNumber
	 *
	 * @return self
	 */
	public function setBillingPhoneNumber($billingPhoneNumber)
	{
		$this->billingPhoneNumber = $billingPhoneNumber;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getDeliveryPhoneNumber()
	{
		return $this->deliveryPhoneNumber;
	}

	/**
	 * @param mixed $deliveryPhoneNumber
	 *
	 * @return self
	 */
	public function setDeliveryPhoneNumber($deliveryPhoneNumber)
	{
		$this->deliveryPhoneNumber = $deliveryPhoneNumber;

		return $this;
	}

	/**
	 * @param Address $shippingAddress
	 */
	public function setShippingAddress($shippingAddress)
	{
		$this->shippingAddress = $shippingAddress;
	}

	/**
	 * @return string
	 */
	public function getOrderNumber()
	{
		return $this->orderNumber;
	}

	/**
	 * @param string $orderNumber
	 */
	public function setOrderNumber($orderNumber)
	{
		$this->orderNumber = $orderNumber;
	}

	/**
	 * @return string
	 */
	public function getEmail()
	{
		return $this->email;
	}

	/**
	 * @param string $email
	 */
	public function setEmail($email)
	{
		$this->email = $email;
	}

	/**
	 * @return Line[]
	 */
	public function getLines()
	{
		return $this->lines;
	}

	/**
	 * @param Line[] $lines
	 */
	public function setLines($lines)
	{
		$this->lines = $lines;
	}

	/**
	 * @return array
	 */
	public function getPayment()
	{
		return $this->payment;
	}

	/**
	 * @param array $payment
	 */
	public function setPayment($payment)
	{
		$this->payment = $payment;
	}

	public function jsonSerialize()
	{
		$lines = [];
		foreach ($this->getLines() as $line) {
			$lines[] = $line->jsonSerialize();
		}

		$result = [
			'amount' => [
				'currency' => $this->getAmount()->getCurrency(),
				'value' => (string) $this->getAmount()->getValue(),
			],
			'billingAddress' => [
				'organizationName' => ltrim($this->getBillingAddress()->company),
				'streetAndNumber' => $this->getBillingAddress()->address1,
				'city' => $this->getBillingAddress()->city,
				'postalCode' => $this->getBillingAddress()->postcode,
				'country' => (string) Country::getIsoById($this->getBillingAddress()->id_country),
				'givenName' => $this->getBillingAddress()->firstname,
				'familyName' => $this->getBillingAddress()->lastname,
				'email' => $this->getEmail(),
			],
			'shippingAddress' => [
				'organizationName' => ltrim($this->getShippingAddress()->company),
				'streetAndNumber' => $this->getShippingAddress()->address1,
				'city' => $this->getShippingAddress()->city,
				'postalCode' => $this->getShippingAddress()->postcode,
				'country' => (string) Country::getIsoById($this->getShippingAddress()->id_country),
				'givenName' => $this->getShippingAddress()->firstname,
				'familyName' => $this->getShippingAddress()->lastname,
				'email' => $this->getEmail(),
			],
			'redirectUrl' => $this->getRedirectUrl(),
			'webhookUrl' => $this->getWebhookUrl(),
			'method' => $this->getMethod(),
			'metadata' => $this->getMetadata(),
			'locale' => $this->getLocale(),
			'orderNumber' => $this->getOrderNumber(),
			'lines' => $lines,
			'payment' => $this->getPayment(),
		];

		if ($this->billingPhoneNumber) {
			$result['billingAddress']['phone'] = $this->billingPhoneNumber;
		}

		if ($this->deliveryPhoneNumber) {
			$result['shippingAddress']['phone'] = $this->deliveryPhoneNumber;
		}

		return $result;
	}
}
