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
    var applePayMethodElement = document.querySelector(
        '#mollie-applepay-direct-button',
    )

    const canShowButton = applePayMethodElement && (window.ApplePaySession && ApplePaySession.canMakePayments())
    if (!canShowButton) {
        return;
    }

    let buttonStyle = getApplePayButtonStyle();
    createAppleButton(applePayMethodElement, buttonStyle)

    $( document ).ajaxComplete(function( event, request, settings) {
        var method = getUrlParam('action', settings.url)

        if (method === 'refresh') {
            applePayMethodElement = document.querySelector(
                '#mollie-applepay-direct-button',
            )
            createAppleButton(applePayMethodElement, buttonStyle)
        }
    });


    let updatedContactInfo = []
    let selectedShippingMethod = []
    let cartSubTotal = 0;

    $(document).on('click', '#mollie_applepay_button', function(e) {
        e.preventDefault();
        applePaySession();
    })

    let applePaySession = () => {
        getCartSubTotal();
        //todo: constant
        const session = new ApplePaySession(3, createRequest(countryCode, currencyCode, totalLabel, cartSubTotal))
        session.begin()
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
                        window.location.href = redirectionUrl
                    } else {
                        result.errors = createAppleErrors(result.errors)
                        session.completePayment(result)
                    }
                },
                error: (jqXHR) => {
                    let result = JSON.parse(jqXHR.responseText)
                    result.errors = createAppleErrors(result.errors)
                    session.completePayment(result)
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
                    session.completeShippingMethodSelection(
                        ApplePaySession.STATUS_SUCCESS,
                        {
                            'amount': response.data.amount,
                            'label': ' mollie'
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
                    if (applePayShippingContactUpdate.success === true) {
                        if (response.totals.length > 0) {
                            var firstTotal = response.totals[0];
                            session.completeShippingContactSelection(
                                ApplePaySession.STATUS_SUCCESS,
                                response.shipping_methods,
                                {
                                    'label': firstTotal.label,
                                    'amount': firstTotal.amount
                                },
                                [
                                    response.paymentFee
                                ]
                            );

                            return;
                        }

                        session.completeShippingContactSelection(
                            ApplePaySession.STATUS_FAILURE,
                            [],
                            {
                                label: "No carriers", amount: "0"
                            },
                            []
                        );
                    }
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
});

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
            'postalAddress',
            'email'
        ],
        requiredShippingContactFields: [
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

function createAppleButton(ApplePayButtonElement, buttonStyle) {
    const button = document.createElement('button')
    button.setAttribute('id', 'mollie_applepay_button')
    button.classList.add('apple-pay-button')
    button.classList.add(buttonStyle)
    ApplePayButtonElement.appendChild(button)
}
