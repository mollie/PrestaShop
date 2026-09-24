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
    whenApplePaySessionAvailable(initApplePayDirect)
})

function initApplePayDirect() {
    var applePayMethodElement = document.querySelector(
        '#mollie-applepay-direct-button',
    )

    if (!applePayMethodElement) {
        return;
    }

    let buttonStyle = getApplePayButtonStyle();
    const startApplePaySession = function () {
        applePaySession();
    }
    createAppleButton(applePayMethodElement, buttonStyle, startApplePaySession)
    toggleApplePayVisibility()

    if (typeof prestashop !== 'undefined') {
        prestashop.on('updatedCart', function () {
            var container = document.querySelector('#mollie-applepay-direct-button');
            if (!container) {
                return;
            }

            if (!container.querySelector('#mollie_applepay_button')) {
                createAppleButton(container, buttonStyle, startApplePaySession);
            }

            toggleApplePayVisibility()
        });
    }

    let updatedContactInfo = []
    let selectedShippingMethod = []
    let cartSubTotal = 0;

    let applePaySession = () => {
        getCartSubTotal();
        //todo: constant
        var supportedApplePaySessionVersion = 3;
        const session = new ApplePaySession(supportedApplePaySessionVersion, createRequest(countryCode, currencyCode, totalLabel, cartSubTotal))
        session.begin()
        session.oncancel = () => {
            restoreCartTotals()
        }
        session.onvalidatemerchant = (applePayValidateMerchantEvent) => {
            jQuery.ajax({
                url: ajaxUrl,
                method: 'POST',
                data: {
                    action: 'mollie_apple_pay_validation',
                    validationUrl: applePayValidateMerchantEvent.validationURL,
                    cartId: cartId
                },
                success: (merchantSession) => {
                    merchantSession = JSON.parse(merchantSession);
                    if (merchantSession.success === true) {
                        session.completeMerchantValidation(JSON.parse(merchantSession.data))
                    } else {
                        console.warn(merchantSession.data)
                        session.abort()
                    }
                },
                error: (jqXHR, textStatus, errorThrown) => {
                    console.warn(textStatus, errorThrown)
                    session.abort()
                },
            })
        }
        session.onpaymentauthorized = (ApplePayPayment) => {
            jQuery.ajax({
                url: ajaxUrl,
                method: 'POST',
                data: {
                    action: 'mollie_apple_pay_create_order',
                    shippingContact: ApplePayPayment.payment.shippingContact,
                    billingContact: ApplePayPayment.payment.billingContact,
                    token: ApplePayPayment.payment.token,
                    cartId: cartId,
                },
                success: (authorizationResult) => {
                    let result = JSON.parse(authorizationResult)

                    if (result.success === true) {
                        redirectionUrl = result.successUrl;
                        session.completePayment(result.responseToApple)
                        setTimeout(function () {
                            window.location.href = redirectionUrl
                        }, 500)
                    } else {
                        session.completePayment(buildPaymentFailure(result))
                    }
                },
                error: (jqXHR) => {
                    session.completePayment(buildPaymentFailure(parseJsonSafely(jqXHR.responseText)))
                },
            })
        }
        session.onshippingmethodselected = function (event) {
            jQuery.ajax({
                url: ajaxUrl,
                method: 'POST',
                data: {
                    action: 'mollie_apple_pay_update_shipping_method',
                    shippingMethod: event.shippingMethod,
                    simplifiedContact: updatedContactInfo,
                    cartId: cartId
                },
                success: (applePayShippingMethodUpdate) => {
                    let response = JSON.parse(applePayShippingMethodUpdate)
                    selectedShippingMethod = event.shippingMethod
                    if (response.success === false) {
                        response.errors = createAppleErrors(response.errors)
                    }
                    showCartTotals(
                        response.data && response.data.amount,
                        event.shippingMethod && event.shippingMethod.amount
                    )
                    session.completeShippingMethodSelection(
                        ApplePaySession.STATUS_SUCCESS,
                        {
                            'amount': response.data.amount,
                            'label': totalLabel
                        },
                        []
                    )
                },
                error: (jqXHR, textStatus, errorThrown) => {
                    console.warn(textStatus, errorThrown)
                    session.abort()
                },
            })
        }
        session.onshippingcontactselected = function (event) {

            jQuery.ajax({
                url: ajaxUrl,
                method: 'POST',
                data: {
                    action: 'mollie_apple_pay_update_shipping_contact',
                    countryCode: event.shippingContact.countryCode,
                    postalCode: event.shippingContact.postalCode,
                    simplifiedContact: event.shippingContact,
                    cartId: cartId,
                    customerId: customerId
                },
                success: (applePayShippingContactUpdate) => {
                    applePayShippingContactUpdate = JSON.parse(applePayShippingContactUpdate)
                    let response = applePayShippingContactUpdate.data
                    if (applePayShippingContactUpdate.success === true && response.totals.length > 0) {
                        var firstTotal = response.totals[0];
                        var firstShippingMethod = response.shipping_methods[0]
                        showCartTotals(
                            firstTotal.amount,
                            firstShippingMethod && firstShippingMethod.amount
                        )
                        session.completeShippingContactSelection(
                            ApplePaySession.STATUS_SUCCESS,
                            response.shipping_methods,
                            {
                                'label': totalLabel,
                                'amount': firstTotal.amount
                            },
                            [
                                response.paymentFee
                            ]
                        );

                        return;
                    }

                    if (!response || !response.fallbackTotal) {
                        console.warn(applePayShippingContactUpdate)
                        session.abort()

                        return;
                    }

                    session.completeShippingContactSelection({
                        errors: createAppleErrors(applePayShippingContactUpdate.errors || []),
                        newShippingMethods: [],
                        newTotal: response.fallbackTotal,
                        newLineItems: []
                    });
                },
                error: (jqXHR, textStatus, errorThrown) => {
                    console.warn(textStatus, errorThrown)
                    session.abort()
                },
            })
        }
    }

    function getCartSubTotal() {
        jQuery.ajax({
            url: ajaxUrl,
            method: 'POST',
            async: false,
            data: {
                action: 'mollie_apple_pay_get_total_price',
                cartId: cartId
            },
            success: (cartTotal) => {
                let response = JSON.parse(cartTotal)
                cartSubTotal = response.total;
            },
        })
    }
}

