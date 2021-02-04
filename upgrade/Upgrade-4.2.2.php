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

use Mollie\Config\Config;

if (!defined('_PS_VERSION_')) {
	exit;
}

function upgrade_module_4_2_2()
{
	Configuration::updateValue(Config::MOLLIE_SHOW_RESEND_PAYMENT_LINK, Config::SHOW_RESENT_LINK);

	return true;
}
