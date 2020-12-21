/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @link        https://github.com/mollie/PrestaShop
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 * @codingStandardsIgnoreStart
 */

function toggleCountries(showCountries, countriesEnabledSwitch)
{
    if (showCountries.attr('checked') && countriesEnabledSwitch.attr('checked')) {
        $('div.js-country').closest('.form-group').show();
    } else {
        $('div.js-country').closest('.form-group').fadeOut('slow');
    }
}

$(document).ready(function() {
    jQuery('.chosen').chosen("destroy");
    jQuery('.chosen').chosen({inherit_select_classes: true});

    $('select.mollie-chosen').on('change', function () {
        var selectedValues = $(this).val();
        if (jQuery.inArray('0', selectedValues) !== -1) {
            $(this).val('0');
            $('select').trigger("chosen:updated");
        }
    });


    var $countriesEnabledSwitch = $('input[name="MOLLIE_METHOD_COUNTRIES"]');
    var $countriesSwitch = $('input[name="MOLLIE_METHOD_COUNTRIES_DISPLAY"]');
    toggleCountries($countriesSwitch, $countriesEnabledSwitch);

    $countriesSwitch.on('change', function () {
        toggleCountries($countriesSwitch, $countriesEnabledSwitch);
    });

    $countriesEnabledSwitch.on('change', function () {
        toggleCountries($countriesSwitch, $countriesEnabledSwitch);
    });
});
