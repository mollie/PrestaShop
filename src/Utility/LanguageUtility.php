<?php

namespace Mollie\Utility;

use _PhpScoper5ea00cc67502b\Mollie\Api\Types\PaymentStatus;
use _PhpScoper5ea00cc67502b\Mollie\Api\Types\RefundStatus;
use Mollie;

class LanguageUtility
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