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
if (!defined('_PS_VERSION_')) {
	exit;
}

/**
 * @param Mollie $module
 *
 * @return bool
 *
 * @throws PrestaShopDatabaseException
 * @throws PrestaShopException
 */
function upgrade_module_3_3_0($module)
{
	try {
		if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
                SELECT COUNT(*)
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = \''._DB_NAME_.'\'
                AND TABLE_NAME = \''._DB_PREFIX_.'mollie_payments\'
                AND COLUMN_NAME = \'order_reference\'')) {
			Db::getInstance()->execute(
				'ALTER TABLE `'._DB_PREFIX_.'mollie_payments` ADD `order_reference` varchar(191)'
			);
		}
	} catch (PrestaShopException $e) {
		PrestaShopLogger::addLog("Mollie update error: {$e->getMessage()}");
	}

	if (method_exists($module, 'setDefaultCarrierStatuses')) {
		$module->setDefaultCarrierStatuses();
	}

	Configuration::updateValue('MOLLIE_API', 'payments');

	// Major changes, need to clear the cache
	if (!Mollie::$cacheCleared) {
		if (method_exists('Tools', 'clearAllCache')) {
			Tools::clearAllCache();
		}
		if (method_exists('Tools', 'clearCache')) {
			Tools::clearCache();
		}
		Mollie::$cacheCleared = true;
	}

	return true;
}
