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

if (!defined('_PS_VERSION_')) {
    exit;
}

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

    /**
     * @var bool
     */
    private $subscriptionOrder = false;

    /**
     * @var string
     */
    private $email;

    /**
     * @var PaymentLine[]
     */
    private $lines = [];

    /**
     * @var string
     */
    private $billingPhoneNumber;

    /**
     * @var ?string
     */
    private $title;

    public function __construct(
        Amount $amount,
        string $description,
        string $redirectUrl,
        string $webhookUrl
    ) {
        $this->amount = $amount;
        $this->description = $description;
        $this->redirectUrl = $redirectUrl;
        $this->webhookUrl = $webhookUrl;
    }

    /**
     * @return Amount
     */
    public function getAmount(): Amount
    {
        return $this->amount;
    }

    /**
     * @param Amount $amount
     */
    public function setAmount(Amount $amount): void
    {
        $this->amount = $amount;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getRedirectUrl(): string
    {
        return $this->redirectUrl;
    }

    /**
     * @param string $redirectUrl
     */
    public function setRedirectUrl(string $redirectUrl): void
    {
        $this->redirectUrl = $redirectUrl;
    }

    /**
     * @return string
     */
    public function getWebhookUrl(): string
    {
        return $this->webhookUrl;
    }

    /**
     * @param string $webhookUrl
     */
    public function setWebhookUrl(string $webhookUrl): void
    {
        $this->webhookUrl = $webhookUrl;
    }

    /**
     * @return string
     */
    public function getMethod(): ?string
    {
        return $this->method;
    }

    /**
     * @param string $method
     */
    public function setMethod(string $method): void
    {
        $this->method = $method;
    }

    /**
     * @return array
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * @param array $metadata
     */
    public function setMetadata(array $metadata): void
    {
        $this->metadata = $metadata;
    }

    /**
     * @return string
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     */
    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    /**
     * @return ?string
     */
    public function getCardToken(): ?string
    {
        return $this->cardToken;
    }

    /**
     * @param ?string $cardToken
     */
    public function setCardToken(?string $cardToken): void
    {
        $this->cardToken = $cardToken;
    }

    /**
     * @return ?string
     */
    public function getCustomerId(): ?string
    {
        return $this->customerId;
    }

    /**
     * @param ?string $customerId
     */
    public function setCustomerId(?string $customerId): void
    {
        $this->customerId = $customerId;
    }

    /**
     * @return Address
     */
    public function getBillingAddress(): Address
    {
        return $this->billingAddress;
    }

    /**
     * @param Address $billingAddress
     */
    public function setBillingAddress(Address $billingAddress): void
    {
        $this->billingAddress = $billingAddress;
    }

    /**
     * @return Address
     */
    public function getShippingAddress(): Address
    {
        return $this->shippingAddress;
    }

    /**
     * @param Address $shippingAddress
     */
    public function setShippingAddress(Address $shippingAddress): void
    {
        $this->shippingAddress = $shippingAddress;
    }

    /**
     * @return ?string
     */
    public function getApplePayToken(): ?string
    {
        return $this->applePayToken;
    }

    /**
     * @param ?string $applePayToken
     */
    public function setApplePayToken(?string $applePayToken): void
    {
        $this->applePayToken = $applePayToken;
    }

    public function getShippingStreetAndNumber(): ?string
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

    public function isSubscriptionOrder(): bool
    {
        return $this->subscriptionOrder;
    }

    public function setSubscriptionOrder(bool $subscriptionOrder): void
    {
        $this->subscriptionOrder = $subscriptionOrder;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return PaymentLine[]
     */
    public function getLines(): array
    {
        return $this->lines;
    }

    /**
     * @param PaymentLine[] $lines
     */
    public function setLines(array $lines): void
    {
        $this->lines = $lines;
    }

    /**
     * @return ?string
     */
    public function getBillingPhoneNumber(): ?string
    {
        return $this->billingPhoneNumber;
    }

    /**
     * @param ?string $billingPhoneNumber
     */
    public function setBillingPhoneNumber(?string $billingPhoneNumber): void
    {
        $this->billingPhoneNumber = $billingPhoneNumber;
    }

    /**
     * @return ?string
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param ?string $title
     */
    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function jsonSerialize(): array
    {
        $result = [
            'amount' => [
                'currency' => $this->getAmount()->getCurrency(),
                'value' => (string) $this->getAmount()->getValue(),
            ],
            'billingAddress' => [
                'organizationName' => $this->cleanUpInput($this->getBillingAddress()->company),
                'givenName' => $this->cleanUpInput($this->getBillingAddress()->firstname),
                'familyName' => $this->cleanUpInput($this->getBillingAddress()->lastname),
                'email' => $this->cleanUpInput($this->getEmail()),
                'streetAndNumber' => $this->cleanUpInput($this->getBillingAddress()->address1),
                'streetAdditional' => $this->cleanUpInput($this->getBillingAddress()->address2, null),
                'city' => $this->cleanUpInput($this->getBillingAddress()->city),
                'postalCode' => $this->cleanUpInput($this->getBillingAddress()->postcode),
                'country' => $this->cleanUpInput(Country::getIsoById($this->getBillingAddress()->id_country)),
                'title' => $this->cleanUpInput($this->getTitle()),
                'phone' => $this->getBillingPhoneNumber(),
            ],
            'shippingAddress' => [
                'givenName' => $this->cleanUpInput($this->getBillingAddress()->firstname),
                'familyName' => $this->cleanUpInput($this->getBillingAddress()->lastname),
                'email' => $this->cleanUpInput($this->getEmail()),
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
            'cardToken' => $this->getCardToken(),
            'customerId' => $this->getCustomerId(),
            'applePayPaymentToken' => $this->getApplePayToken(),
        ];

        if ($this->sequenceType) {
            $result['sequenceType'] = $this->sequenceType;
        }

        if (!empty($this->getLines())) {
            $result['lines'] = $this->getLines();
        }

        return $result;
    }

    private function cleanUpInput($input, $defaultValue = 'N/A'): ?string
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
