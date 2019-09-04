function toggleCountries(showCountries)
{
    if (showCountries.attr('checked')) {
        $('div.js-country').closest('.form-group').show();
    } else {
        $('div.js-country').closest('.form-group').fadeOut('slow');
    }
}

$(() => {
    jQuery('.chosen').chosen("destroy");
    jQuery('.chosen').chosen({inherit_select_classes: true});

    $('select').on('change', function () {
        const selectedValues = $(this).val();
        if (jQuery.inArray('0', selectedValues) !== -1) {
            $(this).val('0');
            $('select').trigger("chosen:updated");
        }
    });

    const countriesSwitch = $('input[name="MOLLIE_METHOD_COUNTRIES_DISPLAY"]');
    toggleCountries(countriesSwitch);

    countriesSwitch.on('change', function () {
        toggleCountries(countriesSwitch);
    })
});