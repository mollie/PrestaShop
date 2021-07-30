<?php

namespace Validator;

use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Config\Config;
use Mollie\Service\VoucherService;
use Mollie\Validator\VoucherValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class VoucherValidatorTest extends TestCase
{
    /**
     * @dataProvider validateDataProvider
     *
     * @param $configurationMocks
     * @param $voucherServiceMocks
     * @param $result
     */
    public function testValidate(array $products, $configurationMocks, $voucherServiceMocks, $result)
    {
        /** @var MockObject $configurationAdapter */
        $configurationAdapter = $this->getMockBuilder(ConfigurationAdapter::class)->getMock();
        foreach ($configurationMocks as $mock) {
            $configurationAdapter->expects(self::at($mock['at']))->method($mock['function'])->with($mock['expects'])->willReturn($mock['return']);
        }

        /** @var MockObject $voucherService */
        $voucherService = $this->getMockBuilder(VoucherService::class)->disableOriginalConstructor()->getMock();
        foreach ($voucherServiceMocks as $mock) {
            $voucherService->expects(self::at($mock['at']))->method($mock['function'])->willReturn($mock['return']);
        }

        $voucherValidator = new VoucherValidator($configurationAdapter, $voucherService);
        $hasVoucherProducts = $voucherValidator->validate($products);

        self::assertEquals($result, $hasVoucherProducts);
    }

    public function validateDataProvider()
    {
        return [
            'default meal category' => [
                'products' => [
                    'features' => [
                        'id_feature' => 1,
                        'id_feature_value' => 1,
                    ],
                ],
                'configurationMocks' => [
                    0 => [
                        'function' => 'get',
                        'expects' => Config::MOLLIE_VOUCHER_CATEGORY,
                        'return' => Config::MOLLIE_VOUCHER_CATEGORY_MEAL,
                        'at' => 0,
                    ],
                ],
                'voucherServiceMocks' => [],
                'result' => true,
            ],
            'null category with feature' => [
                'products' => [
                    'features' => [
                        'id_feature' => 1,
                        'id_feature_value' => 1,
                    ],
                ],
                'configurationMocks' => [
                    0 => [
                        'function' => 'get',
                        'expects' => Config::MOLLIE_VOUCHER_CATEGORY,
                        'return' => Config::MOLLIE_VOUCHER_CATEGORY_NULL,
                        'at' => 0,
                    ],
                ],
                'voucherServiceMocks' => [
                    0 => [
                        'function' => 'getProductCategory',
                        'return' => true,
                        'at' => 0,
                    ],
                ],
                'result' => true,
            ],
            'null category without feature' => [
                'products' => [
                    'features' => [
                        'id_feature' => 1,
                        'id_feature_value' => 1,
                    ],
                ],
                'configurationMocks' => [
                    0 => [
                        'function' => 'get',
                        'expects' => Config::MOLLIE_VOUCHER_CATEGORY,
                        'return' => Config::MOLLIE_VOUCHER_CATEGORY_NULL,
                        'at' => 0,
                    ],
                ],
                'voucherServiceMocks' => [
                    0 => [
                        'function' => 'getProductCategory',
                        'return' => false,
                        'at' => 0,
                    ],
                ],
                'result' => false,
            ],
        ];
    }
}
