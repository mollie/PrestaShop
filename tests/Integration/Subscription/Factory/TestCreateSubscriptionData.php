<?php

declare(strict_types=1);

use Mollie\Api\Types\MandateMethod;
use Mollie\Repository\MolCustomerRepository;
use Mollie\Repository\PaymentMethodRepository;
use Mollie\Subscription\Config\Config;
use Mollie\Subscription\Constants\IntervalConstant;
use Mollie\Subscription\DTO\Object\Interval;
use Mollie\Subscription\Factory\CreateSubscriptionData;
use Mollie\Subscription\Provider\SubscriptionDescription;
use Mollie\Subscription\Provider\SubscriptionInterval;
use Mollie\Subscription\Repository\Combination;
use Mollie\Subscription\Repository\Currency;
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
            ]
        );

        $intervalAmount = 1;

        $subscriptionIntervalMock = $this->createMock(SubscriptionInterval::class);
        $subscriptionIntervalMock->method('getSubscriptionInterval')->willReturn(new Interval($intervalAmount, IntervalConstant::DAY));

        $paymentMethodMock = $this->createMock(PaymentMethodRepository::class);
        $paymentMethodMock->method('getPaymentBy')->willReturn(
            [
                'method' => MandateMethod::CREDITCARD,
                'name' => self::CUSTOMER_NAME,
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

        $combinationRepositoryMock = $this->createMock(Combination::class);
        $combinationRepositoryMock->method('getById')->willReturn($combinationMock);

        $customerRepository = new MolCustomerRepository('MolCustomer');
        /** @var CreateSubscriptionData $createSubscriptionData */
        $createSubscriptionData = new CreateSubscriptionData(
            $customerRepository,
            $subscriptionIntervalMock,
            new SubscriptionDescription(),
            new Currency(),
            $combinationRepositoryMock,
            $paymentMethodMock
        );

        $customer = $this->createMock('Customer');
        $customer->email = self::CUSTOMER_EMAIL;

        $orderMock = $this->createMock('Order');
        $orderMock->id = 9999;
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
                'description' => 'mol-9999-19.99-EUR',
                'method' => MandateMethod::CREDITCARD,
            ],
            $subscriptionData->jsonSerialize()
        );
    }
}
