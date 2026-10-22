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

namespace Mollie\DTO\Express;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Body of POST /v2/sessions. The only class that knows Mollie's request field names, so a
 * rename on the private-beta API is a change here and nowhere else.
 */
class ExpressSessionRequest implements \JsonSerializable
{
    /** @var string */
    private $currency;
    /** @var string */
    private $amount;
    /** @var string */
    private $description;
    /** @var array */
    private $lines;
    /** @var string */
    private $redirectUrl;
    /** @var string */
    private $webhookUrl;
    /** @var array */
    private $metadata;
    /** @var string[] */
    private $requiredCustomerDetails;

    public function __construct(
        string $currency,
        string $amount,
        string $description,
        array $lines,
        string $redirectUrl,
        string $webhookUrl,
        array $metadata,
        array $requiredCustomerDetails = []
    ) {
        $this->currency = $currency;
        $this->amount = $amount;
        $this->description = $description;
        $this->lines = $lines;
        $this->redirectUrl = $redirectUrl;
        $this->webhookUrl = $webhookUrl;
        $this->metadata = $metadata;
        $this->requiredCustomerDetails = $requiredCustomerDetails;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getAmount(): string
    {
        return $this->amount;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getLines(): array
    {
        return $this->lines;
    }

    public function getRedirectUrl(): string
    {
        return $this->redirectUrl;
    }

    public function getWebhookUrl(): string
    {
        return $this->webhookUrl;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function getRequiredCustomerDetails(): array
    {
        return $this->requiredCustomerDetails;
    }

    public function jsonSerialize(): array
    {
        return [
            'amount' => [
                'currency' => $this->currency,
                'value' => $this->amount,
            ],
            'description' => $this->description,
            'lines' => $this->lines,
            'redirectUrl' => $this->redirectUrl,
            'metadata' => $this->metadata,
            'payment' => [
                'webhookUrl' => $this->webhookUrl,
            ],
            'requiredCustomerDetails' => $this->requiredCustomerDetails,
        ];
    }
}
