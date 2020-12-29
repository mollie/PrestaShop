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
class MolPaymentMethodIssuer extends ObjectModel
{
	/**
	 * @var int
	 */
	public $id_payment_method;

	/**
	 * @var string
	 */
	public $issuers_json;

	/**
	 * @var array
	 */
	public static $definition = [
		'table' => 'mol_payment_method_issuer',
		'primary' => 'id_payment_method_issuer',
		'fields' => [
			'id_payment_method' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
			'issuers_json' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
		],
	];
}
