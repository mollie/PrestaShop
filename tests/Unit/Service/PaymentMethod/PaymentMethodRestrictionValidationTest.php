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

namespace Mollie\Tests\Unit\Service\PaymentMethod;

use Mollie\Logger\LoggerInterface;
use Mollie\Service\PaymentMethod\PaymentMethodRestrictionValidation;
use Mollie\Service\PaymentMethod\PaymentMethodRestrictionValidation\ApplePayPaymentMethodRestrictionValidator;
use Mollie\Service\PaymentMethod\PaymentMethodRestrictionValidation\BasePaymentMethodRestrictionValidator;
use PHPUnit\Framework\TestCase;

class PaymentMethodRestrictionValidationTest extends TestCase
{
    private function validation(): PaymentMethodRestrictionValidation
    {
        $base = $this->getMockBuilder(BasePaymentMethodRestrictionValidator::class)->disableOriginalConstructor()->getMock();
        $base->method('supports')->willReturn(true);
        $base->method('isValid')->willReturn(true);

        $applePay = $this->getMockBuilder(ApplePayPaymentMethodRestrictionValidator::class)->disableOriginalConstructor()->getMock();
        $applePay->method('supports')->willReturn(true);
        $applePay->method('isValid')->willReturn(false);

        return new PaymentMethodRestrictionValidation(
            [$base, $applePay],
            $this->getMockBuilder(LoggerInterface::class)->getMock()
        );
    }

    public function testEveryValidatorAppliesByDefault(): void
    {
        $this->assertFalse($this->validation()->isPaymentMethodValid(new \MolPaymentMethod()));
    }

    public function testASkippedValidatorIsNotConsulted(): void
    {
        $this->assertTrue($this->validation()->isPaymentMethodValid(new \MolPaymentMethod(), [ApplePayPaymentMethodRestrictionValidator::class]));
    }
}
