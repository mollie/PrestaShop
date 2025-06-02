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

$(document).ready(function () {
    displayPaymentFee();
    updateCartTotal();

    function displayPaymentFee() {
        var paymentFees = $('input[name="payment-fee-price-display"]');
        paymentFees.each(function () {
            var $prev = $(this).closest('.js-payment-option-form').prev();
            if ($prev.hasClass('additional-information')) {
                $prev.prev().find('label').append("<span class='h6'>" + $(this).val() + "</span>");
            } else {
                $prev.find('label').append("<span class='h6'>" + $(this).val() + "</span>");
            }
        });
    }

    function updateCartTotal() {
        const selectedPayment = $('input[name="payment-option"]:checked');
        if (selectedPayment.length === 0) {
            return;
        }

        const nextDiv = selectedPayment.closest('.payment-option').parent().next();
        let paymentMethodId = 0;

        if (nextDiv.hasClass('js-payment-option-form')) {
            paymentMethodId = nextDiv.find('input[name="payment-method-id"]').val();
        } else {
            paymentMethodId = nextDiv.next().find('input[name="payment-method-id"]').val();
        }


        $.ajax({
            url: ajaxUrl,
            method: 'GET',
            data: {
                paymentMethodId: paymentMethodId,
                ajax: 1,
                action: 'getTotalCartPrice'
            },
            success: function (response) {
                response = jQuery.parseJSON(response);

                if (response.error) {
                    console.error(response.message);
                    return;
                }

                $('.card-block.cart-summary-totals').replaceWith(response.cart_summary_totals);
            }
        });
    }

    $('input[name="payment-option"]').on('change', function () {
        updateCartTotal();
    });
});
