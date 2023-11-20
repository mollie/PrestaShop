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
