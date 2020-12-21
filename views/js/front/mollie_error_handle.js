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
    var hasHashTag = document.URL.indexOf('#');
    if (hasHashTag >= 0) {
        var hashTag = document.URL.substr(document.URL.indexOf('#')+1);
        parent.location.hash = '';
        $.ajax({
            url: ajaxUrl,
            method: 'GET',
            data: {
                'hashTag': hashTag,
                ajax: 1,
                action: 'displayCheckoutError'
            },
            success: function (response) {
                if (isPS17) {
                    $('#checkout-payment-step').prepend(response);
                } else {
                    $('#HOOK_PAYMENT').prepend(response);
                }
            }
        })
    }
});