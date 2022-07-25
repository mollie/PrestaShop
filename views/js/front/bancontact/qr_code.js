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
    var paymentMethodInput = $('input[name="mollie-method-id"]');
    paymentMethodInput.closest('form').on('submit', function (e) {
        var selectedPayment = $('input[name="payment-option"]:checked');
        var isMollie = selectedPayment.attr('data-module-name') === 'mollie'
        if (!isMollie) {
            return;
        }
        var $nextDiv = selectedPayment.closest('.payment-option').parent().next();
        var mollieMethodName = $nextDiv.find('input[name="mollie-method-id"]').val();
        if (mollieMethodName !== 'bancontact') {
            return;
        }
        e.preventDefault();

        $('#payment-confirmation').find('button[type=submit]').prop("disabled", false);
        createBancontactTransaction();

        $('#mollie-bancontact-modal').modal('show');
        checkForPaidTransaction(e);
        continueWithoutQR(e)
    });

    function createBancontactTransaction()
    {
        $.ajax({
            url: bancontactAjaxUrl,
            method: 'GET',
            data: {
                ajax: 1,
                action: 'createTransaction'
            },
            success: function (response) {
                response = jQuery.parseJSON(response);
                $('#mollie-bancontact-qr-code').attr('src', response['qr_code']);
            }
        })
    }

    function checkForPaidTransaction()
    {
        $.ajax({
            url: bancontactAjaxUrl,
            method: 'GET',
            data: {
                ajax: 1,
                timeout: 0,
                action: 'checkForPaidTransaction'
            },
            success: function (response) {
                response = jQuery.parseJSON(response);
                if (response['success']) {
                    window.location.href = response['redirectUrl']
                }
                $('#mollie-bancontact-modal').modal('hide');
            }
        })
    }

    function continueWithoutQR(formSubmitEvent)
    {
        $('#js-mollie-bancontact-continue').on('click', function (){
            window.location.href = $(formSubmitEvent.target).attr('action');
        })
    }
});
