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
    var canPay = !!(window.ApplePaySession && window.ApplePaySession.canMakePayments());

    // Without a path the browser kept one cookie per folder. A copy written on a page under
    // /module/mollie/ then overrode this one for every module request (the express session),
    // so it is set for the whole shop and the old folder copy is removed.
    document.cookie = 'isApplePayMethod=; path=/module/mollie; expires=Thu, 01 Jan 1970 00:00:00 GMT';
    document.cookie = 'isApplePayMethod=' + (canPay ? '1' : '0') + '; path=/; SameSite=Lax';
});
