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

use Mollie\Config\Config;
use Mollie\Utility\MultiLangUtility;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * @param Mollie $module
 * @return bool
 */

function upgrade_module_4_0_8($module)
{
    Configuration::updateValue(Config::MOLLIE_SINGLE_CLICK_PAYMENT, 0);
    Configuration::updateValue(Config::MOLLIE_ENVIRONMENT, Config::ENVIRONMENT_LIVE);

    $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'mol_customer` (
				`id_mol_customer`  INT(64)  NOT NULL PRIMARY KEY AUTO_INCREMENT,
				`customer_id` VARCHAR(64) NOT NULL,
				`name` VARCHAR(64) NOT NULL,
				`email` VARCHAR(64) NOT NULL,
				`created_at` VARCHAR(64) NOT NULL
			) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;
';
    $sql .= '
        ALTER TABLE ' . _DB_PREFIX_ . 'mol_payment_method
        ADD COLUMN live_environment TINYINT(1) DEFAULT 1;
     ';

    $sql .= '
        ALTER TABLE `' . _DB_PREFIX_ . 'mollie_payments` ADD INDEX(`order_reference`);
     ';

    if (Db::getInstance()->execute($sql) == false) {
        return false;
    }
    $tabId = Tab::getIdFromClassName('AdminMollieModule');
    $tab = new Tab($tabId);
    $tab->name = MultiLangUtility::createMultiLangField('Mollie');
    $tab->icon = 'mollie';
    $tab->active = true;
    $tab->update();

    return true;
}
