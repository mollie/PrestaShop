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
 * @codingStandardsIgnoreStart
 */

use Mollie\Config\Config;

class AdminMollieModuleController extends ModuleAdminController
{
	public function postProcess()
	{
		if (Config::isVersion17()) {
			Tools::redirectAdmin(
			/* @phpstan-ignore-next-line */
			$this->context->link->getAdminLink(
					'AdminModules',
					true,
					[],
					[
						'configure' => 'mollie',
					]
				)
			);
		}

		Tools::redirectAdmin(
			$this->context->link->getAdminLink('AdminModules') . '&configure=mollie'
		);
	}
}
