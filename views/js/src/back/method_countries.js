$(() => {
    $('select').on('change', function () {
        const selectedValues = $(this).val();
        if (jQuery.inArray('0', selectedValues) !== -1) {
            $(this).val('0');
            $('select').trigger("chosen:updated");
        }
    })
});