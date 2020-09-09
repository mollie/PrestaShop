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
use Mollie\Install\Installer;
use Mollie\Service\imageService;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * @param Mollie $module
 * @return bool
 */

function upgrade_module_4_0_7($module)
{
    Configuration::updateValue(Config::MOLLIE_STATUS_SHIPPING, Configuration::get('PS_OS_SHIPPING'));
    Configuration::updateValue(Config::MOLLIE_STATUS_SHIPPING, true);
    Configuration::updateValue(Config::MOLLIE_SEND_ORDER_CONFIRMATION, Config::ORDER_CONF_MAIL_SEND_ON_NEVER);

    $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'mol_excluded_country` (
				`id_mol_country`  INT(64)  NOT NULL PRIMARY KEY AUTO_INCREMENT,
				`id_method`       VARCHAR(64),
				`id_country`      INT(64),
				`all_countries` tinyint
			) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

    $sql .= '
        CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'mol_pending_order_cart` (
				`id_mol_pending_order_cart`  INT(64)  NOT NULL PRIMARY KEY AUTO_INCREMENT,
				`order_id` INT(64) NOT NULL,
				`cart_id` INT(64) NOT NULL
			) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;
    ';

    if (Db::getInstance()->execute($sql) == false) {
        return false;
    }

    $module->registerHook('actionAdminOrdersListingFieldsModifier');
    $module->registerHook('actionAdminControllerSetMedia');
    $module->registerHook('actionValidateOrder');

    /** @var Installer $installer */
    $installer = $module->getContainer(Installer::class);
    $installed = true;

    $installed &= $installer->installTab('AdminMollieAjax', 0, 'AdminMollieAjax', false);
    $installed &= $installer->installTab('AdminMollieModule', 0, 'Mollie', false, 'mollie');

    $installed &= $installer->partialShippedOrderState();
    $installed &= $installer->orderCompletedOrderState();
    $installed &= $installer->copyEmailTemplates();

    Configuration::updateValue(
        Config::MOLLIE_STATUS_COMPLETED,
        Configuration::get(Config::MOLLIE_STATUS_ORDER_COMPLETED)
    );
    Configuration::updateValue(Config::MOLLIE_MAIL_WHEN_COMPLETED, true);

    if (!$installed) {
        return false;
    }

    return true;
}
