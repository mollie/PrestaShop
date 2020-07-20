<?php

namespace Mollie\Utility;

use Context;
use Customer;

class ContextUtility
{
    public static function setCustomerToContext(Context $context, Customer $customer)
    {
        $context->customer = $customer;
        $context->cookie->id_customer = (int) $customer->id;
        $context->cookie->customer_lastname = $customer->lastname;
        $context->cookie->customer_firstname = $customer->firstname;
        $context->cookie->logged = 1;
        $context->cookie->check_cgv = 1;
        $context->cookie->is_guest = $customer->isGuest();
        $context->cookie->passwd = $customer->passwd;
        $context->cookie->email = $customer->email;

        return $context;
    }
}