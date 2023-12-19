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

use Mollie\Shared\Infrastructure\Repository\AbstractRepository;

if (!defined('_PS_VERSION_')) {
    exit;
}

class RecurringOrderRepository extends AbstractRepository implements RecurringOrderRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(\MolRecurringOrder::class);
    }

    public function getAllOrdersBasedOnStatuses(array $statuses, int $shopId): array
    {
        $query = new \DbQuery();

        $query
            ->select(
                'mro.id_mol_recurring_order as id, mro.mollie_subscription_id,
                mro.mollie_customer_id, mro.id_cart,
                mro.id_mol_recurring_product as id_recurring_product,
                mro.id_invoice_address, mro.id_delivery_address'
            )
            ->from('mol_recurring_order', 'mro')
            ->leftJoin(
                'orders', 'o',
                'o.id_order = mro.id_order'
            )
            ->where('mro.status IN (\'' . implode("','", $statuses) . '\')')
            ->where('o.id_shop = ' . $shopId);

        $result = \Db::getInstance()->executeS($query);

        return !empty($result) ? $result : [];
    }
}
