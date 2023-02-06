<?php

class MolRecurringOrdersProduct extends ObjectModel
{
    /** @var int */
    public $id_product;

    /** @var int */
    public $id_product_attribute;

    /** @var int */
    public $quantity;

    /** @var float */
    public $unit_price;

    /** @var string */
    public $date_add;

    /** @var string */
    public $date_update;

    /**
     * @var array
     */
    public static $definition = [
        'table' => 'mol_recurring_orders_product',
        'primary' => 'id_mol_recurring_orders_product',
        'fields' => [
            'id_product' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'id_product_attribute' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'quantity' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'unit_price' => ['type' => self::TYPE_FLOAT, 'validate' => 'isPrice', 'required' => true],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'date_update' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
        ],
    ];
}
