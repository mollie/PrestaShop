<?php

declare(strict_types=1);

use Mollie\Api\Types\MandateMethod;
use Mollie\Repository\MolCustomerRepository;
use Mollie\Repository\PaymentMethodRepository;
use Mollie\Subscription\Factory\CreateMandateDataFactory;
use Mollie\Tests\Integration\BaseTestCase;

class TestCreateMandateData extends BaseTestCase
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
        /** @var CreateMandateDataFactory $mandateDataBuilder */
        $mandateDataBuilder = new CreateMandateDataFactory(new MolCustomerRepository('MolCustomer'), $paymentMethodMock);

        $customer = $this->createMock('Customer');
        $customer->email = self::CUSTOMER_EMAIL;

        $orderMock = $this->createMock('Order');
        $orderMock->method('getCustomer')->willReturn($customer);

        $CreateMandateData = $mandateDataBuilder->build($orderMock);
        $this->assertEquals(self::CUSTOMER_ID, $CreateMandateData->getCustomerId());
        $this->assertEquals(
            [
                'method' => MandateMethod::CREDITCARD,
                'consumerName' => self::CUSTOMER_NAME,
            ],
            $CreateMandateData->jsonSerialize()
        );
    }
}
