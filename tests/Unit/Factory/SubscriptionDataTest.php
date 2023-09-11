<?php

declare(strict_types=1);

namespace Mollie\Tests\Unit\Factory;

use Mollie;
use Mollie\Adapter\Link;
use Mollie\Repository\MolCustomerRepository;
use Mollie\Repository\PaymentMethodRepository;
use Mollie\Subscription\Constants\IntervalConstant;
use Mollie\Subscription\DTO\CreateSubscriptionData as SubscriptionDataDTO;
use Mollie\Subscription\DTO\Object\Amount;
use Mollie\Subscription\DTO\Object\Interval;
use Mollie\Subscription\Factory\CreateSubscriptionDataFactory;
use Mollie\Subscription\Provider\SubscriptionDescriptionProvider;
use Mollie\Subscription\Provider\SubscriptionIntervalProvider;
use Mollie\Subscription\Repository\CombinationRepository;
use Mollie\Subscription\Repository\CurrencyRepository;
use Mollie\Subscription\Validator\SubscriptionProductValidator;
use Mollie\Utility\SecureKeyUtility;
use PHPUnit\Framework\TestCase;

class SubscriptionDataTest extends TestCase
{
    private const TEST_ORDER_ID = 1;
    private const TEST_ORDER_REFERENCE = 111;
    private const TEST_CUSTOMER_ID = 222;
    private const TEST_CART_ID = 333;
    private const TEST_CURRENCY_ISO = 'EUR';
    private const TEST_MANDATE_ID = 'mandate_id_test';

    /**
     * @dataProvider subscriptionDataProvider
     */
    public function testBuildSubscriptionData(string $customerId, float $totalAmount, string $description, SubscriptionDataDTO $expectedResult): void
    {
        $molCustomer = $this->createMock('MolCustomer');
        $molCustomer->customer_id = $customerId;
        $customerRepositoryMock = $this->createMock(MolCustomerRepository::class);
        $customerRepositoryMock->method('findOneBy')->willReturn($molCustomer);

        $subscriptionIntervalProviderMock = $this->createMock(SubscriptionIntervalProvider::class);
        $subscriptionIntervalProviderMock->method('getSubscriptionInterval')->willReturn(new Interval(1, IntervalConstant::DAY));

        $subscriptionDescriptionProviderMock = $this->createMock(SubscriptionDescriptionProvider::class);
        $subscriptionDescriptionProviderMock->method('getSubscriptionDescription')->willReturn($description);

        $currency = $this->createMock('Currency');
        $currency->iso_code = 'EUR';

        $currencyAdapterMock = $this->createMock(CurrencyRepository::class);
        $currencyAdapterMock->method('getById')->willReturn($currency);

        $paymentMethodRepositoryMock = $this->createMock(PaymentMethodRepository::class);
        $paymentMethodRepositoryMock->method('getPaymentBy')->willReturn(
            [
                'mandate_id' => self::TEST_MANDATE_ID,
            ]
        );

        $subscriptionProductValidator = $this->createMock(SubscriptionProductValidator::class);
        $subscriptionProductValidator->method('validate')->willReturn(true);

        $subscriptionDataFactory = new CreateSubscriptionDataFactory(
            $customerRepositoryMock,
            $subscriptionIntervalProviderMock,
            $subscriptionDescriptionProviderMock,
            $currencyAdapterMock,
            new CombinationRepository(),
            $paymentMethodRepositoryMock,
            new Link(),
            new Mollie(),
            $subscriptionProductValidator
        );

        $customerMock = $this->createMock('Customer');
        $customerMock->email = 'test.gmail.com';

        $order = $this->createMock('Order');
        $order->method('getCustomer')->willReturn($customerMock);
        $order->method('getCartProducts')->willReturn([
            [
                'id_product_attribute' => 1,
                'total_price_tax_incl' => 19.99,
            ],
        ]);
        $order->id = self::TEST_ORDER_ID;
        $order->reference = self::TEST_ORDER_REFERENCE;
        $order->id_cart = self::TEST_CART_ID;
        $order->id_customer = self::TEST_CUSTOMER_ID;
        $order->id_currency = 1;
        $order->total_paid_tax_incl = $totalAmount;

        $subscriptionData = $subscriptionDataFactory->build($order);

        $this->assertEquals($expectedResult, $subscriptionData);
    }

    public function subscriptionDataProvider()
    {
        $subscriptionDto = new SubscriptionDataDTO(
            'testCustomerId',
            new Amount(19.99, 'EUR'),
            new Interval(1, IntervalConstant::DAY),
            'subscription-' . self::TEST_ORDER_REFERENCE
        );
        $subscriptionDto->setMandateId(self::TEST_MANDATE_ID);

        $key = SecureKeyUtility::generateReturnKey(
            self::TEST_CUSTOMER_ID,
            self::TEST_CART_ID,
            'mollie'
        );

        $subscriptionDto->setMetaData(
            [
                'secure_key' => $key,
            ]
        );

        $link = new Link();
        $subscriptionDto->setWebhookUrl($link->getModuleLink(
            'mollie',
            'subscriptionWebhook'
        ));

        return [
            'first example' => [
                'customer id' => 'testCustomerId',
                'total paid amount' => 19.99,
                'description' => 'subscription-' . self::TEST_ORDER_REFERENCE,
                'expected result' => $subscriptionDto,
            ],
        ];
    }
}
