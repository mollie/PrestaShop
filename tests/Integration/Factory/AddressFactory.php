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

namespace Mollie\Tests\Integration\Factory;

class AddressFactory implements FactoryInterface
{
    public static function create(array $data = []): \Address
    {
        $address = new \Address(null, (int) \Configuration::get('PS_LANG_DEFAULT'));

        $address->firstname = $data['first_name'] ?? 'test-first-name';
        $address->lastname = $data['last_name'] ?? 'test-last-name';
        $address->country = $data['country'] ?? 'test-country';
        $address->id_country = $data['id_country'] ?? \Configuration::get('PS_COUNTRY_DEFAULT');
        $address->city = $data['city'] ?? 'test-city';
        $address->postcode = $data['postcode'] ?? '97222'; //max 12 chars
        $address->address1 = $data['address1'] ?? 'test-address1';
        $address->address2 = $data['address2'] ?? 'test-address2';
        $address->phone_mobile = $data['phone_mobile'] ?? '5555555'; //letters or symbols cause errors
        $address->alias = $data['alias'] ?? 'test-alias';
        $address->vat_number = $data['vat_number'] ?? 'test-vat';
        $address->company = $data['company'] ?? 'test-company';

        $address->save();

        return $address;
    }
}
