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

namespace Mollie\Provider;

use Mollie\Adapter\Context;
use Mollie\Repository\PaymentMethodLangRepositoryInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PaymentMethodTranslationProvider
{
    /** @var PaymentMethodLangRepositoryInterface */
    private $paymentMethodLangRepository;

    /** @var Context */
    private $context;

    public function __construct(PaymentMethodLangRepositoryInterface $paymentMethodLangRepository, Context $context)
    {
        $this->paymentMethodLangRepository = $paymentMethodLangRepository;
        $this->context = $context;
    }

    public function trans(string $idMethod): ?string
    {
        return $this->paymentMethodLangRepository->getTextByLanguageAndMethod($this->context->getLanguageId(), $idMethod, $this->context->getShopId());
    }


    /**
     * Gets all translations for a payment method title
     *
     * @param string $idMethod
     * @return array [id_lang => text] for instance: [74 => 'Apelo Pay', 68 => 'Apella Pia']
     */
    public function getTransList(string $idMethod): array
    {
        $result = $this->paymentMethodLangRepository->getAllTranslationsByMethod($idMethod, $this->context->getShopId());

        $mappedArray = [];
        foreach ($result as $value) {
            $mappedArray[$value['id_lang']] = $value['text'];
        }

        return $mappedArray;
    }
}
