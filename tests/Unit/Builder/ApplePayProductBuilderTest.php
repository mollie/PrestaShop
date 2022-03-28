<?php

namespace Builder;

use Mollie\Builder\ApplePayDirect\ApplePayOrderBuilder;
use PHPUnit\Framework\TestCase;

class ApplePayProductBuilderTest extends TestCase
{
    /**
     * @dataProvider getTestProductData
     */
    public function testBuild(array $productInfo)
    {
        $builder = new ApplePayOrderBuilder();
        $applePayProduct = $builder->build($productInfo);
    }

    public function getTestProductData()
    {
        yield [
            'basic order with 1 product' => [
                'action' => 'mollie_apple_pay_create_order',
                'product' => [
                        'id_product' => '5',
                        'id_product_attribute' => '19',
                        'id_customization' => '0',
                        'quantity_wanted' => '1',
                    ],
                'shippingContact' => [
                        'addressLines' => [
                                0 => 'Žemaičių gatvė 36',
                            ],
                        'administrativeArea' => '',
                        'country' => 'Lithuania',
                        'countryCode' => 'LT',
                        'emailAddress' => 'marius.gudauskis@invertus.eu',
                        'familyName' => 'Gudauskis',
                        'givenName' => 'Marius',
                        'locality' => 'Kaunas',
                        'phoneticFamilyName' => '',
                        'phoneticGivenName' => '',
                        'postalCode' => '44174',
                        'subAdministrativeArea' => '',
                        'subLocality' => '',
                    ],
                'billingContact' => [
                        'addressLines' => [
                                0 => 'Žemaičių gatvė 36',
                            ],
                        'administrativeArea' => '',
                        'country' => 'Lithuania',
                        'countryCode' => 'LT',
                        'familyName' => 'Gudauskis',
                        'givenName' => 'Marius',
                        'locality' => 'Kaunas',
                        'phoneticFamilyName' => '',
                        'phoneticGivenName' => '',
                        'postalCode' => '44174',
                        'subAdministrativeArea' => '',
                        'subLocality' => '',
                    ],
                'token' => [
                        'paymentData' => [
                                'data' => 'HPQ/hlAxyrw/WtaEJRC6CzeMdeobnXAg3GJkpxjhlWyEgMxXzNnIuA7zu99HJUqHgEBLwmBvd11yYxHOfqbC6mFNJF9o76NTzvrD5lB/W4NSQbJfA00f+8V3PdytA623M4oqqoRJBMQROJ1rVxBiC34xu4VImK7ul7jox7+3kTwNU3DuCUSo5/EGkhUobzvmG5vgFn03pPZVjj/OpelMoBAGvQLCRM4FfA5BNseL4mL1JSS1varQ28oWUWB0tc8GvH/eLeWMCMlxH5mvv/SCfUQqXarKKIuayl1egJy5Wwsgaf7jY0WeyCg8HUCiXnKB8ii98A0iNZ0J53J+6Nm2KRXRy1RKyP6MoQs3OxTc5zxbAAjvmaDH0NnEWAfvjcSAw4dUfaW8aW2uHCZ4BfG8JLe9k/VDaHHnD/DrrtM=',
                                'signature' => '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',
                                'header' => [
                                        'publicKeyHash' => 'lzTX+Ff9RaY69YL20QcKON7A9O6cmF1b72cFLTVEBqw=',
                                        'ephemeralPublicKey' => 'MFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAExAsk12iESfyp3YQN+FFdoy3pzmM4UlMR0FJ0uKHlCHGx1EDEehQQeT3KxShMNVTwlVPK+5ARzVN2pJ9j4gTXAw==',
                                        'transactionId' => 'c7a4526f045801fc522fb1e3a458d79bbc5ebc7416b8fea92912114f35f1f846',
                                    ],
                                'version' => 'EC_v1',
                            ],
                        'paymentMethod' => [
                                'displayName' => 'Visa 0540',
                                'network' => 'Visa',
                                'type' => 'debit',
                            ],
                        'transactionIdentifier' => 'C7A4526F045801FC522FB1E3A458D79BBC5EBC7416B8FEA92912114F35F1F846',
                    ],
                'billing_first_name' => 'Marius',
                'billing_last_name' => 'Gudauskis',
                'billing_company' => '',
                'billing_country' => 'LT',
                'billing_address_1' => 'Žemaičių gatvė 36',
                'billing_address_2' => '',
                'billing_postcode' => '44174',
                'billing_city' => 'Kaunas',
                'billing_state' => '',
                'billing_phone' => '000000000000',
                'billing_email' => 'marius.gudauskis@invertus.eu',
                'shipping_first_name' => 'Marius',
                'shipping_last_name' => 'Gudauskis',
                'shipping_company' => '',
                'shipping_country' => 'LT',
                'shipping_address_1' => 'Žemaičių gatvė 36',
                'shipping_address_2' => '',
                'shipping_postcode' => '44174',
                'shipping_city' => 'Kaunas',
                'shipping_state' => '',
                'shipping_phone' => '000000000000',
                'shipping_email' => 'marius.gudauskis@invertus.eu',
                'order_comments' => '',
                'payment_method' => 'mollie_wc_gateway_applepay',
                '_wp_http_referer' => '/?wc-ajax=update_order_review',
            ],
        ];
    }
}
