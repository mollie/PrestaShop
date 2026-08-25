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

namespace Mollie\Tests\Unit\Translation;

use PHPUnit\Framework\TestCase;

/**
 * Guards translations/*.php against drift.
 *
 * Six locale files (el, et, is, lv, ro, sk) were created from a stale string snapshot and then
 * silently skipped by every later top-up commit, leaving the back office order panel half
 * English for five months (PIPRES-813). Nothing in the build noticed. This test is that missing
 * check: it resolves every translatable string in the codebase against every locale file the
 * same way PrestaShop does at runtime, and fails when a locale gains a new gap.
 *
 * Known, accepted gaps live in translation-baseline.json. Regenerate it with
 * `php tests/Unit/Translation/regenerate-baseline.php` and commit the result whenever you
 * deliberately change what is outstanding.
 */
class TranslationCompletenessTest extends TestCase
{
    /** @var TranslationScanner */
    private $scanner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->scanner = new TranslationScanner(realpath(__DIR__ . '/../../..'));
    }

    public function testNoNewUntranslatedStrings(): void
    {
        $baseline = $this->baseline();

        $regressions = [];
        foreach ($this->scanner->missingByLocale() as $locale => $missing) {
            $accepted = isset($baseline[$locale]) ? $baseline[$locale] : [];
            $new = array_values(array_diff($missing, $accepted));
            if ([] !== $new) {
                $regressions[$locale] = $new;
            }
        }

        $this->assertSame(
            [],
            $regressions,
            'New untranslated strings found. Add them to translations/<locale>.php, or if the gap '
            . "is deliberate, regenerate the baseline:\n" . self::format($regressions)
        );
    }

    /**
     * Keeps the accepted-gap list shrinking. Once a locale is filled in, its baseline entries have
     * to go, otherwise the list silently stops describing reality and stops catching regressions.
     */
    public function testBaselineHasNoStaleEntries(): void
    {
        $actual = $this->scanner->missingByLocale();

        $stale = [];
        foreach ($this->baseline() as $locale => $accepted) {
            $missing = isset($actual[$locale]) ? $actual[$locale] : [];
            $fixed = array_values(array_diff($accepted, $missing));
            if ([] !== $fixed) {
                $stale[$locale] = $fixed;
            }
        }

        $this->assertSame(
            [],
            $stale,
            'These strings are translated now but still listed as accepted gaps. Regenerate the '
            . "baseline with `php tests/Unit/Translation/regenerate-baseline.php`:\n" . self::format($stale)
        );
    }

    /**
     * Every locale must at least parse and define $_MODULE, otherwise PrestaShop silently serves
     * the source string and the shop looks untranslated for no visible reason.
     */
    public function testEveryLocaleFileIsLoadable(): void
    {
        $files = $this->scanner->localeFiles();
        $this->assertNotEmpty($files, 'No translation files found.');

        foreach ($files as $locale => $path) {
            $table = TranslationScanner::loadLocale($path);
            $this->assertNotEmpty($table, sprintf('translations/%s.php defines no $_MODULE entries.', $locale));
        }
    }

    private function baseline(): array
    {
        $path = __DIR__ . '/translation-baseline.json';
        $this->assertFileExists($path, 'Translation baseline is missing.');

        $decoded = json_decode((string) file_get_contents($path), true);
        $this->assertTrue(is_array($decoded), 'Translation baseline is not valid JSON.');

        return $decoded;
    }

    private static function format(array $byLocale): string
    {
        $out = '';
        foreach ($byLocale as $locale => $entries) {
            $out .= sprintf("  [%s] %d entry(ies)\n", $locale, count($entries));
            foreach (array_slice($entries, 0, 10) as $entry) {
                $out .= '      ' . $entry . "\n";
            }
            if (count($entries) > 10) {
                $out .= sprintf("      ... and %d more\n", count($entries) - 10);
            }
        }

        return $out;
    }
}
