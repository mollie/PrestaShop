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

namespace Mollie\Service;

use Context;
use Mollie\Handler\ErrorHandler\ErrorHandler;
use MolliePrefix\Mollie\Api\Exceptions\ApiException;
use MolliePrefix\Mollie\Api\MollieApiClient;
use Tools;

class ApiKeyService
{
	/**
	 * @param string $apiKey
	 * @param string $moduleVersion
	 *
	 * @return MollieApiClient|null
	 *
	 * @throws ApiException
	 */
	public function setApiKey($apiKey, $moduleVersion)
	{
		$api = new MollieApiClient();
		$context = Context::getContext();
		if ($apiKey) {
			try {
				$api->setApiKey($apiKey);
			} catch (ApiException $e) {
				$errorHandler = ErrorHandler::getInstance();
				$errorHandler->handle($e, $e->getCode(), false);

				return null;
			}
		} elseif (!empty($context->employee)
			&& Tools::getValue('Mollie_Api_Key')
		) {
			$api->setApiKey(Tools::getValue('Mollie_Api_Key'));
		}
		if (defined('_TB_VERSION_')) {
			$api->addVersionString('ThirtyBees/' . _TB_VERSION_);
			$api->addVersionString("MollieThirtyBees/{$moduleVersion}");
		} else {
			$api->addVersionString('PrestaShop/' . _PS_VERSION_);
			$api->addVersionString("MolliePrestaShop/{$moduleVersion}");
		}

		return $api;
	}
}
