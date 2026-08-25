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

use Mollie\Tests\Unit\BaseTestCase;
use Mollie\Utility\TabTranslationUtility;

class TabTranslationUtilityTest extends BaseTestCase
{
    /** @var string */
    private $translationsDir = __DIR__ . '/../../../translations';

    public function testReturnsTranslationFromTheLanguagesOwnFile()
    {
        $this->assertSame(
            'API-configuratie',
            TabTranslationUtility::translate($this->translationsDir, 'mollie', 'API Configuration', 'nl')
        );

        $this->assertSame(
            'API-Konfiguration',
            TabTranslationUtility::translate($this->translationsDir, 'mollie', 'API Configuration', 'de')
        );
    }

    public function testLanguageWithoutTranslationFileFallsBackToEnglishEvenAfterAnotherLanguageWasLoaded()
    {
        TabTranslationUtility::translate($this->translationsDir, 'mollie', 'API Configuration', 'nl');

        $this->assertSame(
            'API Configuration',
            TabTranslationUtility::translate($this->translationsDir, 'mollie', 'API Configuration', 'gb')
        );
    }

    public function testMissingKeyFallsBackToEnglish()
    {
        $this->assertSame(
            'A string the module never shipped',
            TabTranslationUtility::translate($this->translationsDir, 'mollie', 'A string the module never shipped', 'nl')
        );
    }

    public function testGlobalModuleArrayIsRestoredAfterLoadingATranslationFile()
    {
        global $_MODULE;
        $_MODULE = ['sentinel' => 'untouched'];

        TabTranslationUtility::translate($this->translationsDir, 'mollie', 'Payment Methods', 'fr');

        $this->assertSame(['sentinel' => 'untouched'], $_MODULE);
    }
}
