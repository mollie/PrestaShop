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
class MolPaymentMethod extends ObjectModel
{
	/**
	 * @var bool
	 */
	public $enabled;

	/**
	 * @var string
	 */
	public $id_method;

	/**
	 * @var string
	 */
	public $method_name;

	/**
	 * @var string
	 */
	public $title;

	/**
	 * @var string
	 */
	public $method;

	/**
	 * @var string
	 */
	public $description;

	/**
	 * @var bool
	 */
	public $is_countries_applicable;

	/**
	 * @var string
	 */
	public $minimal_order_value;

	/**
	 * @var string
	 */
	public $max_order_value;

	/**
	 * @var int
	 */
	public $surcharge;

	/**
	 * @var string
	 */
	public $surcharge_fixed_amount;

	/**
	 * @var string
	 */
	public $surcharge_percentage;

	/**
	 * @var string
	 */
	public $surcharge_limit;

	/**
	 * @var string
	 */
	public $images_json;

	/**
	 * @var bool
	 */
	public $live_environment;

	/** @var int */
	public $position;

	/**
	 * @var array
	 */
	public static $definition = [
		'table' => 'mol_payment_method',
		'primary' => 'id_payment_method',
		'fields' => [
			'id_method' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
			'method_name' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
			'enabled' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
			'title' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
			'method' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
			'description' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
			'is_countries_applicable' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
			'minimal_order_value' => ['type' => self::TYPE_FLOAT, 'validate' => 'isFloat'],
			'max_order_value' => ['type' => self::TYPE_FLOAT, 'validate' => 'isFloat'],
			'surcharge' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
			'surcharge_fixed_amount' => ['type' => self::TYPE_FLOAT, 'validate' => 'isFloat'],
			'surcharge_percentage' => ['type' => self::TYPE_FLOAT, 'validate' => 'isFloat'],
			'surcharge_limit' => ['type' => self::TYPE_FLOAT, 'validate' => 'isFloat'],
			'images_json' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
			'live_environment' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
			'position' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
		],
	];

	public function getPaymentMethodName()
	{
		return $this->id_method;
	}
}
