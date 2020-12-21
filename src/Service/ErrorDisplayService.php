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

namespace Mollie\Service;

use Context;
use Mollie\Config\Config;
use Tools;

class ErrorDisplayService
{
	public function showCookieError($id)
	{
		$context = Context::getContext();
		if (isset($context->cookie->$id)) {
			if (Config::isVersion17()) {
				$context->controller->warning = $this->stripSlashesDeep(json_decode($context->cookie->$id));
			} else {
				$context->controller->errors = $this->stripSlashesDeep(json_decode($context->cookie->$id));
			}
			unset($context->cookie->$id);
			unset($_SERVER['HTTP_REFERER']);
		}
	}

	private function stripSlashesDeep($value)
	{
		$value = is_array($value) ?
			array_map('stripslashes', $value) :
			Tools::stripslashes($value);

		return $value;
	}
}
