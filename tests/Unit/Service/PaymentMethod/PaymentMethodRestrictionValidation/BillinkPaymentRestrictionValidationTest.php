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
use Mollie\Service\PaymentMethod\PaymentMethodRestrictionValidation\BillinkPaymentMethodRestrictionValidator;
use Mollie\Tests\Unit\Tools\UnitTestCase;

class BillinkPaymentRestrictionValidationTest extends UnitTestCase
{
    /**
     * @var MolPaymentMethod|PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentMethod;

    protected function setUp()
    {
        parent::setUp();

        $this->paymentMethod = $this->mockPaymentMethod(Config::BILLINK, true);
    }

    /**
     * @dataProvider getBillinkPaymentRestrictionValidationDataProvider
     */
    public function testIsValid(float $totalOrderAmount, string $currencyIso, string $company, bool $expectedResult)
    {
        $context = $this->mockContext('NL', $currencyIso);

        $context
            ->method('getCart')
            ->willReturn($this->mockCart($totalOrderAmount))
        ;

        $context
            ->method('getInvoiceCompany')
            ->willReturn($company)
        ;

        $validator = new BillinkPaymentMethodRestrictionValidator($context);

        $this->assertEquals($expectedResult, $validator->isValid($this->paymentMethod));
    }

    public function getBillinkPaymentRestrictionValidationDataProvider()
    {
        return [
            'Consumer purchase within the maximum amount' => [
                'totalOrderAmount' => 100.00,
                'currencyIso' => 'EUR',
                'company' => '',
                'expectedResult' => true,
            ],
            'Consumer purchase at the maximum amount' => [
                'totalOrderAmount' => 2500.00,
                'currencyIso' => 'EUR',
                'company' => '',
                'expectedResult' => true,
            ],
            'Consumer purchase one cent above the maximum amount' => [
                'totalOrderAmount' => 2500.01,
                'currencyIso' => 'EUR',
                'company' => '',
                'expectedResult' => false,
            ],
            'Business purchase is not supported' => [
                'totalOrderAmount' => 100.00,
                'currencyIso' => 'EUR',
                'company' => 'Mollie B.V.',
                'expectedResult' => false,
            ],
            'Unsupported currency' => [
                'totalOrderAmount' => 100.00,
                'currencyIso' => 'SEK',
                'company' => '',
                'expectedResult' => false,
            ],
        ];
    }

    public function testSupportsOnlyBillink()
    {
        $validator = new BillinkPaymentMethodRestrictionValidator($this->mockContext('NL', 'EUR'));

        $this->assertTrue($validator->supports($this->paymentMethod));
        $this->assertFalse($validator->supports($this->mockPaymentMethod(Config::MOLLIE_METHOD_ID_KLARNA_PAY_LATER, true)));
    }
}
