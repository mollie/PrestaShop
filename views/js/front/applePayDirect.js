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
    const applePayMethodElement = document.querySelector(
        '#mollie-applepay-direct-button',
    )
    const canShowButton = applePayMethodElement && (ApplePaySession && ApplePaySession.canMakePayments())
    if (!canShowButton) {
        return;
    }

    const button = document.createElement('button')
    button.setAttribute('id', 'mollie_applepay_button')
    button.classList.add('apple-pay-button')
    button.classList.add('apple-pay-button-black')
    applePayMethodElement.appendChild(button)
    let updatedContactInfo = []
    let selectedShippingMethod = []

    document.querySelector('#mollie_applepay_button').addEventListener('click', (e) => {
        e.preventDefault();
        applePaySession();
    })

    let applePaySession = () => {
        const subtotal = $('.product-prices').find('[itemprop="price"]').attr('content') * $('#quantity_wanted').val();
        const session = new ApplePaySession(3, createRequest(countryCode, currencyCode, totalLabel, subtotal))
        session.begin()
        session.onvalidatemerchant = (applePayValidateMerchantEvent) => {
            jQuery.ajax({
                url: ajaxUrl,
                method: 'POST',
                data: {
                    action: 'mollie_apple_pay_validation',
                    validationUrl: applePayValidateMerchantEvent.validationURL
                },
                complete: (jqXHR, textStatus) => {
                },
                success: (merchantSession, textStatus, jqXHR) => {
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
            const productDetails = JSON.parse(document.getElementById('product-details').dataset.product);
            const product = {
                'id_product': productDetails.id_product,
                'id_product_attribute': productDetails.id_product_attribute,
                'id_customization': productDetails.id_customization,
                'quantity_wanted': productDetails.quantity_wanted,
            }
            let selectedShippingMethod = []

            const {billingContact, shippingContact} = ApplePayPayment.payment
            jQuery.ajax({
                url: ajaxUrl,
                method: 'POST',
                data: {
                    action: 'mollie_apple_pay_create_order',
                    product: product,
                    customerId: customerId,
                    shippingContact: ApplePayPayment.payment.shippingContact,
                    billingContact: ApplePayPayment.payment.billingContact,
                    token: ApplePayPayment.payment.token,
                    shippingMethod: selectedShippingMethod,
                    'billing_first_name': billingContact.givenName || '',
                    'billing_last_name': billingContact.familyName || '',
                    'billing_company': '',
                    'billing_country': billingContact.countryCode || '',
                    'billing_address_1': billingContact.addressLines[0] || '',
                    'billing_address_2': billingContact.addressLines[1] || '',
                    'billing_postcode': billingContact.postalCode || '',
                    'billing_city': billingContact.locality || '',
                    'billing_state': billingContact.administrativeArea || '',
                    'billing_phone': billingContact.phoneNumber || '000000000000',
                    'billing_email': shippingContact.emailAddress || '',
                    'shipping_first_name': shippingContact.givenName || '',
                    'shipping_last_name': shippingContact.familyName || '',
                    'shipping_company': '',
                    'shipping_country': shippingContact.countryCode || '',
                    'shipping_address_1': shippingContact.addressLines[0] || '',
                    'shipping_address_2': shippingContact.addressLines[1] || '',
                    'shipping_postcode': shippingContact.postalCode || '',
                    'shipping_city': shippingContact.locality || '',
                    'shipping_state': shippingContact.administrativeArea || '',
                    'shipping_phone': shippingContact.phoneNumber || '000000000000',
                    'shipping_email': shippingContact.emailAddress || '',
                    'order_comments': '',
                    'payment_method': 'mollie_wc_gateway_applepay',
                    '_wp_http_referer': '/?wc-ajax=update_order_review'
                },
                complete: (jqXHR, textStatus) => {
                },
                success: (authorizationResult, textStatus, jqXHR) => {
                    let result = authorizationResult.data

                    if (authorizationResult.success === true) {
                        redirectionUrl = result['returnUrl'];
                        session.completePayment(result['responseToApple'])
                        window.location.href = redirectionUrl
                    } else {
                        result.errors = createAppleErrors(result.errors)
                        session.completePayment(result)
                    }
                },
                error: (jqXHR, textStatus, errorThrown) => {
                    console.warn(textStatus, errorThrown)
                    session.abort()
                },
            })
        }
        session.onshippingmethodselected = function (event) {
            console.log(event);
            console.log(session);
            jQuery.ajax({
                url: ajaxUrl,
                method: 'POST',
                data: {
                    action: 'mollie_apple_pay_update_shipping_method',
                    shippingMethod: event.shippingMethod,
                    simplifiedContact: updatedContactInfo,
                },
                complete: (jqXHR, textStatus) => {
                },
                success: (applePayShippingMethodUpdate, textStatus, jqXHR) => {
                    let response = applePayShippingMethodUpdate.data
                    selectedShippingMethod = event.shippingMethod
                    if (applePayShippingMethodUpdate.success === false) {
                        response.errors = createAppleErrors(response.errors)
                    }
                    this.completeShippingMethodSelection(response)
                },
                error: (jqXHR, textStatus, errorThrown) => {
                    console.warn(textStatus, errorThrown)
                    session.abort()
                },
            })
        }
        session.onshippingcontactselected = function (event) {
            console.log(event);
            const productDetails = JSON.parse(document.getElementById('product-details').dataset.product);
            const product = {
                'id_product': productDetails.id_product,
                'id_product_attribute': productDetails.id_product_attribute,
                'id_customization': productDetails.id_customization,
                'quantity_wanted': productDetails.quantity_wanted,
            }

            jQuery.ajax({
                url: ajaxUrl,
                method: 'POST',
                data: {
                    action: 'mollie_apple_pay_update_shipping_contact',
                    countryCode: event.shippingContact.countryCode,
                    postalCode: event.shippingContact.postalCode,
                    simplifiedContact: event.shippingContact,
                    product: product
                },
                complete: (jqXHR, textStatus) => {
                },
                success: (applePayShippingContactUpdate, textStatus, jqXHR) => {
                    applePayShippingContactUpdate = JSON.parse(applePayShippingContactUpdate)
                    let response = applePayShippingContactUpdate.data
                    console.log(applePayShippingContactUpdate)
                    console.log(ApplePaySession.STATUS_SUCCESS)
                    console.log(response.shipping_methods[0].amount)
                    if (applePayShippingContactUpdate.success === true) {
                        session.completeShippingContactSelection(
                            ApplePaySession.STATUS_SUCCESS,
                            response.shipping_methods,
                            {
                                'label': 'test',
                                'amount': '0.01'
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
});

function createRequest(countryCode, currencyCode, totalLabel, subtotal) {
    // let applePayShippingMethod = {
    //     amount: "0.00",
    //     dateComponentsRange: {
    //         startDateComponents: {
    //             year: shippingStart.getFullYear(),
    //             // Because the JavaScript getMonth() function is 0-based, add 1 to return a 1-based month.
    //             months: shippingStart.getMonth() + 1,
    //             days: shippingStart.getDate(),
    //         },
    //         endDateComponents: {
    //             year: shippingEnd.getFullYear(),
    //             months: shippingEnd.getMonth() + 1,
    //             days: shippingEnd.getDate(),
    //         },
    //     },
    //     detail: "Tickets sent to your address",
    //     identifier: "delivery",
    //     label: "Delivery",
    // };
    //
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
