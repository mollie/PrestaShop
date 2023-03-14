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
    $(document).on('change', 'input[name="mollie-save-card"]', function () {
        var mollieSaveCard = $('input[name="mollieSaveCard"]');
        mollieSaveCard.val($(this).is(':checked') ? 1 : 0);
    });

    handleSavedCard($('input[name="mollie-use-saved-card"]').is(':checked'));
    $(document).on('click', 'input[name="mollie-use-saved-card"]', function () {
        handleSavedCard($(this).is(':checked'));
    });

    function handleSavedCard(useSavedCard)
    {
        $('input[name="mollieUseSavedCard"]').val(useSavedCard ? 1 : 0);
        if (useSavedCard) {
            $('.mollie-credit-card-inputs').addClass('mollie-credit-card-container__hide')

        } else {
            $('.mollie-credit-card-inputs').removeClass('mollie-credit-card-container__hide')
        }
    }
});
