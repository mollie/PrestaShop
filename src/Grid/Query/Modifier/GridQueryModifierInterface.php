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

interface GridQueryModifierInterface
{
	/**
	 * Used to modify Grid Query Builder.
	 *
	 * @param QueryBuilder $queryBuilder
	 */
	public function modify(QueryBuilder $queryBuilder);
}
