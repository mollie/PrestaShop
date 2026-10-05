<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 *
 * @see        https://github.com/mollie/PrestaShop
 * @codingStandardsIgnoreStart
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class MolExpressSession extends ObjectModel
{
    public $id_cart;
    public $id_shop;
    public $session_id;
    public $amount;
    public $currency_iso;
    public $status;
    public $created_at;
    public $updated_at;

    public static $definition = [
        'table' => 'mol_express_session',
        'primary' => 'id_mol_express_session',
        'fields' => [
            'id_cart' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'id_shop' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'session_id' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'size' => 64],
            'amount' => ['type' => self::TYPE_FLOAT, 'validate' => 'isPrice', 'required' => true],
            'currency_iso' => ['type' => self::TYPE_STRING, 'validate' => 'isLanguageIsoCode', 'required' => true, 'size' => 3],
            'status' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 32],
            'created_at' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'updated_at' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
        ],
    ];
}
