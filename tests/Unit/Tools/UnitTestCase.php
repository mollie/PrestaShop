<?php

namespace Mollie\Tests\Unit\Tools;

use Cart;
use Currency;
use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Adapter\LegacyContext;
use Mollie\Api\Resources\Method;
use Mollie\Repository\PaymentMethodRepositoryInterface;
use MolPaymentMethod;
use PHPUnit\Framework\TestCase;
use stdClass;

class UnitTestCase extends TestCase
{
    public function mockMethodResponse($minimumAmountValue, $maximumAmountValue)
    {
        $method = $this
            ->getMockBuilder(Method::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $method->minimumAmount = new stdClass();
        $method->maximumAmount = new stdClass();
        $method->minimumAmount->value = $minimumAmountValue;
        $method->maximumAmount->value = $maximumAmountValue;

        return $method;
    }

    public function mockContext($countryCode, $currencyCode)
    {
        $contextMock = $this->getMockBuilder(LegacyContext::class)
            ->disableOriginalConstructor()
            ->getMock();

        $contextMock
            ->method('getCountryIsoCode')
            ->willReturn($countryCode)
        ;

        $contextMock
            ->method('getCurrencyIsoCode')
            ->willReturn($currencyCode)
        ;

        $contextMock
            ->method('getCurrencyId')
            ->willReturn(1)
        ;

        $contextMock
            ->method('getCountryId')
            ->willReturn(1)
        ;

        $contextMock
            ->method('getInvoiceCountryId')
            ->willReturn(1)
        ;

        return $contextMock;
    }

    public function mockContextWithCookie($cookieValue)
    {
        $contextMock = $this->getMockBuilder(LegacyContext::class)
            ->disableOriginalConstructor()
            ->getMock();

        $contextMock
            ->method('getCookieValue')
            ->willReturn($cookieValue)
        ;

        return $contextMock;
    }

    public function mockPaymentMethod($paymentName, $enabled)
    {
        $paymentMethod = $this->getMockBuilder(MolPaymentMethod::class)
            ->disableOriginalConstructor()
            ->getMock();

        $paymentMethod
            ->method('getPaymentMethodName')
            ->willReturn($paymentName)
        ;
        $paymentMethod->enabled = $enabled;

        return $paymentMethod;
    }

    public function mockPaymentMethodRepository()
    {
        $paymentMethodRepository = $this->getMockBuilder(PaymentMethodRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $paymentMethodRepository
            ->method('findAll')
            ->willReturn(
                new \PrestaShopCollection(MolPaymentMethod::class)
            )
        ;
    }

    public function mockConfigurationAdapter($configuration)
    {
        $configurationAdapter = $this->getMockBuilder(ConfigurationAdapter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $configurationAdapter
            ->method('get')
            ->with('PS_SSL_ENABLED_EVERYWHERE')
            ->willReturn($configuration['PS_SSL_ENABLED_EVERYWHERE'])
        ;

        return $configurationAdapter;
    }

    protected function mockCart($totalOrderAmount)
    {
        $cartMock = $this->getMockBuilder(Cart::class)
            ->disableOriginalConstructor()
            ->getMock();

        $cartMock
            ->method('getOrderTotal')
            ->willReturn($totalOrderAmount)
        ;

        return $cartMock;
    }

    protected function mockCurrency($currencyIsoCode, $conversionRate)
    {
        $cartMock = $this->getMockBuilder(Currency::class)
            ->disableOriginalConstructor()
            ->getMock();

        $cartMock
            ->method('getConversionRate')
            ->willReturn($conversionRate)
        ;
        $cartMock->iso_code = $currencyIsoCode;

        return $cartMock;
    }
}
