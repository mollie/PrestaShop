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
    displayPaymentFee();
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

    $('input[name="payment-option"]').on('change', function () {
        var $nextDiv = $(this).closest('.payment-option').parent().next();
        var paymentFee;
        if ($nextDiv.hasClass('js-payment-option-form')) {
            paymentFee = $nextDiv.find('input[name="payment-fee-price"]').val();
        } else {
            paymentFee = $nextDiv.next().find('input[name="payment-fee-price"]').val();
        }

        $.ajax({
            url: ajaxUrl,
            method: 'GET',
            data: {
                'paymentFee': paymentFee,
                ajax: 1,
                action: 'getTotalCartPrice'
            },
            success: function (response) {
                response = jQuery.parseJSON(response);
                $('.card-block.cart-summary-totals').replaceWith(response.cart_summary_totals);
            }
        })
    })
});