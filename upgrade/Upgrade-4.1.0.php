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
 * <<<<<<< HEAD.
 *
 * @param Mollie $module
 *
 * @return bool
 */
function upgrade_module_4_1_0($module)
{
	/** @var Installer $installer */
	$installer = $module->getContainer(Installer::class);

	$awaitingOrderStatusId = Configuration::get(Config::STATUS_MOLLIE_AWAITING);
	$orderStatus = new OrderState($awaitingOrderStatusId);

	if (!Validate::isLoadedObject($orderStatus) || $orderStatus->deleted) {
		$installer->createAwaitingMollieOrderState();
	}

	$sql = '
        ALTER TABLE '._DB_PREFIX_.'mol_payment_method
        ADD `position` INT(10);
    ';

	$sql[] = '
    CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'mol_payment_method_invoice_status` (
				`id_mol_payment_method_invoice_status`  INT(64)  NOT NULL PRIMARY KEY AUTO_INCREMENT,
				`id_method` VARCHAR(64) NOT NULL,
				`id_state` INT(64) NOT NULL,
				`live_environment` TINYINT(1) NOT NULL
			) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

	$isAdded = Db::getInstance()->execute($sql);

	if (!$isAdded) {
		return false;
	}

	/** @var \Mollie\Repository\PaymentMethodRepositoryInterface $paymentMethodsRepo */
	$paymentMethodsRepo = $module->getContainer(\Mollie\Repository\PaymentMethodRepositoryInterface::class);
	$paymentMethods = $paymentMethodsRepo->findAll();

	/** @var Installer $installer */
	$installer = $module->getContainer(Installer::class);
	$installer->installVoucherFeatures();

	foreach ($installer::getHooks() as $hook) {
		$module->registerHook($hook);
	}

	$isUpdated = true;
	// adding positions for all payments in order they exist in database
	$iteration = 0;
	/** @var MolPaymentMethod $paymentMethod */
	foreach ($paymentMethods as $paymentMethod) {
		$paymentMethod->position = $iteration;

		++$iteration;

		$isUpdated &= $paymentMethod->update();
	}

	return $isUpdated;
}
