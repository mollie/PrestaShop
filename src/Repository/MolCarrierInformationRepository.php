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

use Db;
use DbQuery;

class MolCarrierInformationRepository
{
	public function getMollieCarrierInformationIdByCarrierId($carrierId)
	{
		$sql = new DbQuery();
		$sql->select('id_mol_carrier_information');
		$sql->from('mol_carrier_information');
		$sql->where('`id_carrier` = ' . (int) $carrierId . '');

		return Db::getInstance()->getValue($sql);
	}
}
