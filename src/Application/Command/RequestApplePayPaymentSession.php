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

namespace Mollie\Application\Command;

final class RequestApplePayPaymentSession
{
    /**
     * @var string
     */
    private $validationUrl;
    /**
     * @var int
     */
    private $currencyId;
    /**
     * @var int
     */
    private $langId;

    public function __construct(string $validationUrl, int $currencyId, int $langId)
    {
        $this->validationUrl = $validationUrl;
        $this->currencyId = $currencyId;
        $this->langId = $langId;
    }

    public function getValidationUrl(): string
    {
        return $this->validationUrl;
    }

    public function getCurrencyId(): int
    {
        return $this->currencyId;
    }

    public function getLangId(): int
    {
        return $this->langId;
    }
}
