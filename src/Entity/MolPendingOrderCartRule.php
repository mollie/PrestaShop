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
class MolPendingOrderCartRule extends ObjectModel
{
	/**
	 * @var int
	 */
	public $id_order;

	/**
	 * @var int
	 */
	public $id_cart_rule;

	/**
	 * @var int
	 */
	public $id_order_invoice;

	/**
	 * @var string
	 */
	public $name;

	/**
	 * @var float
	 */
	public $value_tax_incl;

	/**
	 * @var float
	 */
	public $value_tax_excl;

	/**
	 * @var bool
	 */
	public $free_shipping;

	/**
	 * @var array
	 */
	public static $definition = [
		'table' => 'mol_pending_order_cart_rule',
		'primary' => 'id_mol_pending_order_cart_rule',
		'fields' => [
			'id_order' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
			'id_cart_rule' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
			'id_order_invoice' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
			'name' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
			'value_tax_incl' => ['type' => self::TYPE_FLOAT, 'validate' => 'isFloat'],
			'value_tax_excl' => ['type' => self::TYPE_FLOAT, 'validate' => 'isFloat'],
			'free_shipping' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
		],
	];
}
