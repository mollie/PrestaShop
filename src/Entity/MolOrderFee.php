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
class MolOrderFee extends ObjectModel
{
	/**
	 * @var int
	 */
	public $id_cart;

	/**
	 * @var float
	 */
	public $order_fee;

	/**
	 * @var array
	 */
	public static $definition = [
		'table' => 'mol_order_fee',
		'primary' => 'id_mol_order_fee',
		'fields' => [
			'id_cart' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
			'order_fee' => ['type' => self::TYPE_FLOAT, 'validate' => 'isFloat'],
		],
	];
}
