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

use Mollie\Utility\PostcodeUtility;
use PHPUnit\Framework\TestCase;

class PostcodeUtilityTest extends TestCase
{
    /**
     * @dataProvider candidateProvider
     */
    public function testItOffersThePostcodeWithAndWithoutTheCountryPrefix(string $postcode, string $iso, array $expected): void
    {
        $this->assertSame($expected, PostcodeUtility::getCandidates($postcode, $iso));
    }

    public function candidateProvider(): array
    {
        return [
            'Apple Pay adds the country code' => ['LT-47412', 'LT', ['LT-47412', '47412']],
            'prefix with a space, lower case' => ['lt 47412', 'LT', ['LT 47412', '47412', 'LT-47412']],
            'shop format expects the prefix' => ['1050', 'LV', ['1050', 'LV-1050']],
            'plain postcode' => ['1015 CW', 'NL', ['1015 CW', 'NL-1015 CW']],
            'empty' => ['', 'LT', ['']],
        ];
    }

    /**
     * @dataProvider validProvider
     */
    public function testItReturnsTheFirstFormThePostcodeFormatAccepts(string $format, string $iso, string $postcode, ?string $expected): void
    {
        $country = new \Country();
        $country->zip_code_format = $format;
        $country->iso_code = $iso;

        $this->assertSame($expected, PostcodeUtility::findValid($country, $postcode, $iso));
    }

    public function validProvider(): array
    {
        return [
            'prefix removed for Lithuania' => ['NNNNN', 'LT', 'LT-47412', '47412'],
            'prefix added for Latvia' => ['C-NNNN', 'LV', '1050', 'LV-1050'],
            'Dutch postcode as sent' => ['NNNN LL', 'NL', '1015 CW', '1015 CW'],
            'too short for the format' => ['NNNNN', 'LT', '4741', null],
            'letters where digits belong' => ['NNNNN', 'LT', 'ABCDE', null],
            'no format accepts anything' => ['', 'GB', 'SW1A 1AA', 'SW1A 1AA'],
        ];
    }
}
