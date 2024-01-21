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

use MolRecurringOrdersProduct;
use PrestaShopCollection;

if (!defined('_PS_VERSION_')) {
    exit;
}

interface RecurringOrdersProductRepositoryInterface
{
    /**
     * @param array $keyValueCriteria - e.g [ 'id_cart' => 5 ]
     *
     * @return ?MolRecurringOrdersProduct
     */
    public function findOneBy(array $keyValueCriteria): ?MolRecurringOrdersProduct;

    /**
     * @param array $keyValueCriteria - e.g [ 'id_cart' => 5 ]
     *
     * @return ?PrestaShopCollection
     */
    public function findAllBy(array $keyValueCriteria): ?PrestaShopCollection;
}
