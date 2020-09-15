$(document).ready(function () {
    var $voucherCategory = $('select[name="MOLLIE_VOUCHER_CATEGORY"]');
    handleVoucherSelectors($voucherCategory.val());

    $voucherCategory.on('change', function () {
        handleVoucherSelectors($(this).val());
    });

    function handleVoucherSelectors(selectedVoucherCategory) {
        switch (selectedVoucherCategory) {
            case 'null':
                handleVoucherSelectionNone();
                break;
            case 'meal':
            case 'gift':
            case 'eco':
                handleVoucherSelectionCategory();
                break;
            case 'custom':
                handleVoucherSelectionCustom();
                break;
            default:
                break;
        }
    }

    function handleVoucherSelectionNone() {
        toggleVoucherSelections(true, true);
    }

    function handleVoucherSelectionCategory() {
        toggleVoucherSelections(false, true);
    }

    function handleVoucherSelectionCustom() {
        toggleVoucherSelections(true, false);
    }

    function toggleVoucherSelections(isCategoryHidden, isAttributeHidden) {
        $('select[name="MOLLIE_VOUCHER_PRESTASHOP_CATEGORY"]').closest('div.form-group').toggleClass('hidden', isCategoryHidden);
        $('select[name="MOLLIE_VOUCHER_CUSTOM_ATTRIBUTE"]').closest('div.form-group').toggleClass('hidden', isAttributeHidden);
    }
});
