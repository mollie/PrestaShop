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
use Country;
use Mollie;

class CountryService
{
	/**
	 * @var Mollie
	 */
	private $module;

	public function __construct(Mollie $module)
	{
		$this->module = $module;
	}

	public function getActiveCountriesList($onlyActive = true)
	{
		$context = Context::getContext();
		$langId = $context->language->id;
		$countries = Country::getCountries($langId, $onlyActive);
		$countriesWithNames = [];
		$countriesWithNames[] = [
			'id' => 0,
			'name' => $this->module->l('All'),
		];
		foreach ($countries as $key => $country) {
			$countriesWithNames[] = [
				'id' => $key,
				'name' => $country['name'],
			];
		}

		return $countriesWithNames;
	}
}
