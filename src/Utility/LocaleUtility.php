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

namespace Mollie\Utility;

use Configuration;
use Context;
use Language;
use Tools;

class LocaleUtility
{
	/**
	 * @return string
	 */
	public static function getWebShopLocale()
	{
		// Current language
		$language = Context::getContext()->language->iso_code;

		$supportedLanguages = [
			'de',
			'en',
			'es',
			'fr',
			'nl',
			'ca',
			'pt',
			'it',
			'no',
			'sv',
			'fi',
			'da',
			'is',
			'hu',
			'pl',
			'lv',
			'lt',
		];

		$supportedLocales = [
			'en_US',
			'de_AT',
			'de_CH',
			'de_DE',
			'es_ES',
			'fr_BE',
			'fr_FR',
			'nl_BE',
			'nl_NL',
			'ca_ES',
			'pt_PT',
			'it_IT',
			'nb_NO',
			'sv_SE',
			'fi_FI',
			'da_DK',
			'is_IS',
			'hu_HU',
			'pl_PL',
			'lv_LV',
			'lt_LT',
		];

		$langIso = Tools::strtolower($language);
		if (!in_array($langIso, $supportedLanguages)) {
			$langIso = 'en';
		}
		$countryIso = Tools::strtoupper(Configuration::get('PS_LOCALE_COUNTRY'));
		if (!in_array("{$langIso}_{$countryIso}", $supportedLocales)) {
			switch ($langIso) {
				case 'de':
					$countryIso = 'DE';
					break;
				case 'ca':
				case 'es':
					$countryIso = 'ES';
					break;
				case 'fr':
					$countryIso = 'FR';
					break;
				case 'nl':
					$countryIso = 'NL';
					break;
				case 'pt':
					$countryIso = 'PT';
					break;
				case 'it':
					$countryIso = 'IT';
					break;
				case 'no':
				case 'nn':
					$langIso = 'nb';
					$countryIso = 'NO';
					break;
				case 'sv':
					$countryIso = 'SE';
					break;
				case 'fi':
					$countryIso = 'FI';
					break;
				case 'da':
					$countryIso = 'DK';
					break;
				case 'is':
					$countryIso = 'IS';
					break;
				case 'hu':
					$countryIso = 'hu';
					break;
				case 'pl':
					$countryIso = 'PL';
					break;
				case 'lv':
					$countryIso = 'LV';
					break;
				case 'lt':
					$countryIso = 'LT';
					break;
				default:
					$countryIso = 'US';
			}
		}

		return "{$langIso}_{$countryIso}";
	}
}
