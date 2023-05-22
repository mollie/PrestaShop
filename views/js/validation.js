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
    var $paymentMethodsEnable = $('select[name^="MOLLIE_METHOD_ENABLED"]');
    $paymentMethodsEnable.each(function () {
            paymentMethodInputsToggle(this);
            paymentMethodOnChangeToggle(this);
            paymentMethodFeeToggle(this);
        }
    );

    $('#module_form').on('submit', function () {
        var description = $('#MOLLIE_DESCRIPTION');
        var selectedAPI = $('select[name="MOLLIE_API"]').val();
        if (!/\S/.test(description.val()) && selectedAPI === payment_api) {
            event.preventDefault();
            description.addClass('mollie-input-error');
            $('.alert.alert-success').hide();
            showErrorMessage(description_message);
        }

        $paymentMethodsEnable.each(validatePaymentMethod);
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
        if (!/\S/.test($description.val())) {
            event.preventDefault();
            $description.addClass('mollie-input-error');
            $('.alert.alert-success').hide();
            showErrorMessage(description_message);
        }

        var minAmount = $paymentMethodForm.find('input[name^="MOLLIE_METHOD_MIN_AMOUNT"]');
        var maxAmount = $paymentMethodForm.find('input[name^="MOLLIE_METHOD_MAX_AMOUNT"]');

        if (parseFloat(minAmount.val()) < parseFloat(minAmount.attr('min')) || parseFloat(minAmount.val()) > parseFloat(maxAmount.attr('max'))) {
            event.preventDefault();
            minAmount.addClass('mollie-input-error');
            $('.alert.alert-success').hide();
            showErrorMessage(min_amount_message);
        }

        if (parseFloat(maxAmount.val()) < parseFloat(minAmount.attr('min')) || parseFloat(maxAmount.val()) > parseFloat(maxAmount.attr('max'))) {
            event.preventDefault();
            maxAmount.addClass('mollie-input-error');
            $('.alert.alert-success').hide();
            showErrorMessage(max_amount_message);
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
        let $paymentMethodForm = $(method).closest('.payment-method');
        let $paymentFeeType = $paymentMethodForm.find('select[name^="MOLLIE_METHOD_SURCHARGE_TYPE"]');
        let $feeFixedTaxIncl = $paymentMethodForm.find('input[name^="MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_TAX_INCL"]');
        let $feeFixedTaxExcl = $paymentMethodForm.find('input[name^="MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_TAX_EXCL"]');
        let $taxRuleId = $paymentMethodForm.find('select[name^="MOLLIE_METHOD_TAX_RULE_ID"]');
        let $feePercentage = $paymentMethodForm.find('input[name^="MOLLIE_METHOD_SURCHARGE_PERCENTAGE"]');
        let $feeLimit = $paymentMethodForm.find('input[name^="MOLLIE_METHOD_SURCHARGE_LIMIT"]');

        switch ($paymentFeeType.val()) {
            case '0':
                $feeFixedTaxIncl.closest('.form-group').hide();
                $feeFixedTaxExcl.closest('.form-group').hide();
                $taxRuleId.closest('.form-group').hide();
                $feePercentage.closest('.form-group').hide();
                $feeLimit.closest('.form-group').hide();
                break;
            case '1':
                $feeFixedTaxIncl.closest('.form-group').show();
                $feeFixedTaxExcl.closest('.form-group').show();
                $taxRuleId.closest('.form-group').show();
                $feePercentage.closest('.form-group').hide();
                $feeLimit.closest('.form-group').hide();
                break;
            case '2':
                $feeFixedTaxIncl.closest('.form-group').hide();
                $feeFixedTaxExcl.closest('.form-group').hide();
                $taxRuleId.closest('.form-group').hide();
                $feePercentage.closest('.form-group').show();
                $feeLimit.closest('.form-group').show();
                break;
            case '3':
                $feeFixedTaxIncl.closest('.form-group').show();
                $feeFixedTaxExcl.closest('.form-group').show();
                $taxRuleId.closest('.form-group').show();
                $feePercentage.closest('.form-group').show();
                $feeLimit.closest('.form-group').show();
                break;
        }
    }
});
