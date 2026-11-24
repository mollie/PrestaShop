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

namespace Mollie\Grid\Query\Modifier;

use Doctrine\DBAL\Query\QueryBuilder;
use Mollie\Api\Types\PaymentStatus;

if (!defined('_PS_VERSION_')) {
    exit;
}

class OrderGridQueryModifier implements GridQueryModifierInterface
{
    /**
     * {@inheritDoc}
     */
    public function modify(QueryBuilder $queryBuilder)
    {
        // A scalar subquery instead of a join: a join on a near-empty payments table makes MariaDB
        // buffer it and sort every order before the page limit. It also returns one row per order:
        // the paid or authorized payment first, then the newest one.
        $queryBuilder->addSelect(sprintf(
            '(SELECT mol.`transaction_id` FROM `%smollie_payments` mol'
            . ' WHERE mol.`cart_id` = o.`id_cart` AND mol.`order_id` > 0'
            . ' ORDER BY mol.`bank_status` IN (\'%s\', \'%s\') DESC, mol.`created_at` DESC, mol.`transaction_id` DESC'
            . ' LIMIT 1) AS `transaction_id`',
            pSQL(_DB_PREFIX_),
            PaymentStatus::STATUS_PAID,
            PaymentStatus::STATUS_AUTHORIZED
        ));
    }
}
