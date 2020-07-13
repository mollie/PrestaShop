<?php

namespace Mollie\Service;

use Customer;
use Mail;
use Mollie;

class MailService
{
    /**
     * @var Mollie
     */
    private $module;

    public function __construct(Mollie $module)
    {
        $this->module = $module;
    }

    public function sendSecondChanceMail(Customer $customer, $checkoutUrl, $methodName)
    {
        Mail::send(
            $customer->id_lang,
            'mollie_payment',
            Mail::l('Order payment'),
            [
                '{checkoutUrl}' => $checkoutUrl,
                '{firstName}' => $customer->firstname,
                '{lastName}' => $customer->lastname,
                '{paymentMethod}' => $methodName
            ],
            $customer->email,
            null,
            null,
            null,
            null,
            null,
            $this->module->getLocalPath() . 'mails/'
        );
    }
}