<?php

class MolPaymentMethod extends ObjectModel
{
    /**
     * @var bool
     */
    public $enabled;

    /**
     * @var string
     */
    public $id_method;

    /**
     * @var string
     */
    public $method_name;

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $method;

    /**
     * @var string
     */
    public $description;

    /**
     * @var bool
     */
    public $is_countries_applicable;

    /**
     * @var string
     */
    public $minimal_order_value;

    /**
     * @var string
     */
    public $max_order_value;

    /**
     * @var string
     */
    public $surcharge;

    /**
     * @var string
     */
    public $surcharge_fixed_amount;

    /**
     * @var string
     */
    public $surcharge_percentage;

    /**
     * @var string
     */
    public $surcharge_limit;

    /**
     * @var string
     */
    public $images_json;

    /**
     * @var array
     */
    public static $definition = array(
        'table' => 'mol_payment_method',
        'primary' => 'id_payment_method',
        'fields' => array(
            'id_method' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'method_name' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'enabled' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'title' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'method' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'description' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'is_countries_applicable' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'minimal_order_value' => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
            'max_order_value' => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
            'surcharge' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'surcharge_fixed_amount' => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
            'surcharge_percentage' => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
            'surcharge_limit' => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
            'images_json' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
        ),
    );
}