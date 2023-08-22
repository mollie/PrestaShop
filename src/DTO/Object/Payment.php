<?php

namespace Mollie\DTO\Object;

class Payment implements \JsonSerializable
{
    /** @var ?string */
    private $cardToken;
    /** @var string */
    private $webhookUrl;
    /** @var ?string */
    private $issuer;
    /** @var ?string */
    private $customerId;
    /** @var ?string */
    private $applePayPaymentToken;
    /** @var ?Company */
    private $company;

    /**
     * @return ?string
     */
    public function getCardToken(): ?string
    {
        return $this->cardToken;
    }

    /**
     * @param string $cardToken
     *
     * @maps cardToken
     */
    public function setCardToken(string $cardToken): void
    {
        $this->cardToken = $cardToken;
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
     *
     * @maps webhookUrl
     */
    public function setWebhookUrl(string $webhookUrl): void
    {
        $this->webhookUrl = $webhookUrl;
    }

    /**
     * @return ?string
     */
    public function getIssuer(): ?string
    {
        return $this->issuer;
    }

    /**
     * @param string $issuer
     *
     * @maps issuer
     */
    public function setIssuer(string $issuer): void
    {
        $this->issuer = $issuer;
    }

    /**
     * @return ?string
     */
    public function getCustomerId(): ?string
    {
        return $this->customerId;
    }

    /**
     * @param string $customerId
     *
     * @maps customerId
     */
    public function setCustomerId(string $customerId): void
    {
        $this->customerId = $customerId;
    }

    /**
     * @return ?string
     */
    public function getApplePayPaymentToken(): ?string
    {
        return $this->applePayPaymentToken;
    }

    /**
     * @param string $applePayPaymentToken
     *
     * @maps applePayPaymentToken
     */
    public function setApplePayPaymentToken(string $applePayPaymentToken): void
    {
        $this->applePayPaymentToken = $applePayPaymentToken;
    }

    /**
     * @return ?Company
     */
    public function getCompany(): ?Company
    {
        return $this->company;
    }

    /**
     * @param \Mollie\DTO\Object\Company $company
     *
     * @maps company
     */
    public function setCompany(Company $company): void
    {
        $this->company = $company;
    }

    public function jsonSerialize()
    {
        $result = [];
        $result['cardToken'] = $this->getCardToken();
        $result['webhookUrl'] = $this->getWebhookUrl();
        $result['issuer'] = $this->getIssuer();
        $result['customerId'] = $this->getCustomerId();
        $result['applePayPaymentToken'] = $this->getApplePayPaymentToken();
        $result['company'] = $this->getCompany() ? $this->getCompany()->jsonSerialize() : null;

        return array_filter($result, static function ($val) {
            return $val !== null && $val !== '';
        });
    }
}
