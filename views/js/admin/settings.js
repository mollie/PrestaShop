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
    disableCharactersInAmountInput();
    handleDisableForCustomUrl();
    handleRequiredApiKey();
    handleRequiredProfileId();
    handlePaymentMethodDescriptions();
    handleApiKeyVisibility();

    function disableCharactersInAmountInput() {
        $('.js-mollie-amount').keypress(function (event) {
            if ((event.which != 46 || $(this).val().indexOf('.') != -1) && (event.which < 48 || event.which > 57)) {
                event.preventDefault();
            }
        });
    }

    function handleDisableForCustomUrl() {
        $('select[name^="MOLLIE_CARRIER_URL_SOURCE"]').on('change', function () {
            var customUrlDisabled = true;
            if ($(this).val() === 'custom_url') {
                customUrlDisabled = false;
            }
            $(this).closest('tr').find('input').attr('disabled', customUrlDisabled);
        });
    }

    function handleRequiredApiKey() {
        toggleRequiredApiKey($('select[name^="MOLLIE_ENVIRONMENT"]').val());
        $('select[name^="MOLLIE_ENVIRONMENT"]').on('change', function () {
            var selectedEnvironment = $(this).val();
            toggleRequiredApiKey(selectedEnvironment);
        });
    }

    function handleRequiredProfileId() {
        var $profileSwitch = $('input[name="MOLLIE_IFRAME"]');
        var isProfileIdRequired = $profileSwitch.prop('checked');
        $('.js-api-profile-id').find('label.control-label').toggleClass('required', isProfileIdRequired);

        $profileSwitch.on('change', function () {
            var isProfileIdRequired = $profileSwitch.prop('checked');
            $('.js-api-profile-id').find('label.control-label').toggleClass('required', isProfileIdRequired);
        });
    }

    function handlePaymentMethodDescriptions() {
        var $apiPaymentMethodSelect = $('select[name^="MOLLIE_METHOD_API"]');

        $apiPaymentMethodSelect.each(function () {
            togglePaymentMethodDescription($(this));
        });

        $apiPaymentMethodSelect.on('change', function () {
            togglePaymentMethodDescription($(this));
        });
    }

    function togglePaymentMethodDescription(apiPaymentMethodSelect) {
        if (apiPaymentMethodSelect.val() === 'payments') {
            apiPaymentMethodSelect.closest('.payment-method').find('.payment-api-description').slideDown();
        } else {
            apiPaymentMethodSelect.closest('.payment-method').find('.payment-api-description').slideUp();
        }
    }

    function toggleRequiredApiKey(selectedEnvironment) {
        var isLive = false;
        if (selectedEnvironment === "1") {
            isLive = true;
        }
        $('.js-test-api-group').find('label.control-label').toggleClass('required', !isLive);
        $('.js-live-api-group').find('label.control-label').toggleClass('required', isLive);
    }

    function handleApiKeyVisibility() {
        $('button[data-action="show-password"]').on('click', function () {
            var elm = $(this).closest('.input-group').children('input.js-visible-password');
            if (elm.attr('type') === 'password') {
                elm.attr('type', 'text');
                $(this).text($(this).data('textHide'));
            } else {
                elm.attr('type', 'password');
                $(this).text($(this).data('textShow'));
            }
        });
    }
});

function togglePaymentMethod($button, paymentId) {
    var $clickedButton = $($button);
    $.ajax(ajaxUrl, {
        method: 'POST',
        data: {
            'paymentMethod': paymentId,
            'status': $clickedButton.data('action'),
            'action': 'togglePaymentMethod',
            'ajax': 1
        },
        success: function (response) {
            response = JSON.parse(response);
            var checkInputClass = 'icon-check text-success';
            var clearInputClass = 'icon-remove text-danger';
            if (response.success) {
                if (response.paymentStatus) {
                    $clickedButton.data('action', 'deactivate');
                    $clickedButton.find('i').removeClass(clearInputClass).addClass(checkInputClass);
                } else {
                    $clickedButton.data('action', 'activate');
                    $clickedButton.find('i').removeClass(checkInputClass).addClass(clearInputClass);
                }

                if (response.paymentStatus !== undefined) {
                    $clickedButton.closest('.payment-method').find('select[name^="MOLLIE_METHOD_ENABLED"] option[value="' + response.paymentStatus + '"]').prop('selected', true);

                }
            }
        }
    })
}
