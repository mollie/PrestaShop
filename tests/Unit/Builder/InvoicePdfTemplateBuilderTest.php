<?php

namespace Mollie\Tests\Unit\Builder;

use Mollie\Adapter\ToolsAdapter;
use Mollie\Builder\InvoicePdfTemplateBuilder;
use Mollie\Repository\CurrencyRepositoryInterface;
use Mollie\Repository\MolOrderPaymentFeeRepositoryInterface;
use MolOrderPaymentFee;
use PHPUnit\Framework\TestCase;
use PrestaShop\PrestaShop\Core\Localization\Locale;

class InvoicePdfTemplateBuilderTest extends TestCase
{
    /** @var MolOrderPaymentFeeRepositoryInterface */
    private $molOrderPaymentFeeRepository;
    /** @var CurrencyRepositoryInterface */
    private $currencyRepository;
    /** @var ToolsAdapter */
    private $tools;

    public function setUp()
    {
        parent::setUp();

        $this->molOrderPaymentFeeRepository = $this->createMock(MolOrderPaymentFeeRepositoryInterface::class);
        $this->currencyRepository = $this->createMock(CurrencyRepositoryInterface::class);
        $this->tools = $this->createMock(ToolsAdapter::class);
    }

    public function testItSuccessfullyBuildsTemplate()
    {
        $molOrderPaymentFee = $this->createMock(MolOrderPaymentFee::class);
        $molOrderPaymentFee->fee_tax_incl = 10.00;

        $this->molOrderPaymentFeeRepository->expects($this->once())->method('findOneBy')->willReturn($molOrderPaymentFee);

        $orderCurrency = $this->createMock(\Currency::class);
        $orderCurrency->iso_code = 'USD';

        $this->currencyRepository->expects($this->once())->method('findOneBy')->willReturn($orderCurrency);

        $this->tools->method('displayPrice')->willReturn('$ 10.00');

        $invoicePdfTemplateBuilder = new InvoicePdfTemplateBuilder(
            $this->molOrderPaymentFeeRepository,
            $this->currencyRepository,
            $this->tools
        );

        $order = $this->createMock(\Order::class);
        $order->id = 1;
        $order->id_currency = 1;

        $locale = $this->createMock(Locale::class);
        $locale->method('formatPrice')->willReturn('$ 10.00');

        $result = $invoicePdfTemplateBuilder
            ->setOrder($order)
            ->setLocale($locale)
            ->buildParams();

        $expectedResult = [
            'orderFeeAmountDisplay' => '$ 10.00',
        ];

        $this->assertEquals($expectedResult, $result);
    }

    public function testItUnsuccessfullyBuildsTemplateFailedToFindOrderPaymentFee()
    {
        $this->molOrderPaymentFeeRepository->expects($this->once())->method('findOneBy')->willReturn(null);

        $orderCurrency = $this->createMock(\Currency::class);
        $orderCurrency->iso_code = 'USD';

        $this->currencyRepository->expects($this->never())->method('findOneBy')->willReturn($orderCurrency);

        $this->tools->expects($this->never())->method('displayPrice')->willReturn('$ 10.00');

        $invoicePdfTemplateBuilder = new InvoicePdfTemplateBuilder(
            $this->molOrderPaymentFeeRepository,
            $this->currencyRepository,
            $this->tools
        );

        $order = $this->createMock(\Order::class);
        $order->id = 1;
        $order->id_currency = 1;

        $locale = $this->createMock(Locale::class);
        $locale->expects($this->never())->method('formatPrice')->willReturn('$ 10.00');

        $result = $invoicePdfTemplateBuilder
            ->setOrder($order)
            ->setLocale($locale)
            ->buildParams();

        $expectedResult = [];

        $this->assertEquals($expectedResult, $result);
    }

    public function testItUnsuccessfullyBuildsTemplateFailedToFindOrderCurrency()
    {
        $molOrderPaymentFee = $this->createMock(MolOrderPaymentFee::class);
        $molOrderPaymentFee->fee_tax_incl = 10.00;

        $this->molOrderPaymentFeeRepository->expects($this->once())->method('findOneBy')->willReturn($molOrderPaymentFee);

        $this->currencyRepository->expects($this->once())->method('findOneBy')->willReturn(null);

        $this->tools->expects($this->never())->method('displayPrice')->willReturn('$ 10.00');

        $invoicePdfTemplateBuilder = new InvoicePdfTemplateBuilder(
            $this->molOrderPaymentFeeRepository,
            $this->currencyRepository,
            $this->tools
        );

        $order = $this->createMock(\Order::class);
        $order->id = 1;
        $order->id_currency = 1;

        $locale = $this->createMock(Locale::class);
        $locale->expects($this->never())->method('formatPrice')->willReturn('$ 10.00');

        $result = $invoicePdfTemplateBuilder
            ->setOrder($order)
            ->setLocale($locale)
            ->buildParams();

        $expectedResult = [];

        $this->assertEquals($expectedResult, $result);
    }
}
