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

/**
 * The payment overview is the module's only screen that is pure text: every cell is a label, a
 * status badge or a failure reason. A gap in one catalog shows the merchant raw English in the
 * middle of a translated back office, which is the defect PIPRES-810 was raised for.
 *
 * The expected strings are read out of the controller and the templates rather than listed here,
 * so adding an untranslated l() call to either fails this test instead of shipping quietly.
 */
class AdminMolliePaymentOverviewTranslationsTest extends TestCase
{
    /**
     * Terms that are the same word in English and in the target language. Anything outside this
     * list that comes back byte identical to the English source is an untranslated copy paste.
     */
    const ACCEPTED_COGNATES = ['Status', 'Date', 'Transaction', 'Order'];

    /**
     * @dataProvider provideCatalogs
     */
    public function testEveryPaymentOverviewStringIsTranslated(string $locale, array $catalog): void
    {
        $untranslated = [];
        $checked = 0;

        foreach ($this->expectedStrings() as $scope => $strings) {
            foreach ($strings as $source) {
                $key = '<{mollie}prestashop>' . $scope . '_' . md5($source);

                $this->assertArrayHasKey($key, $catalog, sprintf('%s.php is missing "%s" (%s).', $locale, $source, $scope));
                $this->assertNotSame('', trim($catalog[$key]), sprintf('%s.php has an empty value for "%s".', $locale, $source));
                ++$checked;

                if ('en' !== $locale && $catalog[$key] === $source && !in_array($source, self::ACCEPTED_COGNATES, true)) {
                    $untranslated[] = $source;
                }
            }
        }

        $this->assertGreaterThan(1, $checked, 'No strings were collected from the controller or the templates.');
        $this->assertSame([], $untranslated, sprintf('%s.php still holds the English source for: %s', $locale, implode(', ', $untranslated)));
    }

    /**
     * The grace period is interpolated into this sentence, so a catalog that drops the placeholder
     * renders "after hours" with no number.
     *
     * @dataProvider provideCatalogs
     */
    public function testTheGracePeriodPlaceholderSurvivesTranslation(string $locale, array $catalog): void
    {
        $key = '<{mollie}prestashop>payment_overview_intro_' . md5('Attempts still waiting after %d hours are shown as abandoned.');

        $this->assertSame(1, substr_count($catalog[$key], '%d'), sprintf('%s.php lost the %%d placeholder.', $locale));
    }

    public function provideCatalogs(): array
    {
        $cases = [];

        foreach (glob($this->moduleDirectory() . '/translations/*.php') as $file) {
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

    private function expectedStrings(): array
    {
        $controller = $this->moduleDirectory() . '/controllers/admin/AdminMolliePaymentOverviewController.php';

        // The tab name resolves against the module name rather than the controller, because
        // Mollie::getTabTranslations() passes 'mollie' as the source.
        $strings = ['mollie' => ['Payment overview']];
        $strings['adminmolliepaymentoverviewcontroller'] = $this->extract("/->l\(\s*'((?:[^'\\\\]|\\\\.)*)'/", $controller);

        foreach (glob($this->moduleDirectory() . '/views/templates/admin/payment-overview/*.tpl') as $template) {
            $found = $this->extract("/\{l\s+s='((?:[^'\\\\]|\\\\.)*)'/", $template);

            if ($found) {
                $strings[basename($template, '.tpl')] = $found;
            }
        }

        return $strings;
    }

    private function extract(string $pattern, string $file): array
    {
        preg_match_all($pattern, (string) file_get_contents($file), $matches);

        return array_values(array_unique(array_map('stripslashes', $matches[1])));
    }

    private function moduleDirectory(): string
    {
        return dirname(dirname(dirname(__DIR__)));
    }
}
