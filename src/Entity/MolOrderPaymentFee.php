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
class MolOrderPaymentFee extends ObjectModel
{
    /**
     * @var int
     */
    public $id_cart;

    /**
     * @var int
     */
    public $id_order;

    /**
     * @var float
     */
    public $fee_tax_incl;

    /**
     * @var float
     */
    public $fee_tax_excl;

    /**
     * @var array
     */
    public static $definition = [
        'table' => 'mol_order_payment_fee',
        'primary' => 'id_mol_order_payment_fee',
        'fields' => [
            'id_cart' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'id_order' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'fee_tax_incl' => ['type' => self::TYPE_FLOAT, 'validate' => 'isFloat'],
            'fee_tax_excl' => ['type' => self::TYPE_FLOAT, 'validate' => 'isFloat'],
        ],
    ];
}
