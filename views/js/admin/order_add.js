/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @link        https://github.com/mollie/PrestaShop
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 * @codingStandardsIgnoreStart
 */
$(document).ready(function () {
    var $paymentSelector = isPsVersion177 ? 'select[name="cart_summary[payment_module]"]' : 'select[name="payment_module_name"]';
    var displayHiddenClass = 'd-none hidden';
    var $paymentSelectInput = $($paymentSelector)

    $paymentSelectInput.ready(function () {
        $("#mollie-email-send-group").appendTo($paymentSelectInput.closest('div.form-group'));
        isMollie = isMolliePayment($paymentSelectInput.val());
        toggleOrderStatus(isMollie);
    });

    $(document).on('change', $paymentSelector, function () {
        var selectedPayment = $(this).val();
        var isMollie = isMolliePayment(selectedPayment);
        toggleOrderStatus(isMollie);
    });

    function toggleOrderStatus(isMolliePayment) {
        var $molliePaymentCheckboxGroup = $('#mollie-email-send-group');
        var $orderStatusSelector = $('select[name="cart_summary[order_state]"],select[name="id_order_state"]');
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