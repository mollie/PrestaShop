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
class MolCustomer extends ObjectModel
{
	/**
	 * @var string
	 */
	public $customer_id;

	/**
	 * @var string
	 */
	public $name;

	/**
	 * @var string
	 */
	public $email;

	/**
	 * @var string
	 */
	public $created_at;

	/**
	 * @var array
	 */
	public static $definition = [
		'table' => 'mol_customer',
		'primary' => 'id_mol_customer',
		'fields' => [
			'customer_id' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
			'name' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
			'email' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
			'created_at' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
		],
	];
}
