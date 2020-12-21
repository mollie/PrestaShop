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
    var $paymentMethodsEnable = $('select[name^="MOLLIE_METHOD_ENABLED"]');
    $paymentMethodsEnable.each(function () {
            paymentMethodInputsToggle(this);
            paymentMethodOnChangeToggle(this);
            paymentMethodFeeToggle(this);
        }
    );

    $('#module_form').on('submit', function () {
        var description = $('#MOLLIE_DESCRIPTION');
        var isProfileChecked = $('input[name="MOLLIE_IFRAME"]').prop('checked');
        var profile = $('#MOLLIE_PROFILE_ID');
        var selectedAPI = $('select[name="MOLLIE_API"]').val();
        if (description.val() === '' && selectedAPI === payment_api) {
            event.preventDefault();
            description.addClass('mollie-input-error');
            $('.alert.alert-success').hide();
            showErrorMessage(description_message);
        }
        if (isProfileChecked && profile.val() === '') {
            event.preventDefault();
            profile.addClass('mollie-input-error');
            $('.alert.alert-success').hide();
            showErrorMessage(profile_id_message_empty);
            return;
        }

        if (isProfileChecked && profile.val().substring(0, 4) !== 'pfl_') {
            event.preventDefault();
            profile.addClass('mollie-input-error');
            $('.alert.alert-success').hide();
            showErrorMessage(profile_id_message);
        }

        $paymentMethodsEnable.each(validatePaymentMethod);
    });

    var $profileSwitch = $('input[name="MOLLIE_IFRAME"]');
    var $singleClickPayment = $('input[name="MOLLIE_SINGLE_CLICK_PAYMENT"]');

    hideElementIfChecked($profileSwitch, $singleClickPayment);
    $profileSwitch.on('change', function () {
        hideElementIfChecked($profileSwitch, $singleClickPayment);
    });

    var $automaticallyShipSwitch = $('input[name="MOLLIE_AS_MAIN"]');
    var $statusesContainer = $('#MOLLIE_AS_STATUSES_container');
    hideElementIfNotChecked($automaticallyShipSwitch, $statusesContainer);
    $automaticallyShipSwitch.on('change', function () {
        hideElementIfNotChecked($automaticallyShipSwitch, $statusesContainer);
    });

    var $enableCountriesSwitch = $('input[name="MOLLIE_METHOD_COUNTRIES"]');
    var $showCountriesSwitch = $('input[name="MOLLIE_METHOD_COUNTRIES_DISPLAY"]');
    hideElementIfNotChecked($enableCountriesSwitch, $showCountriesSwitch);
    $enableCountriesSwitch.on('change', function () {
        hideElementIfNotChecked($enableCountriesSwitch, $showCountriesSwitch);
    });

    function hideElementIfNotChecked($switch, $elementToHide) {
        if ($switch.prop('checked')) {
            $elementToHide.closest('.form-group').show();
        } else {
            $elementToHide.closest('.form-group').hide();
        }
    }
    function hideElementIfChecked($switch, $elementToHide) {
        if ($switch.prop('checked')) {
            $elementToHide.closest('.form-group').hide();
        } else {
            $elementToHide.closest('.form-group').show();
        }
    }

    function validatePaymentMethod() {
        var $paymentMethodForm = $(this).closest('.payment-method');

        var $isPaymentEnabled = $paymentMethodForm.find('select[name^="MOLLIE_METHOD_ENABLED"]');
        if ($isPaymentEnabled.val() === "0") {
            return;
        }
        var $description = $paymentMethodForm.find('input[name^="MOLLIE_METHOD_DESCRIPTION"]');
        if ($description.val() === '') {
            event.preventDefault();
            $description.addClass('mollie-input-error');
            $('.alert.alert-success').hide();
            showErrorMessage(description_message);
        }
    }

    function paymentMethodOnChangeToggle(method) {
        var $paymentMethodForm = $(method).closest('.payment-method');
        var $countrySelectType = $paymentMethodForm.find('select[name^="MOLLIE_METHOD_APPLICABLE_COUNTRIES"]');
        $countrySelectType.on('change', function () {
                paymentMethodInputsToggle(method);
            }
        );
        var $paymentFeeType = $paymentMethodForm.find('select[name^="MOLLIE_METHOD_SURCHARGE_TYPE"]');
        $paymentFeeType.on('change', function () {
                paymentMethodFeeToggle(method);
            }
        );
    }

    function paymentMethodInputsToggle(method) {
        var $paymentMethodForm = $(method).closest('.payment-method');
        var $countrySelectType = $paymentMethodForm.find('select[name^="MOLLIE_METHOD_APPLICABLE_COUNTRIES"]');
        var $countrySelect = $paymentMethodForm.find('select[name^="MOLLIE_METHOD_CERTAIN_COUNTRIES"]');
        var $excludedCountrySelect = $paymentMethodForm.find('select[name^="MOLLIE_METHOD_EXCLUDE_CERTAIN_COUNTRIES"]');
        if ($countrySelectType.val() === "1") {
            $countrySelect.closest('.form-group').show();
            $excludedCountrySelect.closest('.form-group').hide();
        } else {
            $countrySelect.closest('.form-group').hide();
            $excludedCountrySelect.closest('.form-group').show();
        }
    }

    function paymentMethodFeeToggle(method) {
        var $paymentMethodForm = $(method).closest('.payment-method');
        var $paymentFeeType = $paymentMethodForm.find('select[name^="MOLLIE_METHOD_SURCHARGE_TYPE"]');
        var $feeFixed = $paymentMethodForm.find('input[name^="MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT"]');
        var $feePercentage = $paymentMethodForm.find('input[name^="MOLLIE_METHOD_SURCHARGE_PERCENTAGE"]');
        var $feeLimit = $paymentMethodForm.find('input[name^="MOLLIE_METHOD_SURCHARGE_LIMIT"]');
        switch ($paymentFeeType.val()) {
            case '0':
                $feeFixed.closest('.form-group').hide();
                $feePercentage.closest('.form-group').hide();
                $feeLimit.closest('.form-group').hide();
                break;
            case '1':
                $feeFixed.closest('.form-group').show();
                $feePercentage.closest('.form-group').hide();
                $feeLimit.closest('.form-group').show();
                break;
            case '2':
                $feeFixed.closest('.form-group').hide();
                $feePercentage.closest('.form-group').show();
                $feeLimit.closest('.form-group').show();
                break;
            case '3':
                $feeFixed.closest('.form-group').show();
                $feePercentage.closest('.form-group').show();
                $feeLimit.closest('.form-group').show();
                break;
        }
    }
});
