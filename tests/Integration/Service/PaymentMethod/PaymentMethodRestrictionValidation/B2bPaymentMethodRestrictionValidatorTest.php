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

namespace Mollie\Tests\Integration\Service\PaymentMethod\PaymentMethodRestrictionValidation;

use Configuration;
use Mollie\Api\Types\PaymentMethod;
use Mollie\Service\PaymentMethod\PaymentMethodRestrictionValidation\B2bPaymentMethodRestrictionValidator;
use Mollie\Tests\Integration\BaseTestCase;
use Mollie\Tests\Integration\Factory\AddressFactory;
use Mollie\Tests\Integration\Factory\CartFactory;
use Mollie\Tests\Integration\Factory\CustomerFactory;

class B2bPaymentMethodRestrictionValidatorTest extends BaseTestCase
{
    /** @var int */
    private $originalB2bValue;

    public function setUp(): void
    {
        $this->originalB2bValue = (int) Configuration::get('PS_B2B_ENABLE');

        parent::setUp();
    }

    public function tearDown(): void
    {
        Configuration::set('PS_B2B_ENABLE', $this->originalB2bValue);

        parent::tearDown();
    }

    public function testItSuccessfullyValidatedIsValid(): void
    {
        Configuration::set('PS_B2B_ENABLE', 1);

        $molPaymentMethod = new \MolPaymentMethod();
        $molPaymentMethod->id_method = PaymentMethod::BILLIE;

        /** @var B2bPaymentMethodRestrictionValidator $b2bPaymentMethodRestrictionValidator */
        $b2bPaymentMethodRestrictionValidator = $this->getService(B2bPaymentMethodRestrictionValidator::class);

        $supports = $b2bPaymentMethodRestrictionValidator->supports($molPaymentMethod);

        $valid = $b2bPaymentMethodRestrictionValidator->isValid($molPaymentMethod);

        $this->assertEquals(true, $supports);
        $this->assertEquals(true, $valid);
    }

    public function testItUnsuccessfullyValidatedIsValidB2bNotEnabled(): void
    {
        Configuration::set('PS_B2B_ENABLE', 0);

        $molPaymentMethod = new \MolPaymentMethod();
        $molPaymentMethod->id_method = PaymentMethod::BILLIE;

        /** @var B2bPaymentMethodRestrictionValidator $b2bPaymentMethodRestrictionValidator */
        $b2bPaymentMethodRestrictionValidator = $this->getService(B2bPaymentMethodRestrictionValidator::class);

        $supports = $b2bPaymentMethodRestrictionValidator->supports($molPaymentMethod);

        $valid = $b2bPaymentMethodRestrictionValidator->isValid($molPaymentMethod);

        $this->assertEquals(true, $supports);
        $this->assertEquals(false, $valid);
    }
}