function whenApplePaySessionAvailable(onAvailable) {
    if (canUseApplePaySession()) {
        onAvailable()

        return
    }

    if (!window.customElements) {
        return
    }

    // insurance: 1.latest is a rolling URL; if Apple ever moves the ApplePaySession
    // polyfill behind the SDK's dynamic import, re-check once the module lands
    customElements.whenDefined('apple-pay-button').then(function () {
        if (canUseApplePaySession()) {
            onAvailable()
        }
    })
}

function canUseApplePaySession() {
    return !!(window.ApplePaySession && window.ApplePaySession.canMakePayments())
}

var CART_SUMMARY_SELECTORS = {
    total: '.cart-summary-line.cart-total .value',
    shipping: '#cart-subtotal-shipping .value',
}

/**
 * Keeps the cart page's "Shipping" and "Total (tax incl.)" lines in step with the Apple Pay
 * sheet, which re-totals every time the shopper picks a different delivery option. The page
 * itself is not re-rendered while the sheet is open, so without this both lines keep showing
 * the figures from page load.
 */
function showCartTotals(total, shipping) {
    overrideCartSummaryLine(CART_SUMMARY_SELECTORS.total, total)
    overrideCartSummaryLine(CART_SUMMARY_SELECTORS.shipping, shipping)
}

function overrideCartSummaryLine(selector, amount) {
    var target = document.querySelector(selector)
    var value = parseFloat(amount)

    if (!target || isNaN(value)) {
        return
    }

    if (typeof target.dataset.mollieOriginalValue === 'undefined') {
        target.dataset.mollieOriginalValue = target.textContent
    }

    target.textContent = formatCartPrice(value)
}

function restoreCartTotals() {
    Object.keys(CART_SUMMARY_SELECTORS).forEach(function (line) {
        var target = document.querySelector(CART_SUMMARY_SELECTORS[line])

        if (!target || typeof target.dataset.mollieOriginalValue === 'undefined') {
            return
        }

        target.textContent = target.dataset.mollieOriginalValue
        delete target.dataset.mollieOriginalValue
    })
}

function formatCartPrice(amount) {
    try {
        return new Intl.NumberFormat(prestashop.language.locale, {
            style: 'currency',
            currency: prestashop.currency.iso_code,
        }).format(amount)
    } catch (e) {
        return amount.toFixed(2)
    }
}

function getApplePayButtonStyle() {
    switch (parseInt(applePayButtonStyle)) {
        case 0:
            return 'apple-pay-button-black';
        case 1:
            return 'apple-pay-button-white-with-line';
        case 2:
            return 'apple-pay-button-white';
        default:
            return 'apple-pay-button-black';
    }
}

