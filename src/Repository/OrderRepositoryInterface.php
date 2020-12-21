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

namespace Mollie\Repository;

use Order;

interface OrderRepositoryInterface extends ReadOnlyRepositoryInterface
{
	/**
	 * @param int $id_cart
	 *
	 * @return Order|null
	 *
	 * @throws \PrestaShopException
	 */
	public function findOneByCartId($id_cart);
}
