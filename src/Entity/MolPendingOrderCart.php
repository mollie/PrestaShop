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

    /**
     * @var array
     */
    public static $definition = array(
        'table' => 'mol_pending_order_cart',
        'primary' => 'id_mol_pending_order_cart',
        'fields' => array(
            'order_id' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'cart_id' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
        ),
    );
}