<?php

use Mollie\Exception\OrderTotalRestrictionException;
use Mollie\Repository\CurrencyRepository;
use Mollie\Repository\PaymentMethodRepositoryInterface;
use Mollie\Tests\Unit\Tools\UnitTestCase;
use Mollie\Verification\OrderTotal\CanOrderTotalBeUpdated;
use PHPUnit\Framework\MockObject\MockObject;

class CanOrderTotalBeUpdatedTest extends UnitTestCase
{
    /**
     * @var CurrencyRepository|MockObject
     */
    private $currencyRepository;

    /**
     * @var PaymentMethodRepositoryInterface|MockObject
     */
    private $paymentMethodRepository;

    /**
     * @var MockObject|PrestaShopCollection
     */
    private $paymentMethodCollection;

    /**
     * @var MockObject|PrestaShopCollection
     */
    private $currencyCollection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->paymentMethodRepository = $this
            ->getMockBuilder(PaymentMethodRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->currencyRepository = $this
            ->getMockBuilder(CurrencyRepository::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->paymentMethodCollection = $this
            ->getMockBuilder(PrestaShopCollection::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->currencyCollection = $this
            ->getMockBuilder(PrestaShopCollection::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
    }

    /** @dataProvider getCanOrderTotalBeUpdatedData */
    public function testCanOrderTotalBeUpdated($paymentCount, $currencyCount, $exception, $expected)
    {
        $this->paymentMethodCollection
            ->expects($this->any())
            ->method('count')
            ->willReturn($paymentCount)
        ;

        $this->paymentMethodRepository
            ->expects($this->any())
            ->method('findAll')
            ->willReturn($this->paymentMethodCollection)
        ;

        $this->currencyCollection
            ->expects($this->any())
            ->method('count')
            ->willReturn($currencyCount)
        ;

        $this->currencyRepository
            ->expects($this->any())
            ->method('findAll')
            ->willReturn($this->currencyCollection)
        ;

        $canOrderTotalBeUpdated = new CanOrderTotalBeUpdated(
            $this->paymentMethodRepository,
            $this->currencyRepository
        );

        if ($exception) {
            $this->expectException($exception['class']);
            $this->expectExceptionCode($exception['code']);
        }

        $result = $canOrderTotalBeUpdated->verify();

        $this->assertEquals($expected, $result);
    }

    public function getCanOrderTotalBeUpdatedData()
    {
        return [
            'All checks pass' => [
                'paymentCount' => 10,
                'currencyCount' => 10,
                'exception' => [],
                'expected' => true,
            ],
            'Has no available payment methods' => [
                'paymentCount' => 0,
                'currencyCount' => 10,
                'exception' => [
                    'class' => OrderTotalRestrictionException::class,
                    'code' => OrderTotalRestrictionException::NO_AVAILABLE_PAYMENT_METHODS_FOUND,
                ],
                'expected' => null,
            ],
            'Has no available currencies' => [
                'paymentCount' => 10,
                'currencyCount' => 0,
                'exception' => [
                    'class' => OrderTotalRestrictionException::class,
                    'code' => OrderTotalRestrictionException::NO_AVAILABLE_CURRENCIES_FOUND,
                ],
                'expected' => null,
            ],
        ];
    }
}