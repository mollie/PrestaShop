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

namespace Translations;

use PHPUnit\Framework\TestCase;

/**
 * The capture confirmation on the order page names the typed amount when it is below the
 * capturable amount, so every catalog that translates the full capture prompt needs the
 * partial prompts too, with all amount placeholders kept.
 */
class CaptureConfirmTranslationsTest extends TestCase
{
    const FULL_CAPTURE = 'Are you sure you want to capture the full order amount?';

    const PARTIAL_CAPTURE_STRINGS = [
        'Are you sure you want to capture %1$s of %2$s?' => ['%1$s', '%2$s'],
        'Are you sure you want to capture %1$s of %2$s? The remaining %3$s will be released and can no longer be captured.' => ['%1$s', '%2$s', '%3$s'],
    ];

    public function testCatalogsWithTheFullCapturePromptTranslateThePartialPrompts()
    {
        $fullKey = '<{mollie}prestashop>mollie_' . md5(self::FULL_CAPTURE);
        $checked = 0;

        foreach (glob(dirname(__DIR__, 3) . '/translations/*.php') as $catalog) {
            $iso = basename($catalog, '.php');
            if ('index' === $iso) {
                continue;
            }

            $_MODULE = [];
            include $catalog;

            if (!isset($_MODULE[$fullKey])) {
                continue;
            }

            ++$checked;
            foreach (self::PARTIAL_CAPTURE_STRINGS as $english => $placeholders) {
                $key = '<{mollie}prestashop>mollie_' . md5($english);
                $this->assertArrayHasKey($key, $_MODULE, sprintf('Missing "%s" in %s.php', $english, $iso));

                foreach ($placeholders as $placeholder) {
                    $this->assertStringContainsString($placeholder, $_MODULE[$key], sprintf('%s lost in %s.php', $placeholder, $iso));
                }

                if ('en' === $iso) {
                    $this->assertSame($english, $_MODULE[$key], 'en.php must hold the English source strings.');
                    continue;
                }

                $this->assertNotSame($english, $_MODULE[$key], sprintf('"%s" left in English in %s.php', $english, $iso));
            }
        }

        $this->assertGreaterThan(1, $checked, 'Expected the shipped locale catalogs to be checked.');
    }
}
