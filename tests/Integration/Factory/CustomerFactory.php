<?php

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

        $customer->save();

        \Context::getContext()->customer = $customer;

        return $customer;
    }
}
