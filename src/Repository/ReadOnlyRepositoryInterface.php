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

use ObjectModel;
use PrestaShopCollection;

interface ReadOnlyRepositoryInterface
{
	/**
	 * @return PrestaShopCollection
	 */
	public function findAll();

	/**
	 * @param array $keyValueCriteria - e.g [ 'id_cart' => 5 ]
	 *
	 * @return ObjectModel|null
	 */
	public function findOneBy(array $keyValueCriteria);

	/**
	 * @param array $keyValueCriteria - e.g [ 'id_cart' => 5 ]
	 *
	 * @return PrestaShopCollection|null
	 */
	public function findAllBy(array $keyValueCriteria);
}
