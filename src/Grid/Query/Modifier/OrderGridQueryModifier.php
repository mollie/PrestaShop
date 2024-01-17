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
        $queryBuilder->addSelect('mol.`transaction_id`');

        $queryBuilder->leftJoin(
            'o',
            '`' . pSQL(_DB_PREFIX_) . 'mollie_payments`',
            'mol',
            'mol.`cart_id` = o.`id_cart` AND mol.order_id > 0'
        );
    }
}
