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

class MolRecurringOrder extends ObjectModel
{
    /** @var int */
    public $id_mol_recurring_orders_product;

    /** @var int */
    public $id_order;

    /** @var int */
    public $id_cart;

    /** @var int */
    public $id_currency;

    /** @var int */
    public $id_customer;

    /** @var int */
    public $id_address_delivery;

    /** @var int */
    public $id_address_invoice;

    /** @var string */
    public $description;

    /** @var string */
    public $status;

    /** @var float */
    public $total_tax_incl;

    /** @var string */
    public $payment_method;

    /** @var string */
    public $next_payment;

    /** @var string */
    public $reminder_at;

    /** @var string */
    public $cancelled_at;

    /** @var string */
    public $mollie_subscription_id;

    /** @var string */
    public $mollie_customer_id;

    /** @var string */
    public $date_add;

    /** @var string */
    public $date_update;

    /**
     * @var array
     */
    public static $definition = [
        'table' => 'mol_recurring_order',
        'primary' => 'id_mol_recurring_order',
        'fields' => [
            'id_mol_recurring_orders_product' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'id_order' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'id_cart' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'id_currency' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'id_customer' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'id_address_delivery' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'id_address_invoice' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'mollie_subscription_id' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'mollie_customer_id' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'description' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'status' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'total_tax_incl' => ['type' => self::TYPE_FLOAT, 'validate' => 'isFloat'],
            'payment_method' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'next_payment' => ['type' => self::TYPE_DATE],
            'reminder_at' => ['type' => self::TYPE_DATE],
            'cancelled_at' => ['type' => self::TYPE_DATE],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'date_update' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
        ],
    ];
}
