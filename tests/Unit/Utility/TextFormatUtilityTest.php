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

use Mollie\Utility\TextFormatUtility;
use PHPUnit\Framework\TestCase;

class TextFormatUtilityTest extends TestCase
{
    /**
     * @dataProvider formatNumberDataProvider
     *
     * @param $unitPrice
     * @param $apiRoundingPrecision
     * @param $docPoint
     * @param $thousandSep
     * @param $result
     */
    public function testFormatNumber($unitPrice, $apiRoundingPrecision, $docPoint, $thousandSep, $result)
    {
        $formatted = TextFormatUtility::formatNumber($unitPrice, $apiRoundingPrecision, $docPoint, $thousandSep);

        $this->assertSame($result, $formatted);
    }

    /**
     * A cart product with no customisation has a null id_customization, so the
     * product line builder hands null straight to formatNumber(). PHP 8.1
     * deprecates passing null to number_format() and PHP 9 makes it a
     * TypeError, which would break the payment step, so the null must never
     * reach number_format().
     */
    public function testFormatNumberEmitsNoDeprecationForNull()
    {
        $deprecations = [];

        set_error_handler(function ($errno, $errstr) use (&$deprecations) {
            $deprecations[] = $errstr;

            return true;
        }, E_DEPRECATED);

        try {
            $formatted = TextFormatUtility::formatNumber(null, 0);
        } finally {
            restore_error_handler();
        }

        $this->assertSame([], $deprecations);
        $this->assertSame('0', $formatted);
    }

    public function formatNumberDataProvider()
    {
        return [
            'null price formats as zero' => [
                    'unitPrice' => null,
                    'apiRoundingPrecision' => 0,
                    'docPoint' => '.',
                    'thousandSep' => '',
                    'result' => '0',
                ],
            'rounds to the requested precision' => [
                    'unitPrice' => 1234.5678,
                    'apiRoundingPrecision' => 2,
                    'docPoint' => '.',
                    'thousandSep' => '',
                    'result' => '1234.57',
                ],
            'zero precision rounds to whole units' => [
                    'unitPrice' => 12.7,
                    'apiRoundingPrecision' => 0,
                    'docPoint' => '.',
                    'thousandSep' => '',
                    'result' => '13',
                ],
            'numeric string is accepted' => [
                    'unitPrice' => '5',
                    'apiRoundingPrecision' => 0,
                    'docPoint' => '.',
                    'thousandSep' => '',
                    'result' => '5',
                ],
            'honours separators' => [
                    'unitPrice' => 1234.5,
                    'apiRoundingPrecision' => 2,
                    'docPoint' => ',',
                    'thousandSep' => '.',
                    'result' => '1.234,50',
                ],
        ];
    }
}
