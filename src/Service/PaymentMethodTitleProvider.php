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

declare(strict_types=1);

namespace Mollie\Service;

use Mollie\Config\Config;
use Mollie\Repository\PaymentMethodLangRepositoryInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Resolves the payment method label written to Order.payment and OrderPayment.payment_method.
 *
 * Order of resolution:
 *   1. Merchant-configured "Payment Title" (mol_payment_method_translations.text)
 *   2. Built-in display label from Config::$methods
 *   3. Raw method id
 *
 * The result is sanitized of characters known to break ERP/CSV integrations
 * (",", "|", ".", ";", tab) and clamped to the schema width of ps_orders.payment.
 */
class PaymentMethodTitleProvider
{
    private const FORBIDDEN_CHARS = [',', '|', '.', ';', "\t"];
    private const MAX_LENGTH = 255;

    /** @var PaymentMethodLangRepositoryInterface */
    private $paymentMethodLangRepository;

    public function __construct(PaymentMethodLangRepositoryInterface $paymentMethodLangRepository)
    {
        $this->paymentMethodLangRepository = $paymentMethodLangRepository;
    }

    public function getTitle(string $methodId, int $idLang, int $idShop): string
    {
        $methodId = trim($methodId);
        if ($methodId === '') {
            return '';
        }

        $configured = $this->fetchConfiguredTitle($methodId, $idLang, $idShop);
        $title = $configured !== '' ? $configured : $this->getDefaultLabel($methodId);

        $sanitized = $this->sanitize($title);

        return $sanitized !== '' ? $sanitized : $methodId;
    }

    public function sanitize(string $value): string
    {
        $value = str_replace(self::FORBIDDEN_CHARS, ' ', $value);
        $value = (string) preg_replace('/\s+/u', ' ', $value);
        $value = trim($value);

        if (mb_strlen($value) > self::MAX_LENGTH) {
            $value = mb_substr($value, 0, self::MAX_LENGTH);
        }

        return $value;
    }

    private function fetchConfiguredTitle(string $methodId, int $idLang, int $idShop): string
    {
        try {
            /** @var \MolPaymentMethodTranslations|null $translation */
            $translation = $this->paymentMethodLangRepository->findOneBy([
                'id_method' => $methodId,
                'id_lang' => $idLang,
                'id_shop' => $idShop,
            ]);
        } catch (\Throwable $e) {
            return '';
        }

        if (!$translation || !isset($translation->text)) {
            return '';
        }

        return trim((string) $translation->text);
    }

    private function getDefaultLabel(string $methodId): string
    {
        return Config::$methods[$methodId] ?? $methodId;
    }
}