function createRequest(countryCode, currencyCode, totalLabel, subtotal) {
    return {
        countryCode: countryCode,
        currencyCode: currencyCode,
        supportedNetworks: ['amex', 'maestro', 'masterCard', 'visa', 'vPay'],
        merchantCapabilities: ['supports3DS'],
        shippingType: 'shipping',
        requiredBillingContactFields: [
            'name',
            'postalAddress',
            'email'
        ],
        requiredShippingContactFields: [
            'name',
            'postalAddress',
            'email'
        ],
        requiredBillingAddressFields: [
            'countryCode',
        ],
        total: {
            label: totalLabel,
            amount: subtotal,
            type: 'final'
        }
    }
}

function createAppleErrors(errors) {
    const errorList = []
    for (const error of errors) {
        const {contactField = null, code = null, message = null} = error
        const appleError = contactField ? new ApplePayError(code, contactField, message) : new ApplePayError(code)
        errorList.push(appleError)
    }

    return errorList
}

// Apple only accepts a numeric status here. The server sends the string 'STATUS_FAILURE', which
// WebKit reads as STATUS_SUCCESS, so the sheet closed as if paid and the error list was dropped.
function buildPaymentFailure(result) {
    return {
        status: ApplePaySession.STATUS_FAILURE,
        errors: createAppleErrors((result && result.errors) || [])
    }
}

function parseJsonSafely(payload) {
    try {
        return JSON.parse(payload)
    } catch (e) {
        return {}
    }
}

function getUrlParam(sParam, string) {
    var sPageURL = decodeURIComponent(string),
        sURLVariables = sPageURL.split('&'),
        sParameterName,
        i;

    for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split('=');

        if (sParameterName[0] === sParam) {
            return sParameterName[1] === undefined ? true : sParameterName[1];
        }
    }
}

function createAppleButton(ApplePayButtonElement, buttonStyle, onClick) {
    if (!window.customElements) {
        ApplePayButtonElement.appendChild(createLegacyAppleButton(buttonStyle, onClick))

        return
    }

    const button = document.createElement('apple-pay-button')
    button.setAttribute('id', 'mollie_applepay_button')
    button.setAttribute('buttonstyle', getApplePaySdkButtonStyle())
    button.setAttribute('type', 'plain')
    if (typeof applePayLocale !== 'undefined') {
        button.setAttribute('locale', applePayLocale)
    }
    bindAppleButtonClick(button, onClick)
    ApplePayButtonElement.appendChild(button)

    // the SDK registers apple-pay-button asynchronously; swap to the legacy button if it never arrives
    const legacyButtonTimeout = setTimeout(function () {
        if (customElements.get('apple-pay-button')) {
            return
        }

        button.replaceWith(createLegacyAppleButton(buttonStyle, onClick))
    }, 3000)

    customElements.whenDefined('apple-pay-button').then(function () {
        clearTimeout(legacyButtonTimeout)
    })
}

function createLegacyAppleButton(buttonStyle, onClick) {
    const button = document.createElement('button')
    button.setAttribute('id', 'mollie_applepay_button')
    button.classList.add('apple-pay-button')
    button.classList.add(buttonStyle)
    bindAppleButtonClick(button, onClick)

    return button
}

// the SDK button swallows click propagation, so delegated handlers never fire - bind on the element itself
function bindAppleButtonClick(button, onClick) {
    button.addEventListener('click', function (e) {
        e.preventDefault()
        onClick()
    })
}

function getApplePaySdkButtonStyle() {
    switch (parseInt(applePayButtonStyle)) {
        case 1:
            return 'white-outline';
        case 2:
            return 'white';
        default:
            return 'black';
    }
}

function toggleApplePayVisibility() {
    var container = document.querySelector('#mollie-applepay-direct-button');
    if (!container) {
        return;
    }

    if (!isCartCheckoutAvailable()) {
        container.style.display = 'none';
    } else {
        container.style.display = '';
    }
}

function isCartCheckoutAvailable() {
    if (typeof prestashop === 'undefined' || !prestashop.cart) {
        return true;
    }

    var cart = prestashop.cart;

    if (!cart.products || cart.products.length === 0) {
        return false;
    }

    if (cart.minimalPurchaseRequired && cart.minimalPurchaseRequired.length > 0) {
        return false;
    }

    for (var i = 0; i < cart.products.length; i++) {
        if (cart.products[i].availability === 'unavailable') {
            return false;
        }
    }

    return true;
}
