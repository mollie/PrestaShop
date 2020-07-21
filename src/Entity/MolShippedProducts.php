<?php

/**
 * Holds data for duplicated cart -> order id from which cart was duplicated.
 */
class MolShippedProducts extends ObjectModel
{
    /**
     * @var string
     */
    public $shipment_id;

    /**
     * @var string
     */
    public $mollie_order_id;

    /**
     * @var int
     */
    public $order_id;

    /**
     * @var int
     */
    public $product_id;

    /**
     * @var int
     */
    public $quantity;

    /**
     * @var float
     */
    public $unit_price;

    /**
     * @var float
     */
    public $total_amount;

    /**
     * @var string
     */
    public $currency;

    /**
     * @var array
     */
    public static $definition = [
        'table' => 'mol_shipped_product',
        'primary' => 'id_mol_shipped_product',
        'fields' => [
            'shipment_id' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'mollie_order_id' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'order_id' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'product_id' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'quantity' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'unit_price' => ['type' => self::TYPE_FLOAT, 'validate' => 'isFloat'],
            'total_amount' => ['type' => self::TYPE_FLOAT, 'validate' => 'isFloat'],
            'currency' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
        ],
    ];
}