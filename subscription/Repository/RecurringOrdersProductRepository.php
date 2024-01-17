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

class RecurringOrdersProductRepository extends AbstractRepository implements RecurringOrdersProductRepositoryInterface
{
    public function findOneBy(array $keyValueCriteria): ?MolRecurringOrdersProduct
    {
        /** @var ?MolRecurringOrdersProduct $result */
        $result = parent::findOneBy($keyValueCriteria);

        return $result;
    }

    public function findAllBy(array $keyValueCriteria): ?PrestaShopCollection
    {
        /** @var ?PrestaShopCollection $result */
        $result = parent::findAllBy($keyValueCriteria);

        return $result;
    }
}
