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

namespace Builder;

use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Builder\ApplePayDirect\ApplePayCarriersBuilder;
use Mollie\Config\Config;
use PHPUnit\Framework\TestCase;

class ApplePayCarriersBuilderTest extends TestCase
{
    public function testBuildFiltersOutExcludedCarrierReferences()
    {
        $builder = new ApplePayCarriersBuilder($this->mockConfiguration('[10, 20]'));

        $result = $builder->build(
            [
                ['id_carrier' => 5, 'id_reference' => 10, 'name' => 'Relay carrier', 'delay' => '1-2 days'],
                ['id_carrier' => 6, 'id_reference' => 20, 'name' => 'Another relay', 'delay' => '2-3 days'],
            ],
            1
        );

        $this->assertSame([], $result);
    }

    public function testBuildKeepsCarriersWhenNothingIsExcluded()
    {
        $builder = new ApplePayCarriersBuilder($this->mockConfiguration(null));

        $result = $builder->build(
            [
                ['id_carrier' => 1, 'id_reference' => 1, 'name' => 'Default carrier', 'delay' => '1-2 days'],
            ],
            1
        );

        $this->assertCount(1, $result);
    }

    /**
     * @dataProvider getExcludedCarrierConfigData
     */
    public function testGetExcludedCarrierReferences($configValue, array $expected)
    {
        $builder = new ApplePayCarriersBuilder($this->mockConfiguration($configValue));

        $this->assertSame($expected, $builder->getExcludedCarrierReferences());
    }

    public function getExcludedCarrierConfigData()
    {
        return [
            'valid json array' => ['[1, 2, 3]', [1, 2, 3]],
            'string values are cast to int' => ['["4", "5"]', [4, 5]],
            'empty config' => [null, []],
            'empty json array' => ['[]', []],
            'invalid json' => ['not-json', []],
            'scalar json instead of array' => ['5', []],
        ];
    }

    private function mockConfiguration($excludedCarriersValue)
    {
        $configuration = $this->getMockBuilder(ConfigurationAdapter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $configuration->method('get')
            ->with(Config::MOLLIE_APPLE_PAY_DIRECT_EXCLUDED_CARRIERS)
            ->willReturn($excludedCarriersValue);

        return $configuration;
    }
}
