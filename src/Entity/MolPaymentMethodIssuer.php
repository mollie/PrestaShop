<?php

class MolPaymentMethodIssuer extends ObjectModel
{
    /**
     * @var int
     */
    public $id_payment_method;

    /**
     * @var string
     */
    public $issuers_json;

    /**
     * @var array
     */
    public static $definition = array(
        'table' => 'mol_payment_method_issuer',
        'primary' => 'id_payment_method_issuer',
        'fields' => array(
            'id_payment_method' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'issuers_json' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
        ),
    );
}