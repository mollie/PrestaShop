<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 *
 * @see        https://github.com/mollie/PrestaShop
 *
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 */
if (!defined('_PS_VERSION_')) {
	exit;
}

/**
 * @param Mollie $module
 *
 * @return bool
 */
function upgrade_module_4_0_6()
{
	$languageId = Context::getContext()->language->id;
	$states = OrderState::getOrderStates((int) $languageId);
	$moduleClass = Module::getInstanceByName('mollie');
	foreach ($states as $state) {
		if ($moduleClass->l('Awaiting Mollie payment') === $state['name']) {
			Configuration::updateValue(
				Mollie\Config\Config::STATUS_MOLLIE_AWAITING,
				(int) $state[OrderState::$definition['primary']]
			);
			break;
		}
	}

	Configuration::updateValue(Mollie\Config\Config::MOLLIE_SEND_ORDER_CONFIRMATION, false);

	return true;
}
