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

use Mollie\Config\Config;
use Mollie\Utility\ExpressPaymentUtility;
use PHPUnit\Framework\TestCase;

class ExpressPaymentUtilityTest extends TestCase
{
    /**
     * @dataProvider paymentProvider
     */
    public function testItRecognisesExpressPaymentsByTheirMetadataFlag($apiPayment, bool $expected): void
    {
        $this->assertSame($expected, ExpressPaymentUtility::isExpressPayment($apiPayment));
    }

    public function paymentProvider(): array
    {
        return [
            'object metadata with flag' => [(object) ['metadata' => (object) [Config::EXPRESS_METADATA_FLAG => 1]], true],
            'array metadata with flag' => [(object) ['metadata' => [Config::EXPRESS_METADATA_FLAG => '1']], true],
            'flag set to zero' => [(object) ['metadata' => (object) [Config::EXPRESS_METADATA_FLAG => 0]], false],
            'regular payment metadata' => [(object) ['metadata' => (object) ['cart_id' => 7, 'secure_key' => 'x']], false],
            'null metadata' => [(object) ['metadata' => null], false],
            'no metadata property' => [(object) ['id' => 'tr_1'], false],
            'not an object' => [null, false],
        ];
    }
}
