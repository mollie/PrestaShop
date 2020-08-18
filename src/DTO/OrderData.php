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

NameSpace Mollie\DTO;

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
     * @var
     */
    private $redirectUrl;

    /**
     * @var
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
     * @return null
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param null $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * @return mixed
     */
    public function getCardToken()
    {
        return $this->cardToken;
    }

    /**
     * @param mixed $cardToken
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
        return [
            'amount' => [
                'currency' => $this->getAmount()->getCurrency(),
                'value' => (string)$this->getAmount()->getValue(),
            ],
            'billingAddress' => [
                "organizationName" => $this->getBillingAddress()->company,
                "streetAndNumber" => $this->getBillingAddress()->address1,
                "city" => $this->getBillingAddress()->city,
                "postalCode" => $this->getBillingAddress()->postcode,
                "country" => (string)Country::getIsoById($this->getBillingAddress()->id_country),
                "givenName" => $this->getBillingAddress()->firstname,
                "familyName" => $this->getBillingAddress()->lastname,
                "email" => $this->getEmail(),
                "phone" => $this->getBillingAddress()->phone,
            ],
            'shippingAddress' => [
                "organizationName" => $this->getShippingAddress()->company,
                "streetAndNumber" => $this->getShippingAddress()->address1,
                "city" => $this->getShippingAddress()->city,
                "postalCode" => $this->getShippingAddress()->postcode,
                "country" => (string)Country::getIsoById($this->getShippingAddress()->id_country),
                "givenName" => $this->getShippingAddress()->firstname,
                "familyName" => $this->getShippingAddress()->lastname,
                "email" => $this->getEmail(),
                "phone" => $this->getShippingAddress()->phone,
            ],
            'redirectUrl' => $this->getRedirectUrl(),
            'webhookUrl' => $this->getWebhookUrl(),
            'method' => $this->getMethod(),
            'metadata' => $this->getMetadata(),
            'locale' => $this->getLocale(),
            'orderNumber' => $this->getOrderNumber(),
            'lines' => $lines,
            'payment' => $this->getPayment()
        ];
    }
}