<?php

class MollieCart extends ObjectModel
{
    public $id_mollie_cart;

    public $id_cart;

    public $id_shop;

    public static $definition = [
        'table' => 'mollie_cart',
        'primary' => 'id_mollie_cart',
        'fields' => [
            'id_cart' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'id_shop' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
        ],
    ];
}
