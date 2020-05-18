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
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * @param Mollie $module
 * @return bool
 */
function upgrade_module_4_0_0()
{
    $sql = [];
    $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'mol_payment_method` (
				`id_payment_method`  INT(64)  NOT NULL PRIMARY KEY AUTO_INCREMENT,
				`id_method`  VARCHAR(64) NOT NULL,
				`method_name`  VARCHAR(64) NOT NULL,
				`enabled`       TINYINT(1),
				`title`      VARCHAR(64),
				`method` VARCHAR(64),
				`description` VARCHAR(255),
				`is_countries_applicable` TINYINT(1),
				`minimal_order_value` decimal(20,6),
				`max_order_value` decimal(20,6),
				`surcharge` INT(10),
				`surcharge_fixed_amount` decimal(20,6),
				`surcharge_percentage` decimal(20,6),
				`surcharge_limit` decimal(20,6),
				`images_json` TEXT
			) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

    $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'mol_payment_method_issuer` (
				`id_payment_method_issuer`  INT(64)  NOT NULL PRIMARY KEY AUTO_INCREMENT,
				`id_payment_method` INT(64) NOT NULL,
				`issuers_json` TEXT NOT NULL
			) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

    $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'mol_order_fee` (
				`id_mol_order_fee`  INT(64)  NOT NULL PRIMARY KEY AUTO_INCREMENT,
				`id_cart` INT(64) NOT NULL,
				`order_fee` decimal(20,6) NOT NULL
			) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

    $sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'mol_carrier_information` (
				`id_mol_carrier_information`  INT(64)  NOT NULL PRIMARY KEY AUTO_INCREMENT,
				`id_carrier` INT(64) NOT NULL,
				`url_source` VARCHAR(64) NOT NULL,
				`custom_url` VARCHAR(255)
			) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

    foreach ($sql as $query) {
        if (Db::getInstance()->execute($query) == false) {
            return false;
        }
    }

    return true;
}
