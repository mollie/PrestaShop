<?php

/**
 * Holds data for duplicated cart -> order id from which cart was duplicated.
 */
class MolShippedProducts extends ObjectModel
{
    /**
     * @var int
     */
    public $order_id;

    /**
     * @var int
     */
    public $cart_id;

    /**
     * @var array
     */
    public static $definition = [
        'table' => 'mol_shipped_product',
        'primary' => 'id_mol_shipped_product',
        'fields' => [
            'shipment_id' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'mollie_order_id' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
        ],
    ];
}