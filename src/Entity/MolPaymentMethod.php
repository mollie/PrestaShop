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
     * @var int
     */
    public $surcharge;

    /**
     * @var float
     */
    public $surcharge_fixed_amount_tax_excl;

    /**
     * @var int
     */
    public $tax_rules_group_id;

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
     * @var float
     */
    public $min_amount;

    /**
     * @var float
     */
    public $max_amount;

    /**
     * @var bool
     */
    public $live_environment;

    /** @var int */
    public $position;

    /**
     * @var int
     */
    public $id_shop;

    /**
     * @var array
     */
    public static $definition = [
        'table' => 'mol_payment_method',
        'primary' => 'id_payment_method',
        'fields' => [
            'id_method' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'method_name' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'enabled' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'title' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'method' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'description' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'is_countries_applicable' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'minimal_order_value' => ['type' => self::TYPE_FLOAT, 'validate' => 'isFloat'],
            'max_order_value' => ['type' => self::TYPE_FLOAT, 'validate' => 'isFloat'],
            'surcharge' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'surcharge_fixed_amount_tax_excl' => ['type' => self::TYPE_FLOAT, 'validate' => 'isFloat'],
            'tax_rules_group_id' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'surcharge_percentage' => ['type' => self::TYPE_FLOAT, 'validate' => 'isFloat'],
            'surcharge_limit' => ['type' => self::TYPE_FLOAT, 'validate' => 'isFloat'],
            'images_json' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'min_amount' => ['type' => self::TYPE_FLOAT, 'validate' => 'isFloat'],
            'max_amount' => ['type' => self::TYPE_FLOAT, 'validate' => 'isFloat'],
            'live_environment' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'position' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'id_shop' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
        ],
    ];

    public function getPaymentMethodName()
    {
        return $this->id_method;
    }
}
