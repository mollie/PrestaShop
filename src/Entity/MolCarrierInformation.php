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
class MolCarrierInformation extends ObjectModel
{
	/**
	 * @var int
	 */
	public $id_carrier;

	/**
	 * @var string
	 */
	public $url_source;

	/**
	 * @var string
	 */
	public $custom_url;

	/**
	 * @var array
	 */
	public static $definition = [
		'table' => 'mol_carrier_information',
		'primary' => 'id_mol_carrier_information',
		'fields' => [
			'id_carrier' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
			'url_source' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
			'custom_url' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
		],
	];
}
