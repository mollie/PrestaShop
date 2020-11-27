<?php

namespace Service;

use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Repository\CountryRepository;
use Mollie\Repository\PaymentMethodRepository;
use Mollie\Service\ApiService;
use MolliePrefix\Mollie\Api\MollieApiClient;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;


class ApiServiceTest extends TestCase
{

    public function testGetMethodsForConfig(array $methods, $isSSLEnabled)
    {
        /** @var MockObject $paymentMethodRepository */
        $paymentMethodRepository = $this->getMockBuilder(PaymentMethodRepository::class)->getMock();

        /** @var MockObject $countryRepository */
        $countryRepository = $this->getMockBuilder(CountryRepository::class)->getMock();

        /** @var MockObject $configurationAdapter */
        $configurationAdapter = $this->getMockBuilder(ConfigurationAdapter::class)->getMock();
        $configurationAdapter->method('get')->with('PS_SSL_ENABLED_EVERYWHERE')->willReturn($isSSLEnabled);

        /** @var MockObject $mollieApiClient */
        $mollieApiClient = $this->getMockBuilder(MollieApiClient::class)->getMock();

        $apiService = new ApiService($paymentMethodRepository, $countryRepository, $configurationAdapter);

        $response = $apiService->getMethodsForConfig($mollieApiClient, '/mollie/modules/mollie/', false);
    }

