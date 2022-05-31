$(document).ready(function () {
    qr_code = null;
    $(document).on('change', 'input[data-module-name="mollie"]', function () {
        var paymentOption = $(this).attr('id');
        var $additionalInformation = $('#' + paymentOption + '-additional-information');

        var methodId = $additionalInformation.find('input[name="mollie-method-id"]').val();
        if (methodId !== 'bancontact') {
            return;
        }

        if (qr_code === null) {
            createBancontactTransaction();
        }

        $('#mollie-bancontact-modal').modal('show');
        checkForPaidTransaction();
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
                qr_code = response['qr_code'];
                $('#mollie-bancontact-qr-code').attr('src', qr_code);
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
});
