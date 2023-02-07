<?php

declare(strict_types=1);

namespace Mollie\Adapter;

use Mollie\Exception\MollieException;

class ProductAttributeAdapter
{
    /**
     * @return \AttributeCore|\ProductAttributeCore
     *
     * @throws MollieException
     */
    public function getProductAttribute(?int $id = null, ?int $idLang = null, ?int $idShop = null)
    {
        if (class_exists('AttributeCore')) {
            return new \AttributeCore($id, $idLang, $idShop);
        }

        if (class_exists('ProductAttributeCore')) {
            return new \ProductAttributeCore($id, $idLang, $idShop);
        }

        throw new MollieException('Attribute class was not found');
    }
}
