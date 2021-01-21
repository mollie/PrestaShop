<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 *
 * @see        https://github.com/mollie/PrestaShop
 *
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 * @codingStandardsIgnoreStart
 */

namespace Mollie\Grid\Query\Modifier;

use Doctrine\DBAL\Query\QueryBuilder;

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
			'mol.`order_reference` = o.`reference` AND mol.`cart_id` = o.`id_cart`'
		);
	}
}
