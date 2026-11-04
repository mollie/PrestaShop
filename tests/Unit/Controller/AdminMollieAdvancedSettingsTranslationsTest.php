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

namespace Mollie\Tests\Unit\Controller;

use PHPUnit\Framework\TestCase;
use RuntimeException;

class AdminMollieAdvancedSettingsTranslationsTest extends TestCase
{
    const SOURCES = [
        'PrestaShop Account and CloudSync',
        'Show PrestaShop Account and CloudSync',
        'Shows the PrestaShop Account and CloudSync setup on the API configuration page.',
    ];

    /**
     * @dataProvider provideCatalogs
     */
    public function testOnboardingSwitchStringsAreTranslated(string $locale, array $catalog): void
    {
        foreach (self::SOURCES as $source) {
            $key = '<{mollie}prestashop>adminmollieadvancedsettingscontroller_' . md5($source);

            $this->assertArrayHasKey($key, $catalog, sprintf('%s.php is missing "%s".', $locale, $source));
            $this->assertNotSame('', trim($catalog[$key]), sprintf('%s.php has an empty value for "%s".', $locale, $source));

            if ('en' === $locale) {
                continue;
            }

            $this->assertNotSame($source, $catalog[$key], sprintf('%s.php still holds the English source for "%s".', $locale, $source));
        }
    }

    public function provideCatalogs(): array
    {
        $cases = [];

        foreach (glob(dirname(dirname(dirname(__DIR__))) . '/translations/*.php') as $file) {
            $locale = basename($file, '.php');

            if ('index' === $locale) {
                continue;
            }

            $_MODULE = [];
            include $file;

            $cases[$locale] = [$locale, $_MODULE];
        }

        if (count($cases) < 2) {
            throw new RuntimeException('No translation catalogs were found, the glob is broken.');
        }

        return $cases;
    }
}
