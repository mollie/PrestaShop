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

namespace Mollie\Subscription\Form\ChoiceProvider;

use Mollie\Repository\CarrierRepositoryInterface;
use PrestaShop\PrestaShop\Core\Form\FormChoiceProviderInterface;
use PrestaShopBundle\Entity\Repository\AttributeGroupLangRepository;


if (!defined('_PS_VERSION_')) {
    exit;
}

class AttributeGroupOptionsProvider implements FormChoiceProviderInterface
{
    /** @var attributeGroupLangRepository */
    private $attributeGroupLangRepository;
    
    /** @var int */
    private $idLang;

    public function __construct(
        AttributeGroupLangRepository $attributeGroupLangRepository,
        int $idLang
    ) {
        $this->attributeGroupLangRepository = $attributeGroupLangRepository;
        $this->idLang = $idLang;
    }

    public function getChoices(): array
    {
        /** @var \AttributeGroupLang[] $attributeGroupLangs */
        $attributeGroupLangs = $this->attributeGroupLangRepository->findBy(['lang' => $this->idLang,]);

        $choices = [];

        foreach ($attributeGroupLangs as $attributeGroupLang) {
            $choices[$attributeGroupLang->getName()] = $attributeGroupLang->getAttributeGroup()->getId();
        }

        return $choices;
    }
}
