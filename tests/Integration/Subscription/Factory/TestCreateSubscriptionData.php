<?php

declare(strict_types=1);

use Mollie\Api\Types\MandateMethod;
use Mollie\Repository\MolCustomerRepository;
use Mollie\Repository\PaymentMethodRepository;
use Mollie\Subscription\Config\Config;
use Mollie\Subscription\Constants\IntervalConstant;
use Mollie\Subscription\DTO\Object\Interval;
use Mollie\Subscription\Factory\CreateSubscriptionDataFactory;
use Mollie\Subscription\Provider\SubscriptionDescriptionProvider;
use Mollie\Subscription\Provider\SubscriptionIntervalProvider;
use Mollie\Subscription\Repository\CombinationRepository;
use Mollie\Subscription\Repository\CurrencyRepository;
use Mollie\Tests\Integration\BaseTestCase;

class TestCreateSubscriptionData extends BaseTestCase
{
    private const CUSTOMER_EMAIL = 'test@gmail.com';
    private const CUSTOMER_ID = 'testId';
    private const CUSTOMER_NAME = 'testName';

    protected function setUp(): void
    {
        parent::setUp();

        $molCustomer = new MolCustomer();
        $molCustomer->name = self::CUSTOMER_NAME;
        $molCustomer->email = self::CUSTOMER_EMAIL;
        $molCustomer->customer_id = self::CUSTOMER_ID;

        $molCustomer->add();
    }

    public function testBuild()
    {
        $paymentMethodMock = $this->createMock(PaymentMethodRepository::class);
        $paymentMethodMock->method('getPaymentBy')->willReturn(
            [
                'method' => MandateMethod::CREDITCARD,
                'name' => self::CUSTOMER_NAME,
                'mandate_id' => 'test-mandate-id'
            ]
        );

        $intervalAmount = 1;

        $subscriptionIntervalMock = $this->createMock(SubscriptionIntervalProvider::class);
        $subscriptionIntervalMock->method('getSubscriptionInterval')->willReturn(new Interval($intervalAmount, IntervalConstant::DAY));

        $paymentMethodMock = $this->createMock(PaymentMethodRepository::class);
        $paymentMethodMock->method('getPaymentBy')->willReturn(
            [
                'method' => MandateMethod::CREDITCARD,
                'name' => self::CUSTOMER_NAME,
                'mandate_id' => 'test-mandate-id'
            ]
        );
        $combinationMock = $this->createMock(\Combination::class);
        $combinationMock->method('getWsProductOptionValues')->willReturn(
            [
                [
                    'id' => $this->configuration->get(Config::SUBSCRIPTION_ATTRIBUTE_DAILY),
                ],
            ]
        );

        $combinationRepositoryMock = $this->createMock(CombinationRepository::class);
        $combinationRepositoryMock->method('getById')->willReturn($combinationMock);

        $customerRepository = new MolCustomerRepository('MolCustomer');

        $link = $this->createMock(\Mollie\Adapter\Link::class);
        $link->method('getModuleLink')->willReturn('test-link');

        /** @var CreateSubscriptionDataFactory $createSubscriptionData */
        $createSubscriptionData = new CreateSubscriptionDataFactory(
            $customerRepository,
            $subscriptionIntervalMock,
            new SubscriptionDescriptionProvider(),
            new CurrencyRepository(),
            $combinationRepositoryMock,
            $paymentMethodMock,
            $link,
            new Mollie()
        );

        $customer = $this->createMock('Customer');
        $customer->email = self::CUSTOMER_EMAIL;

        $orderMock = $this->createMock('Order');
        $orderMock->id = 9999;
        $orderMock->reference = 'REFERENCE123';
        $orderMock->id_currency = 1;
        $orderMock->total_paid_tax_incl = 19.99;
        $orderMock->method('getCustomer')->willReturn($customer);
        $orderMock->method('getCartProducts')->willReturn(
            [
                [
                    'id_product_attribute' => 999,
                ],
            ]
        );

        $subscriptionData = $createSubscriptionData->build($orderMock);

        $this->assertEquals(self::CUSTOMER_ID, $subscriptionData->getCustomerId());
        $this->assertEquals(
            [
                'amount' => [
                        'value' => '19.99',
                        'currency' => 'EUR',
                    ],
                'interval' => '1 day',
                'description' => 'subscription-REFERENCE123',
                'webhookUrl' => 'test-link',
                'mandateId' => 'test-mandate-id',
                'metadata' => [
                    'secure_key' => $subscriptionData->getMetaData()['secure_key'], //NOTE: cannot really mock static methods.
                ],
            ],
            $subscriptionData->jsonSerialize()
        );
    }
}
