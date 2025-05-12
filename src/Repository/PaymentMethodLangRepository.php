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

namespace Mollie\Repository;

use Mollie\Shared\Infrastructure\Repository\AbstractRepository;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PaymentMethodLangRepository extends AbstractRepository implements PaymentMethodLangRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(\MolPaymentMethodTranslations::class);
    }

    public function savePaymentTitleTranslation(string $idPaymentMethod, int $langId, string $translation, int $idShop): bool
    {
        if (empty($translation)) {
            return false;
        }

        $paymentMethodLangObject = $this->findOneBy([
            'id_method' => $idPaymentMethod,
            'id_lang' => $langId,
            'id_shop' => $idShop,
        ]);

        $multiLangObject = new \MolPaymentMethodTranslations($paymentMethodLangObject->id ?? null);
        $multiLangObject->id_lang = $langId;
        $multiLangObject->id_method = $idPaymentMethod;
        $multiLangObject->id_shop = $idShop;
        $multiLangObject->text = $translation;

        return $multiLangObject->save();
    }
}
