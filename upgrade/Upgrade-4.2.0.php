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
 *
 * @category   Mollie
 *
 * @see       https://www.mollie.nl
 */

use Mollie\Config\Config;
use Mollie\Install\Installer;

if (!defined('_PS_VERSION_')) {
	exit;
}

/**
 * @param Mollie $module
 *
 * @return bool
 */
function upgrade_module_4_2_0($module)
{
	/** @var Installer $installer */
	$installer = $module->getMollieContainer(Installer::class);

	$installer->klarnaPaymentAuthorizedState();
	$installer->klarnaPaymentShippedState();

	$acceptedStatusId = Configuration::get(Config::MOLLIE_STATUS_KLARNA_AUTHORIZED);
	Configuration::updateValue(Config::MOLLIE_KLARNA_INVOICE_ON, $acceptedStatusId);

	$module->registerHook('actionOrderGridQueryBuilderModifier');
	$module->registerHook('actionOrderGridDefinitionModifier');

	Db::getInstance()->execute(' CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'mol_pending_order_cart_rule` (
            `id_mol_pending_order_cart_rule` INT(64) NOT NULL PRIMARY KEY AUTO_INCREMENT,
            `id_order` VARCHAR(64) NOT NULL,
            `id_cart_rule` VARCHAR(64) NOT NULL,
            `name` VARCHAR(64) NOT NULL,
            `value_tax_incl` decimal(20,6) NOT NULL,
            `value_tax_excl` decimal(20,6) NOT NULL,
            `free_shipping` TINYINT(1) NOT NULL,
            `id_order_invoice` INT(64) NOT NULL
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;'
    );

	return true;
}
