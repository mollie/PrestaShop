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
    const applePayMethodElement = document.querySelector(
        '#mollie-applepay-direct-button',
    )
    const canShowButton = applePayMethodElement && (window.ApplePaySession && ApplePaySession.canMakePayments())
    if (!canShowButton) {
        return;
    }

    let buttonStyle = getApplePayButtonStyle();
    createAppleButton(applePayMethodElement, buttonStyle)

    let updatedContactInfo = []
    let selectedShippingMethod = []

    document.querySelector('#mollie_applepay_button').addEventListener('click', (e) => {
        e.preventDefault();
        applePaySession();
    })

    let applePaySession = () => {
        const productDetails = JSON.parse(document.getElementById('product-details').dataset.product);
        const product =
            {
                'id_product': productDetails.id_product,
                'id_product_attribute': productDetails.id_product_attribute,
                'id_customization': productDetails.id_customization,
                'quantity_wanted': productDetails.quantity_wanted,
                'price_amount': productDetails.price_amount
            }

        const subtotal = product.quantity_wanted * product.price_amount;
        var supportedApplePaySessionVersion = 3;
        const session = new ApplePaySession(supportedApplePaySessionVersion, createRequest(countryCode, currencyCode, totalLabel, subtotal))
        var cartId;
        session.begin()
        session.onvalidatemerchant = (applePayValidateMerchantEvent) => {
            jQuery.ajax({
                url: ajaxUrl,
                method: 'POST',
                data: {
                    action: 'mollie_apple_pay_validation',
                    validationUrl: applePayValidateMerchantEvent.validationURL
                },
                success: (merchantSession) => {
                    merchantSession = JSON.parse(merchantSession);
                    if (merchantSession.success === true) {
                        cartId = merchantSession.cartId
                        session.completeMerchantValidation(JSON.parse(merchantSession.data))
                    } else {
                        console.warn(merchantSession.error)
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
            const productDetails = JSON.parse(document.getElementById('product-details').dataset.product);
            const products = [
                {
                    'id_product': productDetails.id_product,
                    'id_product_attribute': productDetails.id_product_attribute,
                    'id_customization': productDetails.id_customization,
                    'quantity_wanted': productDetails.quantity_wanted,
                }
            ]

            jQuery.ajax({
                url: ajaxUrl,
                method: 'POST',
                data: {
                    action: 'mollie_apple_pay_create_order',
                    products: products,
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
            const productDetails = JSON.parse(document.getElementById('product-details').dataset.product);
            const products = [
                {
                    'id_product': productDetails.id_product,
                    'id_product_attribute': productDetails.id_product_attribute,
                    'id_customization': productDetails.id_customization,
                    'quantity_wanted': productDetails.quantity_wanted,
                }
            ]

            jQuery.ajax({
                url: ajaxUrl,
                method: 'POST',
                data: {
                    action: 'mollie_apple_pay_update_shipping_contact',
                    countryCode: event.shippingContact.countryCode,
                    postalCode: event.shippingContact.postalCode,
                    simplifiedContact: event.shippingContact,
                    products: products,
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
                    } else {
                        console.warn(applePayShippingContactUpdate)
                        session.abort()
                    }
                },
                error: (jqXHR, textStatus, errorThrown) => {
                    console.warn(textStatus, errorThrown)
                    session.abort()
                },
            })
        }
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
