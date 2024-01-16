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

        $customer = CustomerFactory::create([
            'siret' => 'test-siret-number',
        ]);

        $billingAddress = AddressFactory::create([
            'vat_number' => 'vat-number',
        ]);

        $this->contextBuilder->setCart(CartFactory::create());
        $this->contextBuilder->getContext()->cart->id_address_invoice = $billingAddress->id;
        $this->contextBuilder->getContext()->cart->id_customer = $customer->id;

        /** @var B2bPaymentMethodRestrictionValidator $b2bPaymentMethodRestrictionValidator */
        $b2bPaymentMethodRestrictionValidator = $this->getService(B2bPaymentMethodRestrictionValidator::class);

        $supports = $b2bPaymentMethodRestrictionValidator->supports($molPaymentMethod);

        $valid = $b2bPaymentMethodRestrictionValidator->isValid($molPaymentMethod);

        $this->assertEquals(true, $supports);
        $this->assertEquals(true, $valid);
    }

    public function testItSuccessfullyValidatedIsValidMissingVatNumberInFormat(): void
    {
        Configuration::set('PS_B2B_ENABLE', 1);

        $molPaymentMethod = new \MolPaymentMethod();
        $molPaymentMethod->id_method = PaymentMethod::BILLIE;

        $customer = CustomerFactory::create([
            'siret' => 'test-siret-number',
        ]);

        $billingAddress = AddressFactory::create([
            'vat_number' => 'vat-number',
        ]);

        $addressFormat = new \AddressFormat($billingAddress->id_country);

        $originalCountryFormat = $addressFormat->format;

        $addressFormat->format = 'test-format';
        $addressFormat->save();

        $this->contextBuilder->setCart(CartFactory::create());
        $this->contextBuilder->getContext()->cart->id_address_invoice = $billingAddress->id;
        $this->contextBuilder->getContext()->cart->id_customer = $customer->id;

        /** @var B2bPaymentMethodRestrictionValidator $b2bPaymentMethodRestrictionValidator */
        $b2bPaymentMethodRestrictionValidator = $this->getService(B2bPaymentMethodRestrictionValidator::class);

        $supports = $b2bPaymentMethodRestrictionValidator->supports($molPaymentMethod);

        $valid = $b2bPaymentMethodRestrictionValidator->isValid($molPaymentMethod);

        $addressFormat->format = $originalCountryFormat;
        $addressFormat->save();

        $this->assertEquals(true, $supports);
        $this->assertEquals(true, $valid);
    }

    public function testItUnsuccessfullyValidatedIsValidMethodNotSupported(): void
    {
        Configuration::set('PS_B2B_ENABLE', 1);

        $molPaymentMethod = new \MolPaymentMethod();
        $molPaymentMethod->id_method = 'not-supported-method';

        $customer = CustomerFactory::create([
            'siret' => 'test-siret-number',
        ]);

        $billingAddress = AddressFactory::create([
            'vat_number' => 'vat-number',
        ]);

        $this->contextBuilder->setCart(CartFactory::create());
        $this->contextBuilder->getContext()->cart->id_address_invoice = $billingAddress->id;
        $this->contextBuilder->getContext()->cart->id_customer = $customer->id;

        /** @var B2bPaymentMethodRestrictionValidator $b2bPaymentMethodRestrictionValidator */
        $b2bPaymentMethodRestrictionValidator = $this->getService(B2bPaymentMethodRestrictionValidator::class);

        $supports = $b2bPaymentMethodRestrictionValidator->supports($molPaymentMethod);

        $valid = $b2bPaymentMethodRestrictionValidator->isValid($molPaymentMethod);

        $this->assertEquals(false, $supports);
        $this->assertEquals(true, $valid);
    }

    public function testItUnsuccessfullyValidatedIsValidMissingSiretNumber(): void
    {
        Configuration::set('PS_B2B_ENABLE', 1);

        $molPaymentMethod = new \MolPaymentMethod();
        $molPaymentMethod->id_method = PaymentMethod::BILLIE;

        $customer = CustomerFactory::create([
            'siret' => '',
        ]);

        $billingAddress = AddressFactory::create([
            'vat_number' => 'vat-number',
        ]);

        $this->contextBuilder->setCart(CartFactory::create([
            'id_customer' => $customer->id,
            'id_address_delivery' => $billingAddress->id,
            'id_address_invoice' => $billingAddress->id,
        ]));

        /** @var B2bPaymentMethodRestrictionValidator $b2bPaymentMethodRestrictionValidator */
        $b2bPaymentMethodRestrictionValidator = $this->getService(B2bPaymentMethodRestrictionValidator::class);

        $supports = $b2bPaymentMethodRestrictionValidator->supports($molPaymentMethod);

        $valid = $b2bPaymentMethodRestrictionValidator->isValid($molPaymentMethod);

        $this->assertEquals(true, $supports);
        $this->assertEquals(false, $valid);
    }

    public function testItUnsuccessfullyValidatedIsValidB2bNotEnabled(): void
    {
        Configuration::set('PS_B2B_ENABLE', 0);

        $molPaymentMethod = new \MolPaymentMethod();
        $molPaymentMethod->id_method = PaymentMethod::BILLIE;

        $customer = CustomerFactory::create([
            'siret' => 'test-siret',
        ]);

        $billingAddress = AddressFactory::create([
            'vat_number' => 'vat-number',
        ]);

        $this->contextBuilder->setCart(CartFactory::create([
            'id_customer' => $customer->id,
            'id_address_delivery' => $billingAddress->id,
            'id_address_invoice' => $billingAddress->id,
        ]));

        /** @var B2bPaymentMethodRestrictionValidator $b2bPaymentMethodRestrictionValidator */
        $b2bPaymentMethodRestrictionValidator = $this->getService(B2bPaymentMethodRestrictionValidator::class);

        $supports = $b2bPaymentMethodRestrictionValidator->supports($molPaymentMethod);

        $valid = $b2bPaymentMethodRestrictionValidator->isValid($molPaymentMethod);

        $this->assertEquals(true, $supports);
        $this->assertEquals(false, $valid);
    }

    public function testItUnsuccessfullyValidatedIsValidMissingVatNumberInBothAddresses(): void
    {
        Configuration::set('PS_B2B_ENABLE', 1);

        $molPaymentMethod = new \MolPaymentMethod();
        $molPaymentMethod->id_method = PaymentMethod::BILLIE;

        $customer = CustomerFactory::create([
            'siret' => 'test-siret',
        ]);

        $billingAddress = AddressFactory::create([
            'vat_number' => '',
        ]);

        $this->contextBuilder->setCart(CartFactory::create([
            'id_customer' => $customer->id,
            'id_address_delivery' => $billingAddress->id,
            'id_address_invoice' => $billingAddress->id,
        ]));

        /** @var B2bPaymentMethodRestrictionValidator $b2bPaymentMethodRestrictionValidator */
        $b2bPaymentMethodRestrictionValidator = $this->getService(B2bPaymentMethodRestrictionValidator::class);

        $supports = $b2bPaymentMethodRestrictionValidator->supports($molPaymentMethod);

        $valid = $b2bPaymentMethodRestrictionValidator->isValid($molPaymentMethod);

        $this->assertEquals(true, $supports);
        $this->assertEquals(false, $valid);
    }

    public function testItUnsuccessfullyValidatedIsValidMissingVatNumberInBillingAddress(): void
    {
        Configuration::set('PS_B2B_ENABLE', 1);

        $molPaymentMethod = new \MolPaymentMethod();
        $molPaymentMethod->id_method = PaymentMethod::BILLIE;

        $customer = CustomerFactory::create([
            'siret' => 'test-siret',
        ]);

        $billingAddress = AddressFactory::create([
            'vat_number' => '',
        ]);

        $shippingAddress = AddressFactory::create([
            'vat_number' => 'test-vat-number',
        ]);

        $this->contextBuilder->setCart(CartFactory::create([
            'id_customer' => $customer->id,
            'id_address_delivery' => $shippingAddress->id,
            'id_address_invoice' => $billingAddress->id,
        ]));

        /** @var B2bPaymentMethodRestrictionValidator $b2bPaymentMethodRestrictionValidator */
        $b2bPaymentMethodRestrictionValidator = $this->getService(B2bPaymentMethodRestrictionValidator::class);

        $supports = $b2bPaymentMethodRestrictionValidator->supports($molPaymentMethod);

        $valid = $b2bPaymentMethodRestrictionValidator->isValid($molPaymentMethod);

        $this->assertEquals(true, $supports);
        $this->assertEquals(false, $valid);
    }
}
