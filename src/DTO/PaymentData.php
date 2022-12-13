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

namespace Mollie\DTO;

use Address;
use Country;
use JsonSerializable;
use Mollie\DTO\Object\Amount;

class PaymentData implements JsonSerializable
{
    /**
     * @var Amount
     */
    private $amount;

    /**
     * @var string
     */
    private $description;

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
    private $issuer;

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
     * @var ?string
     */
    private $applePayToken;
    /**
     * @var string
     */
    private $shippingStreetAndNumber;

    /**
     * @var string
     */
    private $billingStreetAndNumber;

    /**
     * @var string
     */
    private $sequenceType;

    public function __construct(
        Amount $amount,
        $description,
        $redirectUrl,
        $webhookUrl
    ) {
        $this->amount = $amount;
        $this->description = $description;
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
    public function getDescription()
    {
        return $this->description ?: ' ';
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
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
    public function getIssuer()
    {
        return $this->issuer;
    }

    /**
     * @param string $issuer
     */
    public function setIssuer($issuer)
    {
        $this->issuer = $issuer;
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
     * @param Address $shippingAddress
     */
    public function setShippingAddress($shippingAddress)
    {
        $this->shippingAddress = $shippingAddress;
    }

    /**
     * @return string|null
     */
    public function getApplePayToken()
    {
        return $this->applePayToken;
    }

    /**
     * @param string|null $applePayToken
     *
     * @return $this
     */
    public function setApplePayToken($applePayToken): PaymentData
    {
        $this->applePayToken = $applePayToken;

        return $this;
    }

    public function getShippingStreetAndNumber(): string
    {
        return $this->shippingStreetAndNumber;
    }

    public function setShippingStreetAndNumber(string $shippingStreetAndNumber): void
    {
        $this->shippingStreetAndNumber = $shippingStreetAndNumber;
    }

    public function getSequenceType(): string
    {
        return $this->sequenceType;
    }

    public function setSequenceType(string $sequenceType): void
    {
        $this->sequenceType = $sequenceType;
    }


    public function jsonSerialize()
    {
        $result =  [
            'amount' => [
                'currency' => $this->getAmount()->getCurrency(),
                'value' => (string) $this->getAmount()->getValue(),
            ],
            'billingAddress' => [
                'streetAndNumber' => $this->cleanUpInput($this->getBillingAddress()->address1),
                'streetAdditional' => $this->cleanUpInput($this->getBillingAddress()->address2, null),
                'city' => $this->cleanUpInput($this->getBillingAddress()->city),
                'postalCode' => $this->cleanUpInput($this->getBillingAddress()->postcode),
                'country' => $this->cleanUpInput(Country::getIsoById($this->getBillingAddress()->id_country)),
            ],
            'shippingAddress' => [
                'streetAndNumber' => $this->cleanUpInput($this->getShippingAddress()->address1),
                'streetAdditional' => $this->cleanUpInput($this->getShippingAddress()->address2, null),
                'city' => $this->cleanUpInput($this->getShippingAddress()->city),
                'postalCode' => $this->cleanUpInput($this->getShippingAddress()->postcode),
                'country' => $this->cleanUpInput(Country::getIsoById($this->getShippingAddress()->id_country)),
            ],
            'description' => $this->getDescription(),
            'redirectUrl' => $this->getRedirectUrl(),
            'webhookUrl' => $this->getWebhookUrl(),
            'method' => $this->getMethod(),
            'metadata' => $this->getMetadata(),
            'locale' => $this->getLocale(),
            'issuer' => $this->getIssuer(),
            'cardToken' => $this->getCardToken(),
            'customerId' => $this->getCustomerId(),
            'applePayPaymentToken' => $this->getApplePayToken(),
        ];

        if ($this->sequenceType) {
            $result['sequenceType'] = $this->sequenceType;
        }

        return $result;
    }

    private function cleanUpInput($input, $defaultValue = 'N/A')
    {
        if (empty($input)) {
            return $defaultValue;
        }

        if (ctype_space($input)) {
            return $defaultValue;
        }
        $input = ltrim($input);

        return substr($input, 0, 100);
    }
}
