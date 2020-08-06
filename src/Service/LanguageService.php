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

use _PhpScoper5eddef0da618a\Mollie\Api\Types\OrderStatus;
use _PhpScoper5eddef0da618a\Mollie\Api\Types\PaymentStatus;
use _PhpScoper5eddef0da618a\Mollie\Api\Types\RefundStatus;
use Mollie;

class LanguageService
{
    const FILE_NAME = 'LanguageService';

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
            PaymentStatus::STATUS_PAID => $this->module->l('Paid', self::FILE_NAME),
            OrderStatus::STATUS_COMPLETED => $this->module->l('Completed', self::FILE_NAME),
            PaymentStatus::STATUS_AUTHORIZED => $this->module->l('Authorized', self::FILE_NAME),
            PaymentStatus::STATUS_CANCELED => $this->module->l('Canceled', self::FILE_NAME),
            PaymentStatus::STATUS_EXPIRED => $this->module->l('Expired', self::FILE_NAME),
            RefundStatus::STATUS_REFUNDED => $this->module->l('Refunded', self::FILE_NAME),
            PaymentStatus::STATUS_OPEN => $this->module->l('Bankwire pending', self::FILE_NAME),
            Mollie\Config\Config::PARTIAL_REFUND_CODE => $this->module->l('Partially refunded', self::FILE_NAME),
            'created' => $this->module->l('Created', self::FILE_NAME),
            'This payment method is not available.' => $this->module->l('This payment method is not available.', self::FILE_NAME),
            'Click here to continue' => $this->module->l('Click here to continue', self::FILE_NAME),
            'This payment method is only available for Euros.' => $this->module->l('This payment method is only available for Euros.', self::FILE_NAME),
            'There was an error while processing your request: ' => $this->module->l('There was an error while processing your request: ', self::FILE_NAME),
            'The order with this id does not exist.' => $this->module->l('The order with this id does not exist.', self::FILE_NAME),
            'We have not received a definite payment status. You will be notified as soon as we receive a confirmation of the bank/merchant.' => $this->module->l('We have not received a definite payment status. You will be notified as soon as we receive a confirmation of the bank/merchant.', self::FILE_NAME),
            'Unfortunately your payment was expired.' => $this->module->l('Unfortunately your payment was expired.', self::FILE_NAME),
            'Thank you. Your payment has been received.' => $this->module->l('Thank you. Your payment has been received.', self::FILE_NAME),
            'The transaction has an unexpected status.' => $this->module->l('The transaction has an unexpected status.', self::FILE_NAME),
            'You are not authorised to see this page.' => $this->module->l('You are not authorised to see this page.', self::FILE_NAME),
            'Continue shopping' => $this->module->l('Continue shopping', self::FILE_NAME),
            'Welcome back' => $this->module->l('Welcome back', self::FILE_NAME),
            'Select your bank:' => $this->module->l('Select your bank:', self::FILE_NAME),
            'OK' => $this->module->l('OK', self::FILE_NAME),
            'Different payment method' => $this->module->l('Different payment method', self::FILE_NAME),
            'Pay with %s' => $this->module->l('Pay with %s', self::FILE_NAME),
            'Refund this order' => $this->module->l('Refund this order', self::FILE_NAME),
            'Mollie refund' => $this->module->l('Mollie refund', self::FILE_NAME),
            'Refund order #%d through the Mollie API.' => $this->module->l('Refund order #%d through the Mollie API.', self::FILE_NAME),
            'The order has been refunded!' => $this->module->l('The order has been refunded!', self::FILE_NAME),
            'Mollie B.V. will transfer the money back to the customer on the next business day.' => $this->module->l('Mollie B.V. will transfer the money back to the customer on the next business day.', self::FILE_NAME),
            'Awaiting Mollie payment' => $this->module->l('Awaiting Mollie payment', self::FILE_NAME),
            'Mollie partially refunded' => $this->module->l('Mollie partially refunded', self::FILE_NAME),
            'iDEAL' => $this->module->l('iDEAL', self::FILE_NAME),
            'Cartes Bancaires' => $this->module->l('Cartes Bancaires', self::FILE_NAME),
            'Credit card' => $this->module->l('Credit card', self::FILE_NAME),
            'Bancontact' => $this->module->l('Bancontact', self::FILE_NAME),
            'SOFORT Banking' => $this->module->l('SOFORT Banking', self::FILE_NAME),
            'SEPA Direct Debit' => $this->module->l('SEPA Direct Debit', self::FILE_NAME),
            'Belfius Pay Button' => $this->module->l('Belfius Pay Button', self::FILE_NAME),
            'Bitcoin' => $this->module->l('Bitcoin', self::FILE_NAME),
            'PODIUM Cadeaukaart' => $this->module->l('PODIUM Cadeaukaart', self::FILE_NAME),
            'Gift cards' => $this->module->l('Gift cards', self::FILE_NAME),
            'Bank transfer' => $this->module->l('Bank transfer', self::FILE_NAME),
            'PayPal' => $this->module->l('PayPal', self::FILE_NAME),
            'paysafecard' => $this->module->l('paysafecard', self::FILE_NAME),
            'KBC/CBC Payment Button' => $this->module->l('KBC/CBC Payment Button', self::FILE_NAME),
            'ING Home\'Pay' => $this->module->l('ING Home\'Pay', self::FILE_NAME),
            'Giropay' => $this->module->l('Giropay', self::FILE_NAME),
            'eps' => $this->module->l('eps', self::FILE_NAME),
            'Pay later.' => $this->module->l('Pay later.', self::FILE_NAME),
            'Slice it.' => $this->module->l('Slice it.', self::FILE_NAME),
            'MyBank' => $this->module->l('MyBank', self::FILE_NAME),
            'Completed' => $this->module->l('Completed', self::FILE_NAME),
        ];
    }

    public function lang($str)
    {
        $lang = $this->getLang();
        if (array_key_exists($str, $lang)) {
            return $lang[$str];
        }

        return $str;
    }
}