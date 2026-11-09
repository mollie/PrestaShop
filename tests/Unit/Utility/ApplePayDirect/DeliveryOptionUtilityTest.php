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

use Mollie\Utility\ApplePayDirect\DeliveryOptionUtility;
use PHPUnit\Framework\TestCase;

class DeliveryOptionUtilityTest extends TestCase
{
    /**
     * @dataProvider filterUsableCarriersDataProvider
     */
    public function testFilterUsableCarriers(array $carriers, array $deliveryOptionList, int $idAddress, array $expected)
    {
        $this->assertSame($expected, DeliveryOptionUtility::filterUsableCarriers($carriers, $deliveryOptionList, $idAddress));
    }

    public function filterUsableCarriersDataProvider(): array
    {
        $two = ['id_carrier' => '2'];
        $five = ['id_carrier' => '5'];
        $seven = ['id_carrier' => '7'];

        return [
            'keeps an offered carrier and drops one that is not offered' => [
                'carriers' => [$two, $five],
                'deliveryOptionList' => [10 => ['2,' => []]],
                'idAddress' => 10,
                'expected' => [$two],
            ],
            'drops a carrier that only appears inside a multi-carrier option' => [
                'carriers' => [$two, $five],
                'deliveryOptionList' => [10 => ['2,' => [], '2,5,' => []]],
                'idAddress' => 10,
                'expected' => [$two],
            ],
            'drops a carrier whose only option spans several packages' => [
                'carriers' => [$two],
                'deliveryOptionList' => [10 => ['2,2,' => []]],
                'idAddress' => 10,
                'expected' => [],
            ],
            'options for another address give no carriers' => [
                'carriers' => [$two],
                'deliveryOptionList' => [11 => ['2,' => []]],
                'idAddress' => 10,
                'expected' => [],
            ],
            'keeps row order and re-indexes when a middle row is dropped' => [
                'carriers' => [$seven, $five, $two],
                'deliveryOptionList' => [10 => ['2,' => [], '7,' => []]],
                'idAddress' => 10,
                'expected' => [$seven, $two],
            ],
        ];
    }
}
