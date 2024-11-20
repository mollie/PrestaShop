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

use Mollie\Adapter\Context;
use Mollie\Repository\MultiLangRepositoryInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PaymentMethodLangService
{
    /** @var MultiLangRepositoryInterface */
    private $multiLangRepository;

    /** @var Context */
    private $context;

    public function __construct(MultiLangRepositoryInterface $multiLangRepository, Context $context)
    {
        $this->multiLangRepository = $multiLangRepository;
        $this->context = $context;
    }

    public function savePaymentTitleTranslation(string $idPaymentMethod, int $langId, string $translation, int $idShop)
    {
        if (empty($translation)) {
            return;
        }

        $obj = $this->multiLangRepository->findOneBy([
            'id_method' => $idPaymentMethod,
            'id_lang' => $langId,
            'id_shop' => $this->context->getShopId(),
        ]);

        $multiLangObject = new \MolPaymentMethodLang($obj->id);
        $multiLangObject->id_lang = $langId;
        $multiLangObject->id_method = $idPaymentMethod;
        $multiLangObject->id_shop = $this->context->getShopId();
        $multiLangObject->text = $translation;
        $multiLangObject->save();
    }

    public function trans(string $idMethod): ?string
    {
        return $this->multiLangRepository->getTextByLanguageAndMethod($this->context->getLanguageId(), $idMethod, $this->context->getShopId());
    }


    /**
     * Gets all translations for a payment method title
     *
     * @param string $idMethod
     * @return array [id_lang => text] for instance: [74 => 'Apelo Pay', 68 => 'Apella Pia']
     */
    public function getTransList(string $idMethod): array
    {
        $result = $this->multiLangRepository->getAllTranslationsByMethod($idMethod, $this->context->getShopId());



        $mappedArray = [];
        foreach ($result as $value) {
            $mappedArray[$value['id_lang']] = $value['text'];
        }

        return $mappedArray;
    }
}
