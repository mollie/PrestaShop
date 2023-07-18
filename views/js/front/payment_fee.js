/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 *
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 *
 * @see        https://github.com/mollie/PrestaShop
 */
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
        const $nextDiv = $(this).closest('.payment-option').parent().next();

        let paymentMethodId = 0;
        let $paymentMethodId;

        if ($nextDiv.hasClass('js-payment-option-form')) {
          $paymentMethodId = $nextDiv.find('input[name="payment-method-id"]');
        } else {
          $paymentMethodId = $nextDiv.next().find('input[name="payment-method-id"]');
        }

        if ($paymentMethodId.length > 0) {
          paymentMethodId = $paymentMethodId.val();
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
        })
    })
});
