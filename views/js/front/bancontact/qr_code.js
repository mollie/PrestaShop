$(document).ready(function () {
    var continueWithoutQr = false;
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
        if (continueWithoutQr) {
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
            continueWithoutQr = true;
            $(formSubmitEvent.target).submit();
        })
    }
});
