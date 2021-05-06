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
 */
if (!defined('_PS_VERSION_')) {
	exit;
}

/**
 * @param Mollie $module
 *
 * @return bool
 */
function upgrade_module_4_3_0($module)
{
	$sql = [];

	$sql[] = '
        ALTER TABLE ' . _DB_PREFIX_ . 'mol_payment_method
        ADD `id_shop` INT(64) DEFAULT 1;
    ';

	$sql[] = '
        ALTER TABLE ' . _DB_PREFIX_ . 'mol_payment_method_order_total_restriction
        ADD `id_shop` INT(64) DEFAULT 1;
    ';

	foreach ($sql as $query) {
		if (false == Db::getInstance()->execute($query)) {
			return false;
		}
	}

	/** @var \Mollie\Repository\PaymentMethodRepositoryInterface $paymentMethodRepo */
	$paymentMethodRepo = $module->getMollieContainer(\Mollie\Repository\PaymentMethodRepositoryInterface::class);

	$sql = new DbQuery();
	$sql->select('`id_mol_country`, `id_method`');
	$sql->from('mol_country');

	$molCountries = Db::getInstance()->executeS($sql);

	foreach ($molCountries as $molCountry) {
		$paymentMethod = $paymentMethodRepo->findOneBy(['id_method' => $molCountry['id_method']]);
		if (!$paymentMethod) {
			continue;
		}
		Db::getInstance()->execute('UPDATE ' . _DB_PREFIX_ . 'mol_country SET id_method = ' .
			(int) $paymentMethod->id . ' WHERE id_mol_country = ' . (int) $molCountry['id_mol_country']);
	}

	$sql = new DbQuery();
	$sql->select('`id_mol_country`, `id_method`');
	$sql->from('mol_excluded_country');

	$molCountries = Db::getInstance()->executeS($sql);

	foreach ($molCountries as $molCountry) {
		$paymentMethod = $paymentMethodRepo->findOneBy(['id_method' => $molCountry['id_method']]);
		if (!$paymentMethod) {
			continue;
		}
		Db::getInstance()->execute('UPDATE ' . _DB_PREFIX_ . 'mol_excluded_country SET id_method = ' .
			(int) $paymentMethod->id . ' WHERE id_mol_country = ' . (int) $molCountry['id_mol_country']);
	}

	return true;
}
