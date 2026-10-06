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

namespace Mollie\Tests\Unit\Handler\PaymentOption;

use Mollie\Adapter\CartAdapter;
use Mollie\Config\Config;
use Mollie\Handler\PaymentOption\PaymentOptionHandler;
use Mollie\Provider\PaymentOption\BancontactPaymentOptionProvider;
use Mollie\Provider\PaymentOption\BasePaymentOptionProvider;
use Mollie\Provider\PaymentOption\CreditCardPaymentOptionProvider;
use Mollie\Provider\PaymentOption\CreditCardSingleClickPaymentOptionProvider;
use Mollie\Provider\PaymentOption\IdealPaymentOptionProvider;
use Mollie\Service\ExpressComponent\ExpressComponentAvailabilityService;
use Mollie\Tests\Unit\BaseTestCase;
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

class PaymentOptionHandlerExpressTest extends BaseTestCase
{
    /** @var ExpressComponentAvailabilityService|\PHPUnit\Framework\MockObject\MockObject */
    private $availability;
    /** @var array<string, mixed> */
    private $config = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->availability = $this->mock(ExpressComponentAvailabilityService::class);
        $this->configuration->method('get')->willReturnCallback(function ($key) {
            return $this->config[$key] ?? null;
        });
    }

    private function handler(): PaymentOptionHandler
    {
        $base = $this->mock(BasePaymentOptionProvider::class);
        $base->method('getPaymentOption')->willReturnCallback(static function () {
            $option = new PaymentOption();
            $option->setCallToActionText('Pay');

            return $option;
        });

        $cartAdapter = $this->mock(CartAdapter::class);
        $cartAdapter->method('getCart')->willReturn($this->cart);

        return new PaymentOptionHandler(
            $base,
            $this->mock(CreditCardPaymentOptionProvider::class),
            $this->mock(CreditCardSingleClickPaymentOptionProvider::class),
            $this->mock(IdealPaymentOptionProvider::class),
            $this->mock(BancontactPaymentOptionProvider::class),
            $this->configuration,
            $this->availability,
            $cartAdapter
        );
    }

    private function method(string $id): \MolPaymentMethod
    {
        $method = new \MolPaymentMethod();
        $method->id_method = $id;
        $method->method_name = $id;

        return $method;
    }

    public function testAWalletWithItsExpressButtonOnIsMarkedAsCovered(): void
    {
        $this->config[Config::MOLLIE_EXPRESS_COMPONENT_APPLEPAY] = '1';
        $this->availability->method('isAvailable')->willReturn(true);

        $option = $this->handler()->handle($this->method('applepay'));

        $this->assertStringContainsString('class="mollie-express-covered"', (string) $option->getAdditionalInformation());
        $this->assertStringContainsString('data-mollie-express-method="applepay"', (string) $option->getAdditionalInformation());
    }

    public function testTheMarkerIsNotAddedWhenTheButtonIsOff(): void
    {
        $this->config[Config::MOLLIE_EXPRESS_COMPONENT_PAYPAL] = '0';
        $this->availability->method('isAvailable')->willReturn(true);

        $option = $this->handler()->handle($this->method('paypal'));

        $this->assertSame('', (string) $option->getAdditionalInformation());
    }

    public function testTheMarkerIsNotAddedWhenTheComponentCannotRender(): void
    {
        $this->config[Config::MOLLIE_EXPRESS_COMPONENT_PAYPAL] = '1';
        $this->availability->method('isAvailable')->willReturn(false);

        $option = $this->handler()->handle($this->method('paypal'));

        $this->assertSame('', (string) $option->getAdditionalInformation());
    }

    public function testCardsAreNeverCoveredByTheGooglePayButton(): void
    {
        $this->config[Config::MOLLIE_EXPRESS_COMPONENT_GOOGLEPAY] = '1';
        $this->availability->method('isAvailable')->willReturn(true);

        $option = $this->handler()->handle($this->method('klarna'));

        $this->assertSame('', (string) $option->getAdditionalInformation());
    }
}
