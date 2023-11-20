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

namespace Mollie\Tests\Unit\Builder;

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

    public function setUp()
    {
        parent::setUp();

        $this->molOrderPaymentFeeRepository = $this->createMock(MolOrderPaymentFeeRepositoryInterface::class);
        $this->currencyRepository = $this->createMock(CurrencyRepositoryInterface::class);
    }

    public function testItSuccessfullyBuildsTemplate(): void
    {
        $molOrderPaymentFee = $this->createMock(MolOrderPaymentFee::class);
        $molOrderPaymentFee->fee_tax_incl = 10.00;

        $this->molOrderPaymentFeeRepository->expects($this->once())->method('findOneBy')->willReturn($molOrderPaymentFee);

        $orderCurrency = $this->createMock(\Currency::class);
        $orderCurrency->iso_code = 'USD';

        $this->currencyRepository->expects($this->once())->method('findOneBy')->willReturn($orderCurrency);

        $invoicePdfTemplateBuilder = new InvoicePdfTemplateBuilder(
            $this->molOrderPaymentFeeRepository,
            $this->currencyRepository
        );

        $order = $this->createMock(\Order::class);
        $order->id = 1;
        $order->id_currency = 1;

        $locale = $this->createMock(Locale::class);
        $locale->expects($this->once())->method('formatPrice')->willReturn('$ 10.00');

        $result = $invoicePdfTemplateBuilder
            ->setOrder($order)
            ->setLocale($locale)
            ->buildParams();

        $expectedResult = [
            'orderFeeAmountDisplay' => '$ 10.00',
        ];

        $this->assertEquals($expectedResult, $result);
    }

    public function testItUnsuccessfullyBuildsTemplateFailedToFindOrderPaymentFee(): void
    {
        $this->molOrderPaymentFeeRepository->expects($this->once())->method('findOneBy')->willReturn(null);

        $orderCurrency = $this->createMock(\Currency::class);
        $orderCurrency->iso_code = 'USD';

        $this->currencyRepository->expects($this->never())->method('findOneBy')->willReturn($orderCurrency);

        $invoicePdfTemplateBuilder = new InvoicePdfTemplateBuilder(
            $this->molOrderPaymentFeeRepository,
            $this->currencyRepository
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

    public function testItUnsuccessfullyBuildsTemplateFailedToFindOrderCurrency(): void
    {
        $molOrderPaymentFee = $this->createMock(MolOrderPaymentFee::class);
        $molOrderPaymentFee->fee_tax_incl = 10.00;

        $this->molOrderPaymentFeeRepository->expects($this->once())->method('findOneBy')->willReturn($molOrderPaymentFee);

        $this->currencyRepository->expects($this->once())->method('findOneBy')->willReturn(null);

        $invoicePdfTemplateBuilder = new InvoicePdfTemplateBuilder(
            $this->molOrderPaymentFeeRepository,
            $this->currencyRepository
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
