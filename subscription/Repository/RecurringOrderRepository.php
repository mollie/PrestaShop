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

use MolRecurringOrder;
use PrestaShopCollection;

if (!defined('_PS_VERSION_')) {
    exit;
}

class RecurringOrderRepository extends AbstractRepository implements RecurringOrderRepositoryInterface
{
    public function findOneBy(array $keyValueCriteria): ?MolRecurringOrder
    {
        /** @var ?MolRecurringOrder $result */
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
