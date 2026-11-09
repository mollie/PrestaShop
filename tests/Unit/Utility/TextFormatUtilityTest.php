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
     */
    public function testFormatNumber($unitPrice, $apiRoundingPrecision, $docPoint, $thousandSep, $result)
    {
        $formatted = TextFormatUtility::formatNumber($unitPrice, $apiRoundingPrecision, $docPoint, $thousandSep);

        $this->assertSame($result, $formatted);
    }

    /**
     * A cart product with no customisation has a null id_customization, which the
     * product line builder passes straight to formatNumber(): PHP 8.1 deprecates
     * that and PHP 9 makes it a TypeError.
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

    /**
     * @dataProvider urlDataProvider
     */
    public function testEncodeUrl(string $url, string $expected)
    {
        $result = TextFormatUtility::encodeUrl($url);

        $this->assertSame($expected, $result);
        $this->assertNotFalse(filter_var($result, FILTER_VALIDATE_URL));
    }

    public function testEncodeUrlTurnsInternationalHostIntoPunycode()
    {
        if (!function_exists('idn_to_ascii')) {
            $this->markTestSkipped('Punycode needs the intl extension');
        }

        $result = TextFormatUtility::encodeUrl('https://möbel-shop.de/5-stühle.html');

        $this->assertSame('https://xn--mbel-shop-07a.de/5-st%C3%BChle.html', $result);
        $this->assertNotFalse(filter_var($result, FILTER_VALIDATE_URL));
    }

    public function testEncodeUrlWithoutSchemeEncodesEverything()
    {
        $this->assertSame('/2/cleaner-n%C2%B053.jpg', TextFormatUtility::encodeUrl('/2/cleaner-n°53.jpg'));
    }

    public function urlDataProvider()
    {
        return [
            'plain url is unchanged' => [
                'url' => 'https://shop.test/2/hummingbird-printed-t-shirt.jpg',
                'expected' => 'https://shop.test/2/hummingbird-printed-t-shirt.jpg',
            ],
            'accented letters are percent-encoded' => [
                'url' => 'https://shop.test/5-café-crème.html',
                'expected' => 'https://shop.test/5-caf%C3%A9-cr%C3%A8me.html',
            ],
            'degree sign is percent-encoded' => [
                'url' => 'https://shop.test/2/cleaner-n°53.jpg',
                'expected' => 'https://shop.test/2/cleaner-n%C2%B053.jpg',
            ],
            'ordinal indicator is percent-encoded' => [
                'url' => 'https://shop.test/2/edge-rounded-180º.jpg',
                'expected' => 'https://shop.test/2/edge-rounded-180%C2%BA.jpg',
            ],
            'chinese is percent-encoded' => [
                'url' => 'https://shop.test/3-去除水泥.html',
                'expected' => 'https://shop.test/3-%E5%8E%BB%E9%99%A4%E6%B0%B4%E6%B3%A5.html',
            ],
            'arabic is percent-encoded' => [
                'url' => 'https://shop.test/4/منظف.jpg',
                'expected' => 'https://shop.test/4/%D9%85%D9%86%D8%B8%D9%81.jpg',
            ],
            'symbols without ascii form are percent-encoded' => [
                'url' => 'https://shop.test/2/price-€5-nⁿ.jpg',
                'expected' => 'https://shop.test/2/price-%E2%82%AC5-n%E2%81%BF.jpg',
            ],
            'emoji and modifier letters are percent-encoded' => [
                'url' => 'https://shop.test/2/ʿarab-😀.jpg',
                'expected' => 'https://shop.test/2/%CA%BFarab-%F0%9F%98%80.jpg',
            ],
            'ascii symbols that are not url safe are percent-encoded' => [
                'url' => 'https://shop.test/2/a|b<c>^d`e f.jpg',
                'expected' => 'https://shop.test/2/a%7Cb%3Cc%3E%5Ed%60e%20f.jpg',
            ],
            'brackets are percent-encoded' => [
                'url' => 'https://shop.test/2/a[1].html?q[color]=red',
                'expected' => 'https://shop.test/2/a%5B1%5D.html?q%5Bcolor%5D=red',
            ],
            'percent sign without escape is percent-encoded' => [
                'url' => 'https://shop.test/2/100%-cotton.jpg?off=5%',
                'expected' => 'https://shop.test/2/100%25-cotton.jpg?off=5%25',
            ],
            'host with port keeps the port' => [
                'url' => 'http://localhost:8992/2/cleaner-n°53.jpg',
                'expected' => 'http://localhost:8992/2/cleaner-n%C2%B053.jpg',
            ],
            'existing percent escapes are kept' => [
                'url' => 'https://shop.test/2/already%20encoded.jpg?x=1&y=2#top',
                'expected' => 'https://shop.test/2/already%20encoded.jpg?x=1&y=2#top',
            ],
        ];
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
