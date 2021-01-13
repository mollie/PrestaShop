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

use Mollie;

class PaymentsTranslationService
{
	/**
	 * @var Mollie
	 */
	private $module;
	/**
	 * @var LanguageService
	 */
	private $languageService;

	public function __construct(
		Mollie $module,
		LanguageService $languageService
	) {
		$this->module = $module;
		$this->languageService = $languageService;
	}

	public function getTranslatedPaymentMethods($paymentMethods)
	{
		foreach ($paymentMethods as $method) {
			$method['method_name'] = $this->languageService->lang($method['method_name']);
		}

		return $paymentMethods;
	}
}
