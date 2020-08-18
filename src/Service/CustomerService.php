<?php
/**
 * Copyright (c) 2012-2020, Mollie B.V.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * - Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR AND CONTRIBUTORS ``AS IS'' AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE AUTHOR OR CONTRIBUTORS BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
 * OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH
 * DAMAGE.
 *
 * @author     Mollie B.V. <info@mollie.nl>
 * @copyright  Mollie B.V.
 * @license    Berkeley Software Distribution License (BSD-License 2) http://www.opensource.org/licenses/bsd-license.php
 * @category   Mollie
 * @package    Mollie
 * @link       https://www.mollie.nl
 * @codingStandardsIgnoreStart
 */

namespace Mollie\Service;

use _PhpScoper5eddef0da618a\Mollie\Api\Types\PaymentMethod;
use Cart;
use Mollie;
use Mollie\Config\Config;
use Mollie\Exception\MollieException;
use Mollie\Repository\MolCustomerRepository;

class CustomerService
{
    /**
     * @var Mollie
     */
    private $mollie;

    /**
     * @var MolCustomerRepository
     */
    private $customerRepository;

    public function __construct(Mollie $mollie, MolCustomerRepository $customerRepository)
    {
        $this->mollie = $mollie;
        $this->customerRepository = $customerRepository;
    }

    public function processCustomerCreation(Cart $cart, $method)
    {
        if (!$this->isSingleCLickPaymentEnabled($method)) {
            return false;
        }

        $customer = new \Customer($cart->id_customer);

        $fullName = "{$customer->firstname} {$customer->lastname}";
        $molCustomer = $this->customerRepository->findOneBy(
            [
                'name' => $fullName,
                'email' => $customer->email
            ]
        );

        if ($molCustomer) {
            return $this->mollie->api->customers->get($molCustomer->customer_id);
        }

        $mollieCustomer = $this->createCustomer($fullName, $customer->email);

        $molCustomer = new \MolCustomer();
        $molCustomer->name = $fullName;
        $molCustomer->email = $customer->email;
        $molCustomer->customer_id = $mollieCustomer->id;
        $molCustomer->created_at = $mollieCustomer->createdAt;

        $molCustomer->add();

        return $mollieCustomer;
    }

    public function createCustomer($name, $email)
    {
        try {
            return $this->mollie->api->customers->create(
                [
                    'name' => $name,
                    'email' => $email
                ]
            );
        } catch (\Exception $e) {
            throw new MollieException(
                'Failed to create Mollie customer',
                MollieException::CUSTOMER_EXCEPTION,
                $e
            );
        }
    }

    public function isSingleCLickPaymentEnabled($method)
    {
        if ($method !== PaymentMethod::CREDITCARD) {
            return false;
        }
        $isComponentEnabled = \Configuration::get(Config::MOLLIE_IFRAME);
        $isSingleClickPaymentEnabled = \Configuration::get(Config::MOLLIE_SINGLE_CLICK_PAYMENT);
        if (!$isComponentEnabled && $isSingleClickPaymentEnabled) {
            return true;
        }

        return false;
    }
}