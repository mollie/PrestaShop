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
    disableCharactersInAmountInput();
    handleDisableForCustomUrl();
    handleRequiredApiKey();
    handleApiKeyVisibility();
    handleApplePayButtonStylesToggle();
    handleApiKeyChanges();

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

    function handleApplePayButtonStylesToggle()
    {
        let $applePayButtonStyles = $('#js-mollie-applepay-button-styles');
        let $applePayDirectProductEnableSelector = $('select[name^="MOLLIE_APPLE_PAY_DIRECT_PRODUCT_ENABLED"]');
        let $applePayDirectCartEnableSelector = $('select[name^="MOLLIE_APPLE_PAY_DIRECT_CART_ENABLED"]');

        toggleElement(
          $applePayButtonStyles,
          $applePayDirectProductEnableSelector.val() === '1' || $applePayDirectCartEnableSelector.val() === '1'
        )

        $applePayDirectProductEnableSelector.add($applePayDirectCartEnableSelector).on('change', function() {
          let isEnabled = $applePayDirectProductEnableSelector.val() === '1' || $applePayDirectCartEnableSelector.val() === '1';

          toggleElement($applePayButtonStyles, isEnabled)
        })
    }

    function toggleElement(element, isShown)
    {
        if (isShown) {
            element.show();
        } else {
            element.hide();
        }
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

function handleApiKeyChanges()
{
    $('select[name="MOLLIE_ENVIRONMENT"], input[name="MOLLIE_API_KEY_TEST"], input[name="MOLLIE_API_KEY"]').on('change', function () {
        $('input').not('input[name="MOLLIE_API_KEY_TEST"], input[name="MOLLIE_API_KEY"], input[name="MOLLIE_ENV_CHANGED"], input[name="MOLLIE_ACCOUNT_SWITCH"]').attr('disabled', true);
        $('select').not('select[name="MOLLIE_ENVIRONMENT"]').attr('disabled', true).trigger("chosen:updated");
        $('.js-mollie-save-warning').removeClass('hidden');
        $('input[name="MOLLIE_ENV_CHANGED"]').val(1);
    });
}
