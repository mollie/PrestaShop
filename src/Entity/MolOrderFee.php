<?php

class MolOrderFee extends ObjectModel
{
    /**
     * @var int
     */
    public $id_order;

    /**
     * @var float
     */
    public $order_fee;

    /**
     * @var array
     */
    public static $definition = array(
        'table' => 'mol_order_fee',
        'primary' => 'id_mol_order_fee',
        'fields' => array(
            'id_order' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'order_fee' => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
        ),
    );
}