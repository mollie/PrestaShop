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

$(document).ready(function() {
    setApplePayMethodCookie();

    if (window.ApplePaySession || !window.customElements) {
        return;
    }

    // insurance: 1.latest is a rolling URL; if Apple ever moves the ApplePaySession
    // polyfill behind the SDK's dynamic import, re-check once the module lands
    customElements.whenDefined('apple-pay-button').then(setApplePayMethodCookie);
});

function setApplePayMethodCookie() {
    if (window.ApplePaySession && window.ApplePaySession.canMakePayments()) {
        document.cookie = 'isApplePayMethod = 1';

        return;
    }

    document.cookie = 'isApplePayMethod = 0';
}
