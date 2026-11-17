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

    const EXPRESS_PLACEMENT_SOURCES = [
        'Show Apple Pay, Google Pay and PayPal as quick-pay buttons, so customers can pay in one tap. Choose where they show below, and which buttons appear on the Payment Methods page.',
        'Cart page',
        'Top of checkout',
        'Payment step',
        'Here the wallet collects the address and shipping choice, so the shopper skips the checkout steps.',
    ];

    /**
     * @dataProvider provideCatalogs
     */
    public function testOnboardingSwitchStringsAreTranslated(string $locale, array $catalog): void
    {
        $this->assertTranslated($locale, $catalog, self::SOURCES);
    }

    /**
     * @dataProvider provideCatalogs
     */
    public function testExpressPlacementStringsAreTranslated(string $locale, array $catalog): void
    {
        $this->assertTranslated($locale, $catalog, self::EXPRESS_PLACEMENT_SOURCES);
    }

    private function assertTranslated(string $locale, array $catalog, array $sources): void
    {
        foreach ($sources as $source) {
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
