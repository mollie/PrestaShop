<?php

class MolSubRecurringOrder extends ObjectModel
{
    /** @var int */
    public $id_order;

    /** @var string */
    public $description;

    /** @var string */
    public $status;

    /** @var string */
    public $quantity;

    /** @var string */
    public $amount;

    /** @var string */
    public $currency_iso;

    /** @var string */
    public $next_payment;

    /** @var string */
    public $reminder_at;

    /** @var string */
    public $cancelled_at;

    /** @var string */
    public $mollie_sub_id;

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
        'table' => 'mol_sub_recurring_order',
        'primary' => 'id_mol_sub_recurring_order',
        'fields' => [
            'id_order' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'description' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'status' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'quantity' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'amount' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'currency_iso' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'next_payment' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'reminder_at' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'cancelled_at' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'mollie_sub_id' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'mollie_customer_id' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'date_update' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
        ],
    ];
}
