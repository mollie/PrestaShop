<?php

/**
 * Holds data for duplicated cart -> order id from which cart was duplicated.
 */
class MolPendingOrderCart extends ObjectModel
{
    /**
     * @var int
     */
    public $order_id;

    /**
     * @var int
     */
    public $cart_id;

    /** @var bool */
    public $should_cancel_order;

    /**
     * @var array
     */
    public static $definition = [
        'table' => 'mol_pending_order_cart',
        'primary' => 'id_mol_pending_order_cart',
        'fields' => [
            'order_id' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'cart_id' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'should_cancel_order' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool']
        ],
    ];
}