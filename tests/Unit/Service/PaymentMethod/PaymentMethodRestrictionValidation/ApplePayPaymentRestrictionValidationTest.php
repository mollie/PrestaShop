<?php

use Mollie\Config\Config;
use Mollie\Service\PaymentMethod\PaymentMethodRestrictionValidation\ApplePayPaymentMethodRestrictionValidator;
use Mollie\Tests\Unit\Tools\UnitTestCase;

class ApplePayPaymentRestrictionValidationTest extends UnitTestCase
{
    /**
     * @dataProvider getApplePayPaymentRestrictionValidationDataProvider
     */
    public function testIsValid($isApple, $configurationAdapter, $expectedResult)
    {
        $_COOKIE['isApplePayMethod'] = $isApple;

        $applePayValidation = new ApplePayPaymentMethodRestrictionValidator(
            $configurationAdapter
        );

        $isValid = $applePayValidation->isValid(
            $this->mockPaymentMethod(Config::MOLLIE_METHOD_ID_APPLE_PAY, true)
        );

        $this->assertEquals($expectedResult, $isValid);
    }

    public function getApplePayPaymentRestrictionValidationDataProvider()
    {
        return [
            'All checks pass' => [
                'isApple' => 1,
                'configurationAdapter' => $this->mockConfigurationAdapter([
                    'PS_SSL_ENABLED_EVERYWHERE' => true,
                ]),
                'expectedResult' => true,
            ],
            'SSL is not enabled' => [
                'isApple' => 1,
                'configurationAdapter' => $this->mockConfigurationAdapter([
                    'PS_SSL_ENABLED_EVERYWHERE' => false,
                ]),
                'expectedResult' => false,
            ],
            'Cookie has no data if Apple Pay is available' => [
                'isApple' => 0,
                'configurationAdapter' => $this->mockConfigurationAdapter([
                    'PS_SSL_ENABLED_EVERYWHERE' => true,
                ]),
                'expectedResult' => false,
            ],
        ];
    }

    /**
     * @dataProvider getApplePayPaymentRestrictionSupportedDataProvider
     */
    public function testIsSupported($paymentName, $expectedResult)
    {
        $applePayValidation = new ApplePayPaymentMethodRestrictionValidator(
            $this->mockConfigurationAdapter([
                'PS_SSL_ENABLED_EVERYWHERE' => true,
            ])
        );

        $this->assertEquals($expectedResult, $applePayValidation->supports($paymentName));
    }

    public function getApplePayPaymentRestrictionSupportedDataProvider()
    {
        return [
            'Supported' => [
                'paymentMethod' => $this->mockPaymentMethod(Config::MOLLIE_METHOD_ID_APPLE_PAY, true),
                'expectedResult' => true,
            ],
            'Not supported' => [
                'paymentName' => $this->mockPaymentMethod(Config::MOLLIE_METHOD_ID_KLARNA_PAY_LATER, true),
                'expectedResult' => false,
            ],
        ];
    }
}
