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

namespace Builder;

use Mollie\Builder\ApplePayDirect\ApplePayOrderBuilder;
use Mollie\Builder\ApplePayDirect\ApplePayProductBuilder;
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

    public function testBuildMapsShippingPhoneNumber()
    {
        $contact = [
            'addressLines' => ['Teststraße 1'],
            'administrativeArea' => '',
            'country' => 'Germany',
            'countryCode' => 'DE',
            'familyName' => 'Doe',
            'givenName' => 'John',
            'locality' => 'Berlin',
            'postalCode' => '10115',
        ];

        $builder = new ApplePayOrderBuilder();
        $order = $builder->build([], $contact + ['phoneNumber' => '+49 30 1234567'], $contact);

        $this->assertSame('+49 30 1234567', $order->getShippingContent()->getPhoneNumber());
        $this->assertSame('', $order->getBillingContent()->getPhoneNumber());
    }

    public function testProductBuilderKeepsTheCustomizationId()
    {
        $products = (new ApplePayProductBuilder())->build([
            ['id_product' => '19', 'id_product_attribute' => '0', 'id_customization' => '4', 'quantity_wanted' => '2'],
            ['id_product' => '5', 'id_product_attribute' => '19', 'quantity_wanted' => '1'],
        ]);

        $this->assertSame(4, $products[0]->getCustomizationId());
        $this->assertSame(0, $products[1]->getCustomizationId());
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
                        0 => 'Teststraße 1',
                    ],
                    'administrativeArea' => '',
                    'country' => 'Germany',
                    'countryCode' => 'DE',
                    'emailAddress' => 'john.doe@example.com',
                    'familyName' => 'Doe',
                    'givenName' => 'John',
                    'locality' => 'Berlin',
                    'phoneticFamilyName' => '',
                    'phoneticGivenName' => '',
                    'postalCode' => '10115',
                    'subAdministrativeArea' => '',
                    'subLocality' => '',
                ],
                'billingContact' => [
                    'addressLines' => [
                        0 => 'Teststraße 1',
                    ],
                    'administrativeArea' => '',
                    'country' => 'Germany',
                    'countryCode' => 'DE',
                    'familyName' => 'Doe',
                    'givenName' => 'John',
                    'locality' => 'Berlin',
                    'phoneticFamilyName' => '',
                    'phoneticGivenName' => '',
                    'postalCode' => '10115',
                    'subAdministrativeArea' => '',
                    'subLocality' => '',
                ],
            ],
        ];
    }
}
