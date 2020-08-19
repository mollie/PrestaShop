<?php
/**
 * Copyright (c) 2012-2020, Mollie B.V.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * - Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR AND CONTRIBUTORS ``AS IS'' AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE AUTHOR OR CONTRIBUTORS BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
 * OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH
 * DAMAGE.
 *
 * @author     Mollie B.V. <info@mollie.nl>
 * @copyright  Mollie B.V.
 * @license    Berkeley Software Distribution License (BSD-License 2) http://www.opensource.org/licenses/bsd-license.php
 * @category   Mollie
 * @package    Mollie
 * @link       https://www.mollie.nl
 * @codingStandardsIgnoreStart
 */

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
     * @var bool
     */
    public $live_environment;

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
            'surcharge' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'surcharge_fixed_amount' => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
            'surcharge_percentage' => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
            'surcharge_limit' => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
            'images_json' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'live_environment' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
        ),
    );
}
