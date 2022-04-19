<?php

namespace Builder;

use Mollie\Builder\ApplePayDirect\ApplePayOrderBuilder;
use PHPUnit\Framework\TestCase;

class ApplePayProductBuilderTest extends TestCase
{
    /**
     * @dataProvider getTestProductData
     */
    public function testBuild(array $products, array $shippingContent, array $billingContent)
    {
        $builder = new ApplePayOrderBuilder();
        $applePayProduct = $builder->build($products, $shippingContent, $billingContent);

        $this->assertObjectHasAttribute('products', $applePayProduct);
        $this->assertObjectHasAttribute('shippingContent', $applePayProduct);
        $this->assertObjectHasAttribute('billingContent', $applePayProduct);
    }

    public function getTestProductData()
    {
        return [
            'basic order with 1 product' => [
                'product' => [
                    [
                        'id_product' => '5',
                        'id_product_attribute' => '19',
                        'id_customization' => '0',
                        'quantity_wanted' => '1',
                    ],
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
            ],
        ];
    }
}
