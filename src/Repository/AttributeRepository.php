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

use Db;
use DbQuery;

if (!defined('_PS_VERSION_')) {
    exit;
}

class AttributeRepository
{
    public function hasAttributeInCombination($attrCombinationId, $attributeId)
    {
        $sql = new DbQuery();
        $sql->select('`id_attribute`');
        $sql->from('product_attribute_combination');
        $sql->where('`id_product_attribute` = "' . pSQL($attrCombinationId) . '" AND id_attribute = ' . pSQL($attributeId));

        return (bool) Db::getInstance()->getValue($sql);
    }
}
