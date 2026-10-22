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
 * The payment history timeline on the order page reads its labels from the payment_history
 * template and from Mollie::hookDisplayAdminOrder, so every shipped catalog needs both sets of keys.
 */
class PaymentHistoryTranslationsTest extends TestCase
{
    const MIN_LOCALIZED_RATIO = 0.8;

    const TEMPLATE_STRINGS = [
        'Payment history',
        'No events yet',
        'Mollie reference',
        '%s events',
        '1 event',
        '%s left to capture',
    ];

    const MODULE_STRINGS = [
        'Created',
        'Authorized',
        'Paid',
        'Captured',
        'Refunded',
        'Refund',
        'Chargeback',
        'Chargeback reversed',
        'Canceled',
        'Expired',
        'Failed',
        'Payment method',
        'Expires at',
        'Due date',
        'Capture before',
        'Capture mode',
        'Settlement amount',
        'Reason',
        'Open',
        'Pending',
        'Queued',
        'Processing',
        'Released',
    ];

    public function testShippedCatalogsTranslateThePaymentHistoryLabels()
    {
        $expected = [];
        foreach (self::TEMPLATE_STRINGS as $string) {
            $expected['<{mollie}prestashop>payment_history_' . md5($string)] = $string;
        }
        foreach (self::MODULE_STRINGS as $string) {
            $expected['<{mollie}prestashop>mollie_' . md5($string)] = $string;
        }

        $catalogs = glob(dirname(__DIR__, 3) . '/translations/*.php');
        $this->assertNotEmpty($catalogs, 'No shipped translation catalogs were found.');

        $checked = 0;
        foreach ($catalogs as $catalog) {
            $iso = basename($catalog, '.php');
            if ('index' === $iso) {
                continue;
            }

            ++$checked;
            $_MODULE = [];
            include $catalog;

            $localized = 0;
            foreach ($expected as $key => $english) {
                $this->assertArrayHasKey($key, $_MODULE, sprintf('Missing "%s" in %s.php', $english, $iso));
                $this->assertNotSame('', trim((string) $_MODULE[$key]), sprintf('Empty "%s" in %s.php', $english, $iso));
                if ($english !== $_MODULE[$key]) {
                    ++$localized;
                }
            }

            if ('en' === $iso) {
                $this->assertSame(0, $localized, 'en.php must hold the English source strings.');
                continue;
            }

            $this->assertGreaterThanOrEqual(
                self::MIN_LOCALIZED_RATIO,
                $localized / count($expected),
                sprintf('Payment history labels in %s.php are mostly left in English', $iso)
            );
        }

        $this->assertGreaterThan(1, $checked, 'Expected the shipped locale catalogs to be checked.');
    }
}
