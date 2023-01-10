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

namespace Mollie\Service;

class PaymentsTranslationService
{
    /**
     * @var LanguageService
     */
    private $languageService;

    public function __construct(
        LanguageService $languageService
    ) {
        $this->languageService = $languageService;
    }

    public function getTranslatedPaymentMethods($paymentMethods)
    {
        foreach ($paymentMethods as $method) {
            $method['method_name'] = $this->languageService->lang($method['method_name']);
        }

        return $paymentMethods;
    }
}
