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

namespace Mollie\Subscription\Repository;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ProductCombinationRepository
{
    public function getIds(int $combinationId): array
    {
        $query = new \DbQuery();
        $query
            ->select('combination.id_attribute')
            ->from('product_attribute_combination', 'combination')
            ->where('combination.id_product_attribute = ' . $combinationId);

        return \Db::getInstance()->executeS($query) ?: [];
    }
}
