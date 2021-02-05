/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @link        https://github.com/mollie/PrestaShop
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 * @codingStandardsIgnoreStart
 */
$(document).ready(function() {
    document.cookie = 'isApplePayMethod = 0';
    if (window.ApplePaySession && window.ApplePaySession.canMakePayments()) {
        document.cookie = 'isApplePayMethod = 1';
    }
});
