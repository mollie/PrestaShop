<?php

class MolCarrierInformation extends ObjectModel
{
    /**
     * @var int
     */
    public $id_carrier;

    /**
     * @var string
     */
    public $url_source;

    /**
     * @var string
     */
    public $custom_url;

    /**
     * @var array
     */
    public static $definition = array(
        'table' => 'mol_carrier_information',
        'primary' => 'id_mol_carrier_information',
        'fields' => array(
            'id_carrier' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'url_source' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'custom_url' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
        ),
    );
}