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
    var paymentFeeClass = 'mollie-payment-fee';
    var totalCartPriceRequest = null;

    displayPaymentFee();
    updateCartTotal();

    // The one-page checkout renders the payment list after the page has loaded, replaces it on every
    // address, carrier or cart change and refreshes the summary and pay button with the total without the fee.
    if (window.prestashop && typeof window.prestashop.on === 'function') {
        $.each(['opcPaymentMethodsUpdated', 'opcPaymentMethodsRefreshed', 'opcCartSummaryUpdated'], function (index, eventName) {
            window.prestashop.on(eventName, function () {
                displayPaymentFee();
                updateCartTotal();
            });
        });
    }

    $(document).on('change', 'input[name="payment-option"]', function () {
        updateCartTotal();
    });

    // Classic, Hummingbird and the one-page checkout all render the option form as #pay-with-<option id>-form
    // and the option label as label[for=<option id>], while the markup between them differs per theme.
    function getPaymentOptionForm(paymentOptionId) {
        return $(document.getElementById('pay-with-' + paymentOptionId + '-form'));
    }

    function displayPaymentFee() {
        $('input[name="payment-option"]').each(function () {
            var paymentFee = getPaymentOptionForm(this.id).find('input[name="payment-fee-price-display"]');
            if (paymentFee.length === 0) {
                return;
            }

            var $label = $('label[for="' + this.id + '"]');
            if ($label.find('.' + paymentFeeClass).length) {
                return;
            }

            $label.append($('<span>', {'class': 'h6 ' + paymentFeeClass}).text(paymentFee.val()));
        });
    }

    function updateCartTotal() {
        if ($('input[name="payment-option"]').length === 0) {
            return;
        }

        // Without a selected option the request carries no method id and gets the total without a fee back,
        // which resets the summary after the one-page checkout re-renders the list and drops the selection.
        const selectedPayment = $('input[name="payment-option"]:checked');
        const paymentMethodId = getPaymentOptionForm(selectedPayment.attr('id')).find('input[name="payment-method-id"]').val();

        if (totalCartPriceRequest) {
            totalCartPriceRequest.abort();
        }

        totalCartPriceRequest = $.ajax({
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

                // PS 1.7.6 Classic renders the totals without the js-cart-summary-totals class.
                $('.js-cart-summary-totals, .card-block.cart-summary-totals').replaceWith(response.cart_summary_totals);

                if (response.cart_total) {
                    $('#opc-pay-amount').text(response.cart_total);
                }
            },
            complete: function () {
                totalCartPriceRequest = null;
            }
        });
    }
});
