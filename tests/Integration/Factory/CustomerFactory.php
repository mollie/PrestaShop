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

class CustomerFactory implements FactoryInterface
{
    public static function create(array $data = []): \Customer
    {
        $customer = new \Customer();

        $customer->firstname = $data['first_name'] ?? 'test-first-name';
        $customer->lastname = $data['last_name'] ?? 'test-last-name';
        $customer->email = $data['email'] ?? 'test-email@email.com';
        $customer->passwd = $data['passwd'] ?? 'test-passwd';
        $customer->is_guest = $data['is_guest'] ?? false;
        $customer->siret = $data['siret'] ?? 'test-siret';
        $customer->id_lang = $data['id_lang'] ?? \Context::getContext()->language->id;
        $customer->id_shop = $data['id_shop'] ?? \Context::getContext()->shop->id;

        $customer->save();

        \Context::getContext()->customer = $customer;

        return $customer;
    }
}
