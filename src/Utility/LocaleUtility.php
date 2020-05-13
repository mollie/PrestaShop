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
 * @category   Mollie
 * @package    Mollie
 * @link       https://www.mollie.nl
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
     * Get webshop locale
     *
     * @return string
     *
     * @throws PrestaShopException
     *
     * @since 3.0.0
     */
    public static function getWebshopLocale()
    {
        // Current language
        if (Context::getContext()->language instanceof Language) {
            $language = Context::getContext()->language->iso_code;
        } else {
            $language = 'en';
        }
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