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
$(document).ready(function () {
    var $paymentSelector = isPsVersion177 ? 'select[name="cart_summary[payment_module]"]' : 'select[name="payment_module_name"]';
    var displayHiddenClass = 'd-none hidden';
    var $paymentSelectInput = $($paymentSelector)

    $paymentSelectInput.ready(function () {
        $("#mollie-email-send-group").appendTo($paymentSelectInput.closest('div.form-group'));
        isMollie = isMolliePayment($($paymentSelector).val());
        toggleOrderStatus(isMollie);
    });

    $(document).on('change', $paymentSelector, function () {
        var selectedPayment = $(this).val();
        var isMollie = isMolliePayment(selectedPayment);
        toggleOrderStatus(isMollie);
    });

    function toggleOrderStatus(isMolliePayment) {
        var $molliePaymentCheckboxGroup = $('#mollie-email-send-group');
        var $orderStatusSelector = isPsVersion177 ? $('select[name="cart_summary[order_state]"]') : $('select[name="id_order_state"]');
        if (isMolliePayment) {
            $orderStatusSelector.closest('div.form-group').toggleClass(displayHiddenClass, true);
            $('#send_email_to_customer').toggleClass(displayHiddenClass, true);
            $molliePaymentCheckboxGroup.toggleClass(displayHiddenClass, false);
            $orderStatusSelector.val(molliePendingStatus);
        } else {
            $orderStatusSelector.closest('div.form-group').toggleClass(displayHiddenClass, false);
            $('#send_email_to_customer').toggleClass(displayHiddenClass, false);
            $molliePaymentCheckboxGroup.toggleClass(displayHiddenClass, true);
        }
    }

    function isMolliePayment(paymentName) {
        var isMollie = false;
        if (paymentName === 'mollie') {
            isMollie = true;
        }

        return isMollie;
    }
});