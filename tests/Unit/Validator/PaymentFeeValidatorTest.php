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

namespace Mollie\Tests\Unit\Validator;

use Mollie\Config\Config;
use Mollie\DTO\PaymentFeeData;
use Mollie\Exception\InvalidPaymentFeePercentageException;
use Mollie\Exception\PaymentFeeExceedsCartAmountException;
use Mollie\Validator\PaymentFeeValidator;
use MolPaymentMethod;
use PHPUnit\Framework\TestCase;

class PaymentFeeValidatorTest extends TestCase
{
    /** @var PaymentFeeValidator */
    private $paymentFeeValidator;

    public function setUp()
    {
        parent::setUp();
        $this->paymentFeeValidator = new PaymentFeeValidator();
    }

    public function testItValidatesValidPercentageFee(): void
    {
        $paymentMethod = $this->createMock(MolPaymentMethod::class);
        $paymentMethod->surcharge = Config::FEE_PERCENTAGE;
        $paymentMethod->surcharge_percentage = -50;

        $this->paymentFeeValidator->validatePaymentFeePercentage($paymentMethod);

        self::assertTrue(true);
    }

    public function testItValidatesValidFixedAndPercentageFee(): void
    {
        $paymentMethod = $this->createMock(MolPaymentMethod::class);
        $paymentMethod->surcharge = Config::FEE_FIXED_FEE_AND_PERCENTAGE;
        $paymentMethod->surcharge_percentage = -75;

        $this->paymentFeeValidator->validatePaymentFeePercentage($paymentMethod);

        self::assertTrue(true);
    }

    public function testItThrowsExceptionForInvalidPercentageFee(): void
    {
        $paymentMethod = $this->createMock(MolPaymentMethod::class);
        $paymentMethod->surcharge = Config::FEE_PERCENTAGE;
        $paymentMethod->surcharge_percentage = -150;

        $this->expectException(InvalidPaymentFeePercentageException::class);
        $this->expectExceptionMessage('Payment fee percentage cannot be less than -99%. Current value: -150.00%');

        $this->paymentFeeValidator->validatePaymentFeePercentage($paymentMethod);
    }

    public function testItThrowsExceptionForInvalidFixedAndPercentageFee(): void
    {
        $paymentMethod = $this->createMock(MolPaymentMethod::class);
        $paymentMethod->surcharge = Config::FEE_FIXED_FEE_AND_PERCENTAGE;
        $paymentMethod->surcharge_percentage = -200;

        $this->expectException(InvalidPaymentFeePercentageException::class);
        $this->expectExceptionMessage('Payment fee percentage cannot be less than -99%. Current value: -200.00%');

        $this->paymentFeeValidator->validatePaymentFeePercentage($paymentMethod);
    }

    public function testItThrowsExceptionForExceedingUpperLimitPercentageFee(): void
    {
        $paymentMethod = $this->createMock(MolPaymentMethod::class);
        $paymentMethod->surcharge = Config::FEE_PERCENTAGE;
        $paymentMethod->surcharge_percentage = 150;

        $this->expectException(InvalidPaymentFeePercentageException::class);
        $this->expectExceptionMessage('Payment fee percentage cannot be greater than 99%. Current value: 150.00%');

        $this->paymentFeeValidator->validatePaymentFeePercentage($paymentMethod);
    }

    public function testItValidatesValidPaymentFeeAmount(): void
    {
        $paymentFeeData = $this->createMock(PaymentFeeData::class);
        $paymentFeeData->method('getPaymentFeeTaxIncl')->willReturn(-5.0);

        $this->paymentFeeValidator->validatePaymentFeeAmount($paymentFeeData, 10.0);

        self::assertTrue(true);
    }

    public function testItValidatesPositivePaymentFeeAmount(): void
    {
        $paymentFeeData = $this->createMock(PaymentFeeData::class);
        $paymentFeeData->method('getPaymentFeeTaxIncl')->willReturn(5.0);

        $this->paymentFeeValidator->validatePaymentFeeAmount($paymentFeeData, 10.0);

        self::assertTrue(true);
    }

    public function testItThrowsExceptionForPaymentFeeExceedingCartAmount(): void
    {
        $paymentFeeData = $this->createMock(PaymentFeeData::class);
        $paymentFeeData->method('getPaymentFeeTaxIncl')->willReturn(-15.0);

        $this->expectException(PaymentFeeExceedsCartAmountException::class);
        $this->expectExceptionMessage('Negative payment fee amount (15.00) cannot exceed cart amount (10.00)');

        $this->paymentFeeValidator->validatePaymentFeeAmount($paymentFeeData, 10.0);
    }

    public function testItValidatesZeroPaymentFeeAmount(): void
    {
        $paymentFeeData = $this->createMock(PaymentFeeData::class);
        $paymentFeeData->method('getPaymentFeeTaxIncl')->willReturn(0.0);

        $this->paymentFeeValidator->validatePaymentFeeAmount($paymentFeeData, 10.0);

        self::assertTrue(true);
    }
}
