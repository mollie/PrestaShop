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

namespace Mollie\Tests\Unit\Service;

use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Api\Types\PaymentMethod;
use Mollie\Config\Config;
use Mollie\Service\PaymentExpiryCalculator;
use PHPUnit\Framework\TestCase;

class PaymentExpiryCalculatorTest extends TestCase
{
    /**
     * @var ConfigurationAdapter|\PHPUnit\Framework\MockObject\MockObject
     */
    private $configurationMock;

    /**
     * @var PaymentExpiryCalculator
     */
    private $calculator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->configurationMock = $this->createMock(ConfigurationAdapter::class);
        $this->calculator = new PaymentExpiryCalculator($this->configurationMock);
    }

    public function testCalculatesDueDateForBankTransfer(): void
    {
        $this->configurationMock
            ->method('get')
            ->with(Config::MOLLIE_BANKTRANSFER_DUE_DAYS)
            ->willReturn('7');

        $result = $this->calculator->calculateDueDate(PaymentMethod::BANKTRANSFER);

        $this->assertNotNull($result);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $result);

        $expectedDate = new \DateTime();
        $expectedDate->modify('+7 days');

        $this->assertEquals($expectedDate->format('Y-m-d'), $result);
    }

    public function testReturnsNullForNonBankTransfer(): void
    {
        $this->assertNull($this->calculator->calculateDueDate(PaymentMethod::CREDITCARD));
    }

    public function testUsesDefaultWhenConfiguredValueIsNull(): void
    {
        $this->configurationMock
            ->method('get')
            ->with(Config::MOLLIE_BANKTRANSFER_DUE_DAYS)
            ->willReturn(null);

        $result = $this->calculator->calculateDueDate(PaymentMethod::BANKTRANSFER);

        $expectedDate = new \DateTime();
        $expectedDate->modify('+14 days');

        $this->assertEquals($expectedDate->format('Y-m-d'), $result);
    }

    public function testUsesDefaultWhenBelowMinimum(): void
    {
        $this->configurationMock
            ->method('get')
            ->with(Config::MOLLIE_BANKTRANSFER_DUE_DAYS)
            ->willReturn('0');

        $result = $this->calculator->calculateDueDate(PaymentMethod::BANKTRANSFER);

        $expectedDate = new \DateTime();
        $expectedDate->modify('+14 days');
        $this->assertEquals($expectedDate->format('Y-m-d'), $result);
    }

    public function testUsesDefaultWhenAboveMaximum(): void
    {
        $this->configurationMock
            ->method('get')
            ->with(Config::MOLLIE_BANKTRANSFER_DUE_DAYS)
            ->willReturn('100');

        $result = $this->calculator->calculateDueDate(PaymentMethod::BANKTRANSFER);

        $expectedDate = new \DateTime();
        $expectedDate->modify('+14 days');
        $this->assertEquals($expectedDate->format('Y-m-d'), $result);
    }

    public function testAcceptsValidRangeValues(): void
    {
        $validValues = [1, 7, 14, 30, 60, 90];

        foreach ($validValues as $days) {
            $configMock = $this->createMock(ConfigurationAdapter::class);
            $configMock
                ->method('get')
                ->with(Config::MOLLIE_BANKTRANSFER_DUE_DAYS)
                ->willReturn((string) $days);

            $calculator = new PaymentExpiryCalculator($configMock);
            $result = $calculator->calculateDueDate(PaymentMethod::BANKTRANSFER);

            $expectedDate = new \DateTime();
            $expectedDate->modify("+{$days} days");

            $this->assertEquals(
                $expectedDate->format('Y-m-d'),
                $result,
                "Failed for {$days} days"
            );
        }
    }
}
