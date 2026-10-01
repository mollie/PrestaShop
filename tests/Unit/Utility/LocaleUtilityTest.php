<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 *
 * @see        https://github.com/mollie/PrestaShop
 * @codingStandardsIgnoreStart
 */

namespace Mollie\Tests\Unit\Utility;

use Configuration;
use Context;
use Language;
use Mollie\Tests\Unit\BaseTestCase;
use Mollie\Utility\LocaleUtility;

class LocaleUtilityTest extends BaseTestCase
{
    /** @var Language|null */
    private $originalLanguage;

    /** @var string|false */
    private $originalCountry;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalLanguage = Context::getContext()->language;
        $this->originalCountry = Configuration::get('PS_LOCALE_COUNTRY');
    }

    protected function tearDown(): void
    {
        Context::getContext()->language = $this->originalLanguage;
        Configuration::set('PS_LOCALE_COUNTRY', $this->originalCountry);

        parent::tearDown();
    }

    /**
     * @dataProvider provideShopLanguages
     */
    public function testGetWebShopLocale($languageIso, $shopCountryIso, $expected)
    {
        $language = new Language();
        $language->iso_code = $languageIso;
        Context::getContext()->language = $language;
        Configuration::set('PS_LOCALE_COUNTRY', $shopCountryIso);

        $this->assertSame($expected, LocaleUtility::getWebShopLocale());
    }

    public function provideShopLanguages()
    {
        return [
            'norwegian pack in a norwegian shop' => ['no', 'no', 'nb_NO'],
            'norwegian pack in a foreign shop' => ['no', 'gb', 'nb_NO'],
            'nynorsk pack' => ['nn', 'no', 'nb_NO'],
            'german in austria keeps the country' => ['de', 'at', 'de_AT'],
            'german in a foreign shop' => ['de', 'gb', 'de_DE'],
            'chinese is not accepted by mollie' => ['zh', 'cn', 'en_US'],
            'serbian is not accepted by mollie' => ['sr', 'cs', 'en_US'],
        ];
    }

    /**
     * @dataProvider provideApplePayLanguages
     */
    public function testGetApplePayLocale($languageIso, $languageLocale, $expected)
    {
        $language = new Language();
        $language->iso_code = $languageIso;
        $language->locale = $languageLocale;
        Context::getContext()->language = $language;

        $this->assertSame($expected, LocaleUtility::getApplePayLocale());
    }

    public function provideApplePayLanguages()
    {
        return [
            'norwegian pack' => ['no', 'no-NO', 'nb-NO'],
            'nynorsk pack' => ['nn', 'nn-NO', 'nb-NO'],
            'czech keeps the shop locale' => ['cs', 'cs-CZ', 'cs-CZ'],
            'english keeps the shop locale' => ['en', 'en-US', 'en-US'],
        ];
    }
}
