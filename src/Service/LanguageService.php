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

use _PhpScoper5ea00cc67502b\Mollie\Api\Types\PaymentStatus;
use _PhpScoper5ea00cc67502b\Mollie\Api\Types\RefundStatus;
use Mollie;

class LanguageService
{
    /**
     * @var Mollie
     */
    private $module;

    public function __construct(Mollie $module)
    {
        $this->module = $module;
    }

    public function getLang() {
        return [
            PaymentStatus::STATUS_PAID => $this->module->l('Paid'),
            PaymentStatus::STATUS_AUTHORIZED => $this->module->l('Authorized'),
            PaymentStatus::STATUS_CANCELED => $this->module->l('Canceled'),
            PaymentStatus::STATUS_EXPIRED => $this->module->l('Expired'),
            RefundStatus::STATUS_REFUNDED => $this->module->l('Refunded'),
            PaymentStatus::STATUS_OPEN => $this->module->l('Bankwire pending'),
            Mollie\Config\Config::PARTIAL_REFUND_CODE => $this->module->l('Partially refunded'),
            'created' => $this->module->l('Created'),
            'This payment method is not available.' => $this->module->l('This payment method is not available.'),
            'Click here to continue' => $this->module->l('Click here to continue'),
            'This payment method is only available for Euros.' => $this->module->l('This payment method is only available for Euros.'),
            'There was an error while processing your request: ' => $this->module->l('There was an error while processing your request: '),
            'The order with this id does not exist.' => $this->module->l('The order with this id does not exist.'),
            'We have not received a definite payment status. You will be notified as soon as we receive a confirmation of the bank/merchant.' => $this->module->l('We have not received a definite payment status. You will be notified as soon as we receive a confirmation of the bank/merchant.'),
            'Unfortunately your payment was expired.' => $this->module->l('Unfortunately your payment was expired.'),
            'Thank you. Your payment has been received.' => $this->module->l('Thank you. Your payment has been received.'),
            'The transaction has an unexpected status.' => $this->module->l('The transaction has an unexpected status.'),
            'You are not authorised to see this page.' => $this->module->l('You are not authorised to see this page.'),
            'Continue shopping' => $this->module->l('Continue shopping'),
            'Welcome back' => $this->module->l('Welcome back'),
            'Select your bank:' => $this->module->l('Select your bank:'),
            'OK' => $this->module->l('OK'),
            'Different payment method' => $this->module->l('Different payment method'),
            'Pay with %s' => $this->module->l('Pay with %s'),
            'Refund this order' => $this->module->l('Refund this order'),
            'Mollie refund' => $this->module->l('Mollie refund'),
            'Refund order #%d through the Mollie API.' => $this->module->l('Refund order #%d through the Mollie API.'),
            'The order has been refunded!' => $this->module->l('The order has been refunded!'),
            'Mollie B.V. will transfer the money back to the customer on the next business day.' => $this->module->l('Mollie B.V. will transfer the money back to the customer on the next business day.'),
            'Awaiting Mollie payment' => $this->module->l('Awaiting Mollie payment'),
            'Mollie partially refunded' => $this->module->l('Mollie partially refunded'),
            'iDEAL' => $this->module->l('iDEAL'),
            'Cartes Bancaires' => $this->module->l('Cartes Bancaires'),
            'Credit card' => $this->module->l('Credit card'),
            'Bancontact' => $this->module->l('Bancontact'),
            'SOFORT Banking' => $this->module->l('SOFORT Banking'),
            'SEPA Direct Debit' => $this->module->l('SEPA Direct Debit'),
            'Belfius Pay Button' => $this->module->l('Belfius Pay Button'),
            'Bitcoin' => $this->module->l('Bitcoin'),
            'PODIUM Cadeaukaart' => $this->module->l('PODIUM Cadeaukaart'),
            'Gift cards' => $this->module->l('Gift cards'),
            'Bank transfer' => $this->module->l('Bank transfer'),
            'PayPal' => $this->module->l('PayPal'),
            'paysafecard' => $this->module->l('paysafecard'),
            'KBC/CBC Payment Button' => $this->module->l('KBC/CBC Payment Button'),
            'ING Home\'Pay' => $this->module->l('ING Home\'Pay'),
            'Giropay' => $this->module->l('Giropay'),
            'eps' => $this->module->l('eps'),
            'Pay later.' => $this->module->l('Pay later.'),
            'Slice it.' => $this->module->l('Slice it.'),
            'MyBank' => $this->module->l('MyBank'),
        ];
    }
}