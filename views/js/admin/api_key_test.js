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
    $('button.js-test-api-keys').on('click', testApiKeys);

    function testApiKeys() {
        var testKey = $('#MOLLIE_API_KEY_TEST').val();
        var liveKey = $('#MOLLIE_API_KEY').val();
        $.ajax(ajaxUrl, {
                method: 'POST',
                data: {
                    'testKey': testKey,
                    'liveKey': liveKey,
                    'action': 'testApiKeys',
                    'ajax': 1
                },
                success: function (response) {
                    response = JSON.parse(response);
                    var $apiKeyTestButtonGroup = $('.js-api-key-test');
                    $('.js-api-test-results').remove();
                    $apiKeyTestButtonGroup.after(response.template);
                }
            }
        )
    }
});