    public function testGetMethodsForConfigDataProvider()
    {
        return [
            'case1' => [
                'methods' => [
                    0 =>
                        (object)[
                            'id' => 'applepay',
                            'description' => 'Apple Pay',
                            'minimumAmount' =>
                                [
                                    'value' => '0.01',
                                    'currency' => 'EUR',
                                ],
                            'maximumAmount' =>
                                [
                                    'value' => '5000.00',
                                    'currency' => 'EUR',
                                ],
                            'image' =>
                                [
                                    'size1x' => 'https://www.mollie.com/external/icons/payment-methods/applepay.png',
                                    'size2x' => 'https://www.mollie.com/external/icons/payment-methods/applepay%402x.png',
                                    'svg' => 'https://www.mollie.com/external/icons/payment-methods/applepay.svg',
                                ],
                            'issuers' => NULL,
                            'pricing' => NULL,
                            'status' => 'activated',
                            '_links' =>
                                [
                                    'self' =>
                                        [
                                            'href' => 'https://api.mollie.com/v2/methods/applepay',
                                            'type' => 'application/hal+json',
                                        ],
                                ],
                            'resource' => 'method',
                        ],
                    1 =>
                        (object)[
                            'id' => 'ideal',
                            'description' => 'iDEAL',
                            'minimumAmount' =>
                                [
                                    'value' => '0.01',
                                    'currency' => 'EUR',
                                ],
                            'maximumAmount' =>
                                [
                                    'value' => '50000.00',
                                    'currency' => 'EUR',
                                ],
                            'image' =>
                                [
                                    'size1x' => 'https://www.mollie.com/external/icons/payment-methods/ideal.png',
                                    'size2x' => 'https://www.mollie.com/external/icons/payment-methods/ideal%402x.png',
                                    'svg' => 'https://www.mollie.com/external/icons/payment-methods/ideal.svg',
                                ],
                            'issuers' =>
                                [
                                    0 =>
                                        [
                                            'resource' => 'issuer',
                                            'id' => 'ideal_ABNANL2A',
                                            'name' => 'ABN AMRO',
                                            'image' =>
                                                [
                                                    'size1x' => 'https://www.mollie.com/external/icons/ideal-issuers/ABNANL2A.png',
                                                    'size2x' => 'https://www.mollie.com/external/icons/ideal-issuers/ABNANL2A%402x.png',
                                                    'svg' => 'https://www.mollie.com/external/icons/ideal-issuers/ABNANL2A.svg',
                                                ],
                                        ],
                                    1 =>
                                        [
                                            'resource' => 'issuer',
                                            'id' => 'ideal_INGBNL2A',
                                            'name' => 'ING',
                                            'image' =>
                                                [
                                                    'size1x' => 'https://www.mollie.com/external/icons/ideal-issuers/INGBNL2A.png',
                                                    'size2x' => 'https://www.mollie.com/external/icons/ideal-issuers/INGBNL2A%402x.png',
                                                    'svg' => 'https://www.mollie.com/external/icons/ideal-issuers/INGBNL2A.svg',
                                                ],
                                        ],
                                    2 =>
                                        [
                                            'resource' => 'issuer',
                                            'id' => 'ideal_RABONL2U',
                                            'name' => 'Rabobank',
                                            'image' =>
                                                [
                                                    'size1x' => 'https://www.mollie.com/external/icons/ideal-issuers/RABONL2U.png',
                                                    'size2x' => 'https://www.mollie.com/external/icons/ideal-issuers/RABONL2U%402x.png',
                                                    'svg' => 'https://www.mollie.com/external/icons/ideal-issuers/RABONL2U.svg',
                                                ],
                                        ],
                                    3 =>
                                        [
                                            'resource' => 'issuer',
                                            'id' => 'ideal_ASNBNL21',
                                            'name' => 'ASN Bank',
                                            'image' =>
                                                [
                                                    'size1x' => 'https://www.mollie.com/external/icons/ideal-issuers/ASNBNL21.png',
                                                    'size2x' => 'https://www.mollie.com/external/icons/ideal-issuers/ASNBNL21%402x.png',
                                                    'svg' => 'https://www.mollie.com/external/icons/ideal-issuers/ASNBNL21.svg',
                                                ],
                                        ],
                                    4 =>
                                        [
                                            'resource' => 'issuer',
                                            'id' => 'ideal_BUNQNL2A',
                                            'name' => 'bunq',
                                            'image' =>
                                                [
                                                    'size1x' => 'https://www.mollie.com/external/icons/ideal-issuers/BUNQNL2A.png',
                                                    'size2x' => 'https://www.mollie.com/external/icons/ideal-issuers/BUNQNL2A%402x.png',
                                                    'svg' => 'https://www.mollie.com/external/icons/ideal-issuers/BUNQNL2A.svg',
                                                ],
                                        ],
                                    5 =>
                                        [
                                            'resource' => 'issuer',
                                            'id' => 'ideal_HANDNL2A',
                                            'name' => 'Handelsbanken',
                                            'image' =>
                                                [
                                                    'size1x' => 'https://www.mollie.com/external/icons/ideal-issuers/HANDNL2A.png',
                                                    'size2x' => 'https://www.mollie.com/external/icons/ideal-issuers/HANDNL2A%402x.png',
                                                    'svg' => 'https://www.mollie.com/external/icons/ideal-issuers/HANDNL2A.svg',
                                                ],
                                        ],
                                    6 =>
                                        [
                                            'resource' => 'issuer',
                                            'id' => 'ideal_KNABNL2H',
                                            'name' => 'Knab',
                                            'image' =>
                                                [
                                                    'size1x' => 'https://www.mollie.com/external/icons/ideal-issuers/KNABNL2H.png',
                                                    'size2x' => 'https://www.mollie.com/external/icons/ideal-issuers/KNABNL2H%402x.png',
                                                    'svg' => 'https://www.mollie.com/external/icons/ideal-issuers/KNABNL2H.svg',
                                                ],
                                        ],
                                    7 =>
                                        [
                                            'resource' => 'issuer',
                                            'id' => 'ideal_MOYONL21',
                                            'name' => 'Moneyou',
                                            'image' =>
                                                [
                                                    'size1x' => 'https://www.mollie.com/external/icons/ideal-issuers/MOYONL21.png',
                                                    'size2x' => 'https://www.mollie.com/external/icons/ideal-issuers/MOYONL21%402x.png',
                                                    'svg' => 'https://www.mollie.com/external/icons/ideal-issuers/MOYONL21.svg',
                                                ],
                                        ],
                                    8 =>
                                        [
                                            'resource' => 'issuer',
                                            'id' => 'ideal_RBRBNL21',
                                            'name' => 'RegioBank',
                                            'image' =>
                                                [
                                                    'size1x' => 'https://www.mollie.com/external/icons/ideal-issuers/RBRBNL21.png',
                                                    'size2x' => 'https://www.mollie.com/external/icons/ideal-issuers/RBRBNL21%402x.png',
                                                    'svg' => 'https://www.mollie.com/external/icons/ideal-issuers/RBRBNL21.svg',
                                                ],
                                        ],
                                    9 =>
                                        [
                                            'resource' => 'issuer',
                                            'id' => 'ideal_SNSBNL2A',
                                            'name' => 'SNS',
                                            'image' =>
                                                [
                                                    'size1x' => 'https://www.mollie.com/external/icons/ideal-issuers/SNSBNL2A.png',
                                                    'size2x' => 'https://www.mollie.com/external/icons/ideal-issuers/SNSBNL2A%402x.png',
                                                    'svg' => 'https://www.mollie.com/external/icons/ideal-issuers/SNSBNL2A.svg',
                                                ],
                                        ],
                                    10 =>
                                        [
                                            'resource' => 'issuer',
                                            'id' => 'ideal_TRIONL2U',
                                            'name' => 'Triodos Bank',
                                            'image' =>
                                                [
                                                    'size1x' => 'https://www.mollie.com/external/icons/ideal-issuers/TRIONL2U.png',
                                                    'size2x' => 'https://www.mollie.com/external/icons/ideal-issuers/TRIONL2U%402x.png',
                                                    'svg' => 'https://www.mollie.com/external/icons/ideal-issuers/TRIONL2U.svg',
                                                ],
                                        ],
                                    11 =>
                                        [
                                            'resource' => 'issuer',
                                            'id' => 'ideal_FVLBNL22',
                                            'name' => 'van Lanschot',
                                            'image' =>
                                                [
                                                    'size1x' => 'https://www.mollie.com/external/icons/ideal-issuers/FVLBNL22.png',
                                                    'size2x' => 'https://www.mollie.com/external/icons/ideal-issuers/FVLBNL22%402x.png',
                                                    'svg' => 'https://www.mollie.com/external/icons/ideal-issuers/FVLBNL22.svg',
                                                ],
                                        ],
                                ],
                            'pricing' => NULL,
                            'status' => 'activated',
                            '_links' =>
                                [
                                    'self' =>
                                        [
                                            'href' => 'https://api.mollie.com/v2/methods/ideal',
                                            'type' => 'application/hal+json',
                                        ],
                                ],
                            'resource' => 'method',
                        ],
                    2 =>
                        (object)[
                            'id' => 'creditcard',
                            'description' => 'Credit card',
                            'minimumAmount' =>
                                [
                                    'value' => '0.01',
                                    'currency' => 'EUR',
                                ],
                            'maximumAmount' =>
                                [
                                    'value' => '5000.00',
                                    'currency' => 'EUR',
                                ],
                            'image' =>
                                [
                                    'size1x' => 'https://www.mollie.com/external/icons/payment-methods/creditcard.png',
                                    'size2x' => 'https://www.mollie.com/external/icons/payment-methods/creditcard%402x.png',
                                    'svg' => 'https://www.mollie.com/external/icons/payment-methods/creditcard.svg',
                                ],
                            'issuers' => NULL,
                            'pricing' => NULL,
                            'status' => 'activated',
                            '_links' =>
                                [
                                    'self' =>
                                        [
                                            'href' => 'https://api.mollie.com/v2/methods/creditcard',
                                            'type' => 'application/hal+json',
                                        ],
                                ],
                            'resource' => 'method',
                        ],
                    3 =>
                        (object)[
                            'id' => 'klarnapaylater',
                            'description' => 'Pay later.',
                            'minimumAmount' =>
                                [
                                    'value' => '0.01',
                                    'currency' => 'EUR',
                                ],
                            'maximumAmount' =>
                                [
                                    'value' => '10000.00',
                                    'currency' => 'EUR',
                                ],
                            'image' =>
                                [
                                    'size1x' => 'https://www.mollie.com/external/icons/payment-methods/klarnapaylater.png',
                                    'size2x' => 'https://www.mollie.com/external/icons/payment-methods/klarnapaylater%402x.png',
                                    'svg' => 'https://www.mollie.com/external/icons/payment-methods/klarnapaylater.svg',
                                ],
                            'issuers' => NULL,
                            'pricing' => NULL,
                            'status' => 'activated',
                            '_links' =>
                                [
                                    'self' =>
                                        [
                                            'href' => 'https://api.mollie.com/v2/methods/klarnapaylater',
                                            'type' => 'application/hal+json',
                                        ],
                                ],
                            'resource' => 'method',
                        ],
                ],
                'isSSLEnabled' => false,

            ]
        ];
    }
}
