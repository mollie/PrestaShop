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
 *
 * Rewrites translation-baseline.json from the current state of translations/*.php.
 *
 * Run it only when you deliberately change what is outstanding: after filling in a locale, or
 * after adding a string you knowingly ship untranslated. Never run it to silence a failing
 * build, that is the one case the test exists for.
 *
 * Usage: php tests/Unit/Translation/regenerate-baseline.php
 */
require_once __DIR__ . '/TranslationScanner.php';

use Mollie\Tests\Unit\Translation\TranslationScanner;

$scanner = new TranslationScanner(realpath(__DIR__ . '/../../..'));

$missing = array_filter($scanner->missingByLocale(), function (array $entries) {
    return [] !== $entries;
});

$target = __DIR__ . '/translation-baseline.json';
file_put_contents($target, json_encode($missing, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n");

echo 'Wrote ' . $target . PHP_EOL;
foreach ($missing as $locale => $entries) {
    printf('  %-4s %d accepted gap(s)%s', $locale, count($entries), PHP_EOL);
}
