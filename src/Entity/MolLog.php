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

class MolLog extends ObjectModel
{
    public $id_mollie_log;

    public $id_log;

    public $id_shop;

    public $request;

    public $response;

    public $context;

    public $date_add;

    public static $definition = [
        'table' => 'mol_logs',
        'primary' => 'id_mollie_log',
        'fields' => [
            'id_log' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'id_shop' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'request' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'response' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'context' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
        ],
    ];
}
