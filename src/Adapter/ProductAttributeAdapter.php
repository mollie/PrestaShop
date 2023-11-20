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

namespace Mollie\Adapter;

use Mollie\Exception\MollieException;

if (!defined('_PS_VERSION_')) {
    exit;
}

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
