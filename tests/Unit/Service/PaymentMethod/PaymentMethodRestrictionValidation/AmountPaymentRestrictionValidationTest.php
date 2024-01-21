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

use Mollie\Config\Config;
use Mollie\Service\PaymentMethod\PaymentMethodRestrictionValidation\AmountPaymentMethodRestrictionValidator;
use Mollie\Tests\Unit\Tools\UnitTestCase;

class AmountPaymentRestrictionValidationTest extends UnitTestCase
{
    /**
     * @var MolPaymentMethod|PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentMethod;

    protected function setUp()
    {
        parent::setUp();

        $this->paymentMethod = $this->mockPaymentMethod(Config::MOLLIE_METHOD_ID_KLARNA_PAY_LATER, true);
        $this->paymentMethod->min_amount = 10;
        $this->paymentMethod->max_amount = 100;
        $this->context = $this->mockContext('AT', 'EUR');
    }

    /**
     * @dataProvider getAmountPaymentRestrictionValidationDataProvider
     */
    public function testIsValid(float $totalOrderAmount, string $currencyIso, float $conversionRate, bool $expectedResult)
    {
        $cartMock = $this->mockCart($totalOrderAmount);
        $currencyMock = $this->mockCurrency($currencyIso, $conversionRate);

        $this->context
            ->method('getCart')
            ->willReturn($cartMock)
        ;

        $this->context
            ->method('getCurrency')
            ->willReturn($currencyMock)
        ;

        $versionSpecificValidation = new AmountPaymentMethodRestrictionValidator(
            $this->context
        );

        $isValid = $versionSpecificValidation->isValid($this->paymentMethod);

        $this->assertEquals($expectedResult, $isValid);
    }

    public function getAmountPaymentRestrictionValidationDataProvider()
    {
        return [
            'Amount is correct and currency is default' => [
                'totalOrderAmount' => 50.5,
                'currencyIso' => 'EUR',
                'conversionRate' => 1,
                'expectedResult' => true,
            ],
            'Amount is to small and currency is default' => [
                'totalOrderAmount' => 5,
                'currencyIso' => 'EUR',
                'conversionRate' => 1,
                'expectedResult' => false,
            ],
            'Amount is to big and currency is default' => [
                'totalOrderAmount' => 100.5,
                'currencyIso' => 'EUR',
                'conversionRate' => 1,
                'expectedResult' => false,
            ],
            'Amount is correct and currency is dollars' => [
                'totalOrderAmount' => 50.5,
                'currencyIso' => 'USD',
                'conversionRate' => 0.8,
                'expectedResult' => true,
            ],
            'Amount is to small and currency is dollars' => [
                'totalOrderAmount' => 5,
                'currencyIso' => 'USD',
                'conversionRate' => 0.8,
                'expectedResult' => false,
            ],
            'Amount is to big and currency is dollars' => [
                'totalOrderAmount' => 150.5,
                'currencyIso' => 'USD',
                'conversionRate' => 0.8,
                'expectedResult' => false,
            ],
            'Amount is bigger but because of currency difference its ok and currency is dollars' => [
                'totalOrderAmount' => 100.5,
                'currencyIso' => 'USD',
                'conversionRate' => 0.8,
                'expectedResult' => true,
            ],
        ];
    }
}
