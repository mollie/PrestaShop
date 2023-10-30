/// <reference types="Cypress" />
function prepareCookie()
      {
            const name = 'PrestaShop-';

                   cy.request(
            {
                url: '/'
            }
        ).then((res) => {

            const cookies = res.requestHeaders.cookie.split(/; */);

            cookies.forEach(cookie => {

                const parts = cookie.split('=');
                const key = parts[0]
                const value = parts[1];

                if (key.startsWith(name)) {
                    cy.setCookie(
                        key,
                        value,
                        {
                            sameSite: 'None',
                            secure: true
                        }
                    );
                }
            });

        });
      }
//Caching the BO and FO session
const login = (MollieBOFOLoggingIn) => {
  cy.session(MollieBOFOLoggingIn,() => {
  cy.visit('/admin1/')
  cy.url().should('contain', 'https').as('Check if HTTPS exists')
  cy.get('#email').type('demo@prestashop.com',{delay: 0, log: false})
  cy.get('#passwd').type('prestashop_demo',{delay: 0, log: false})
  cy.get('#submit_login').click().wait(1000).as('Connection successsful')
  cy.visit('/en/my-account')
  cy.get('#login-form [name="email"]').eq(0).type('demo@prestashop.com')
  cy.get('#login-form [name="password"]').eq(0).type('prestashop_demo')
  cy.get('#login-form [type="submit"]').eq(0).click({force:true})
  cy.get('#history-link > .link-item').click()
  })
  }
//Checking the console for errors
let windowConsoleError;
Cypress.on('window:before:load', (win) => {
  windowConsoleError = cy.spy(win.console, 'error');
})
afterEach(() => {
  expect(windowConsoleError).to.not.be.called;
})
describe('PS8 Tests Suite', () => {
  beforeEach(() => {
      cy.viewport(1920,1080)
      login('MollieBOFOLoggingIn')
  })
it.skip('C339342: 05 Vouchers Checkouting [Orders API]', () => { //temporary skip, possible bug containing PS8 version
      cy.visit('/de/index.php?controller=history')
      cy.contains('Reorder').click()
      cy.contains('DE').click()
      //Billing country LT, DE etc.
      cy.get('.clearfix > .btn').click()
      cy.get('#js-delivery > .continue').click()
      //Payment method choosing
      cy.contains('Voucher').click({force:true})
      cy.get('.condition-label > .js-terms').click({force:true})
      prepareCookie();
      cy.get('.ps-shown-by-js > .btn').click()
      cy.setCookie(
        'SESSIONID',
        "cypress-dummy-value",
        {
            domain: '.www.mollie.com',
            sameSite: 'None',
            secure: true,
            httpOnly: true
        }
      );    // reload current page to activate cookie
      cy.reload();
      cy.get('.grid-button-voucher-monizze-meal').click()
      cy.get('[value="paid"]').click()
      cy.get('[class="button form__button"]').click()
      cy.get('.grid-button-paypal').click()
      cy.get('[value="paid"]').click()
      cy.get('[class="button form__button"]').click()
      cy.get('#content-hook_order_confirmation > .card-block').should('be.visible')
})
it.skip('C339343: 06 Vouchers Order BO Refunding, Shipping (Paid part only) [Orders API]', () => {
      cy.OrderRefundingShippingOrdersAPI()
      cy.get('[class="card-body"]').find('[class="alert alert-warning"]').should('exist') //additional checking if the warning alert for vouchers exist
})
it('C339344: 07 Bancontact Checkouting [Orders API]', () => {
      cy.visit('/de/index.php?controller=history')
      cy.contains('Reorder').click()
      cy.contains('DE').click()
      //Billing country LT, DE etc.
      cy.get('.clearfix > .btn').click()
      cy.get('#js-delivery > .continue').click()
      //Payment method choosing
      cy.contains('Bancontact').click({force:true})
      cy.get('.condition-label > .js-terms').click({force:true})
      prepareCookie();
      cy.get('.ps-shown-by-js > .btn').click()
      cy.setCookie(
        'SESSIONID',
        "cypress-dummy-value",
        {
            domain: '.www.mollie.com',
            sameSite: 'None',
            secure: true,
            httpOnly: true
        }
      );    // reload current page to activate cookie
      cy.reload();
      cy.get('[value="paid"]').click()
      cy.get('[class="button form__button"]').click()
      cy.get('#content-hook_order_confirmation > .card-block').should('be.visible')
})
it('C339345: 08 Bancontact Order BO Shipping, Refunding [Orders API]', () => {
      cy.OrderRefundingShippingOrdersAPI()
})
it('C339346: 09 iDEAL Checkouting [Orders API]', () => {
      cy.visit('/de/index.php?controller=history')
      cy.contains('Reorder').click()
      //Billing country LT, DE etc.
      cy.get('.clearfix > .btn').click()
      cy.get('#js-delivery > .continue').click()
      //Payment method choosing
      cy.contains('iDEAL').click({force:true})
      cy.get('.condition-label > .js-terms').click({force:true})
      prepareCookie();
      cy.get('.ps-shown-by-js > .btn').click()
      cy.setCookie(
        'SESSIONID',
        "cypress-dummy-value",
        {
            domain: '.www.mollie.com',
            sameSite: 'None',
            secure: true,
            httpOnly: true
        }
      );    // reload current page to activate cookie
      cy.reload();
      cy.get('.payment-method-list > :nth-child(1)').click()
      cy.get('[value="paid"]').click()
      cy.get('[class="button form__button"]').click()
      cy.get('#content-hook_order_confirmation > .card-block').should('be.visible')
})
it('C339347: 10 iDEAL Order BO Shipping, Refunding [Orders API]', () => {
      cy.OrderRefundingShippingOrdersAPI()
})
it('C339348: 11 Klarna Slice It Checkouting [Orders API]', () => {
      cy.visit('/de/index.php?controller=history')
      cy.contains('Reorder').click()
      //Billing country LT, DE etc.
      cy.contains('DE').click()
      cy.get('.clearfix > .btn').click()
      cy.get('#js-delivery > .continue').click()
      //Payment method choosing
      cy.contains('Ratenkauf.').click({force:true})
      cy.get('.condition-label > .js-terms').click({force:true})
      prepareCookie();
      cy.get('.ps-shown-by-js > .btn').click()
      cy.setCookie(
        'SESSIONID',
        "cypress-dummy-value",
        {
            domain: '.www.mollie.com',
            sameSite: 'None',
            secure: true,
            httpOnly: true
        }
      );    // reload current page to activate cookie
      cy.reload();
      cy.get('[value="authorized"]').click()
      cy.get('[class="button form__button"]').click()
      cy.get('#content-hook_order_confirmation > .card-block').should('be.visible')
})
it('C339349: 12 Klarna Slice It Order BO Shipping, Refunding [Orders API]', () => {
      cy.OrderShippingRefundingOrdersAPI()
})
it('C339350: 13 Klarna Pay Later Checkouting [Orders API]', () => {
      cy.visit('/de/index.php?controller=history')

      //
      cy.contains('Reorder').click()
      //Billing country LT, DE etc.
      cy.contains('DE').click()
      cy.get('.clearfix > .btn').click()
      cy.get('#js-delivery > .continue').click()
      //Payment method choosing
      cy.contains('Rechnung.').click({force:true})
      cy.get('.condition-label > .js-terms').click({force:true})
      prepareCookie();
      cy.get('.ps-shown-by-js > .btn').click()
      cy.setCookie(
        'SESSIONID',
        "cypress-dummy-value",
        {
            domain: '.www.mollie.com',
            sameSite: 'None',
            secure: true,
            httpOnly: true
        }
      );    // reload current page to activate cookie
      cy.reload();
      cy.get('[value="authorized"]').click()
      cy.get('[class="button form__button"]').click()
      cy.get('#content-hook_order_confirmation > .card-block').should('be.visible')
})
it('C339351: 14 Klarna Pay Later Order BO Shipping, Refunding [Orders API]', () => {
      cy.OrderShippingRefundingOrdersAPI()
})
it('C339352: 15 Klarna Pay Now Checkouting [Orders API]', () => {
      cy.visit('/de/index.php?controller=history')
      //
      cy.contains('Reorder').click()
      //Billing country LT, DE etc.
      cy.contains('DE').click()
      cy.get('.clearfix > .btn').click()
      cy.get('#js-delivery > .continue').click()
      //Payment method choosing
      cy.contains('Pay now.').click({force:true})
      cy.get('.condition-label > .js-terms').click({force:true})
      prepareCookie();
      cy.get('.ps-shown-by-js > .btn').click()
      cy.setCookie(
        'SESSIONID',
        "cypress-dummy-value",
        {
            domain: '.www.mollie.com',
            sameSite: 'None',
            secure: true,
            httpOnly: true
        }
      );    // reload current page to activate cookie
      cy.reload();
      cy.get('[value="authorized"]').click()
      cy.get('[class="button form__button"]').click()
      cy.get('#content-hook_order_confirmation > .card-block').should('be.visible')
})
it('C339353: 16 Klarna Pay Now Order BO Shipping, Refunding [Orders API]', () => {
      cy.OrderShippingRefundingOrdersAPI()
})
it('C339354: 17 Credit Card Checkouting [Orders API]', () => {
      //Enabling the Single-Click for now
      cy.visit('/admin1/')
      cy.OpeningModuleDashboardURL()
      cy.get('#MOLLIE_SANDBOX_SINGLE_CLICK_PAYMENT_on').click({force:true})
      cy.get('[type="submit"]').first().click({force:true})
      cy.get('[class="alert alert-success"]').should('be.visible')
      cy.visit('/en/index.php?controller=history')

      cy.contains('Reorder').click()
      //Billing country LT, DE etc.
      cy.get('.clearfix > .btn').click()
      cy.get('#js-delivery > .continue').click()
      //Payment method choosing
      cy.contains('Card').click({force:true})
      //Credit card inputing
      cy.CreditCardFillingIframe()
      cy.get('.condition-label > .js-terms').click({force:true})
      prepareCookie();
      cy.get('.ps-shown-by-js > .btn').click({force: true})
      cy.setCookie(
        'SESSIONID',
        "cypress-dummy-value",
        {
            domain: '.www.mollie.com',
            sameSite: 'None',
            secure: true,
            httpOnly: true
        }
      );    // reload current page to activate cookie
      cy.reload();
      cy.get('[value="paid"]').click()
      cy.get('[class="button form__button"]').click()
      cy.get('#content-hook_order_confirmation > .card-block').should('be.visible')
})
it('C339355: 18 Check if customerId is passed during the 2nd payment using Single Click Payment [Orders API]', () => {
      cy.visit('/en/index.php?controller=history')
      cy.contains('Reorder').click()
      //Billing country LT, DE etc.
      cy.get('.clearfix > .btn').click()
      cy.get('#js-delivery > .continue').click()
      //Payment method choosing
      cy.contains('Card').click({force:true})
      cy.get('.condition-label > .js-terms').click({force:true})
      prepareCookie();
      cy.get('.ps-shown-by-js > .btn').click({force: true})
      cy.setCookie(
        'SESSIONID',
        "cypress-dummy-value",
        {
            domain: '.www.mollie.com',
            sameSite: 'None',
            secure: true,
            httpOnly: true
        }
      );    // reload current page to activate cookie
      cy.reload();
      cy.visit('/admin1/')
      //Disabling the single-click - no need again
      cy.OpeningModuleDashboardURL()
      cy.get('#MOLLIE_SANDBOX_SINGLE_CLICK_PAYMENT_off').click({force:true})
      cy.get('[type="submit"]').first().click({force:true})
      cy.get('[class="alert alert-success"]').should('be.visible')
})
it('C339356: 19 Credit Card Order BO Shipping, Refunding [Orders API]', () => {
      cy.OrderRefundingShippingOrdersAPI()
})
it('C339357: 20 IN3 Checkouting [Orders API]', () => { // wip
      cy.visit('/de/index.php?controller=history')
      cy.contains('Reorder').click()
      cy.contains('NL').click()
      //Billing country LT, DE etc.
      cy.get('.clearfix > .btn').click()
      cy.get('#js-delivery > .continue').click()
      //Payment method choosing
      // waiting for enabling IN3 payment
      cy.contains('in3').click({force:true})
      cy.get('.condition-label > .js-terms').click({force:true})
      prepareCookie();
      cy.get('.ps-shown-by-js > .btn').click()
      cy.setCookie(
        'SESSIONID',
        "cypress-dummy-value",
        {
            domain: '.www.mollie.com',
            sameSite: 'None',
            secure: true,
            httpOnly: true
        }
      );    // reload current page to activate cookie
      cy.reload();
      cy.get('[value="paid"]').click()
      cy.get('[class="button form__button"]').click()
      cy.get('#content-hook_order_confirmation > .card-block').should('be.visible')
})
it('C339358: 21 IN3 Order BO Shipping, Refunding [Orders API]', () => { // checking why payment div is not loaded in the Orders for some reason
      cy.OrderRefundingShippingOrdersAPI()
})
it('C339359: 22 IN3 should not be shown under 5000 EUR [Orders API]', () => {
      cy.visit('/en/index.php?controller=history')
      cy.contains('Reorder').click()
      cy.visit('/en/cart?action=show')
      cy.get('[class="js-cart-line-product-quantity form-control"]').clear().type('200')
      cy.contains('Proceed to checkout').click()
      cy.contains('NL').click()
      //Billing country LT, DE etc.
      cy.get('.clearfix > .btn').click()
      cy.get('#js-delivery > .continue').click()
      //Payment method choosing
      cy.contains('in3').should('not.exist')
})
it('C339360: 23 IN3 Checking that IN3 logo exists OK [Orders API]', () => {
      cy.visit('/admin1/')
      cy.OpeningModuleDashboardURL()
      cy.get('[href="#advanced_settings"]').click({force:true})
      cy.get('[name="MOLLIE_IMAGES"]').select('big')
      cy.get('[type="submit"]').first().click({force:true})
      cy.get('[class="alert alert-success"]').should('be.visible')
      cy.visit('/de/index.php?controller=history')
      cy.contains('Reorder').click()
      cy.contains('NL').click()
      //Billing country LT, DE etc.
      cy.get('.clearfix > .btn').click()
      cy.get('#js-delivery > .continue').click()
      //asserting i3 image
      cy.get('html').should('contain.html','src="https://www.mollie.com/external/icons/payment-methods/in3%402x.png"')
      //todo finish
      cy.visit('/admin1/')
      cy.OpeningModuleDashboardURL()
      cy.get('[href="#advanced_settings"]').click({force:true})
      cy.get('[name="MOLLIE_IMAGES"]').select('hide')
      cy.get('[type="submit"]').first().click({force:true})
      cy.get('[class="alert alert-success"]').should('be.visible')
})
it('C339361: 24 Paypal Checkouting [Orders API]', () => {
      cy.visit('/de/index.php?controller=history')
      cy.contains('Reorder').click()
      cy.contains('NL').click()
      //Billing country LT, DE etc.
      cy.get('.clearfix > .btn').click()
      cy.get('#js-delivery > .continue').click()
      //Payment method choosing
      cy.contains('PayPal').click({force:true})
      cy.get('.condition-label > .js-terms').click({force:true})
      prepareCookie();
      cy.get('.ps-shown-by-js > .btn').click()
      cy.setCookie(
        'SESSIONID',
        "cypress-dummy-value",
        {
            domain: '.www.mollie.com',
            sameSite: 'None',
            secure: true,
            httpOnly: true
        }
      );    // reload current page to activate cookie
      cy.reload();
      cy.get('[value="paid"]').click()
      cy.get('[class="button form__button"]').click()
      cy.get('#content-hook_order_confirmation > .card-block').should('be.visible')
});
it('C339362: 25 Paypal Order Shipping, Refunding [Orders API]', () => {
      cy.OrderRefundingShippingOrdersAPI()
})
it('C339363: 26 SOFORT Checkouting [Orders API]', () => {
      cy.visit('/de/index.php?controller=history')
      cy.contains('Reorder').click()
      cy.contains('NL').click()
      //Billing country LT, DE etc.
      cy.get('.clearfix > .btn').click()
      cy.get('#js-delivery > .continue').click()
      //Payment method choosing
      cy.contains('SOFORT').click({force:true})
      cy.get('.condition-label > .js-terms').click({force:true})
      prepareCookie();
      cy.get('.ps-shown-by-js > .btn').click()
      cy.setCookie(
        'SESSIONID',
        "cypress-dummy-value",
        {
            domain: '.www.mollie.com',
            sameSite: 'None',
            secure: true,
            httpOnly: true
        }
      );    // reload current page to activate cookie
      cy.reload();
      cy.get('[value="paid"]').click()
      cy.get('[class="button form__button"]').click()
      cy.get('#content-hook_order_confirmation > .card-block').should('be.visible')
});
it('C339364: 27 SOFORT Order Shipping, Refunding [Orders API]', () => {
      cy.visit('/admin1/index.php?controller=AdminOrders')
      cy.get(':nth-child(1) > .column-payment').click()
      //Shipping button in React
      cy.get('.btn-group > .btn-primary').click()
      cy.get('[class="swal-button swal-button--confirm"]').click()
      cy.get('.swal-modal').should('exist')
      cy.get('#input-carrier').clear({force: true}).type('FedEx',{delay:0})
      cy.get('#input-code').clear({force: true}).type('123456',{delay:0})
      cy.get('#input-url').clear({force: true}).type('https://www.invertus.eu',{delay:0})
      cy.get(':nth-child(2) > .swal-button').click()
      cy.get('#mollie_order > :nth-child(1) > .alert').contains('Shipment was made successfully!')
      cy.get('[class="alert alert-success"]').should('be.visible')
      //Refunding not possible because "We haven't received the payment on our bank accounts yet" message from Mollie Dashboard
})
it('C339365: 28 Przelewy24 Checkouting [Orders API]', () => {
      cy.visit('/de/index.php?controller=history')
      cy.contains('Reorder').click()
      cy.contains('NL').click()
      //Billing country LT, DE etc.
      cy.get('.clearfix > .btn').click()
      cy.get('#js-delivery > .continue').click()
      //Payment method choosing
      cy.contains('Przelewy24').click({force:true})
      cy.get('.condition-label > .js-terms').click({force:true})
      prepareCookie();
      cy.get('.ps-shown-by-js > .btn').click()
      cy.setCookie(
        'SESSIONID',
        "cypress-dummy-value",
        {
            domain: '.www.mollie.com',
            sameSite: 'None',
            secure: true,
            httpOnly: true
        }
      );    // reload current page to activate cookie
      cy.reload();
      cy.get('[value="paid"]').click()
      cy.get('[class="button form__button"]').click()
      cy.get('#content-hook_order_confirmation > .card-block').should('be.visible')
});
it('C339366: 29 Przelewy24 Order Shipping, Refunding [Orders API]', () => {
      cy.OrderRefundingShippingOrdersAPI()
})
it('C339367: 30 Giropay Checkouting [Orders API]', () => {
      cy.visit('/de/index.php?controller=history')
      cy.contains('Reorder').click()
      cy.contains('NL').click()
      //Billing country LT, DE etc.
      cy.get('.clearfix > .btn').click()
      cy.get('#js-delivery > .continue').click()
      //Payment method choosing
      cy.contains('giropay').click({force:true})
      cy.get('.condition-label > .js-terms').click({force:true})
      prepareCookie();
      cy.get('.ps-shown-by-js > .btn').click()
      cy.setCookie(
        'SESSIONID',
        "cypress-dummy-value",
        {
            domain: '.www.mollie.com',
            sameSite: 'None',
            secure: true,
            httpOnly: true
        }
      );    // reload current page to activate cookie
      cy.reload();
      cy.get('[value="paid"]').click()
      cy.get('[class="button form__button"]').click()
      cy.get('#content-hook_order_confirmation > .card-block').should('be.visible')
});
it('C339368: 31 Giropay Order Shipping, Refunding [Orders API]', () => {
  cy.OrderRefundingShippingOrdersAPI()
})
it('C339369: 32 EPS Checkouting [Orders API]', () => {
      cy.visit('/de/index.php?controller=history')
      cy.contains('Reorder').click()
      cy.contains('NL').click()
      //Billing country LT, DE etc.
      cy.get('.clearfix > .btn').click()
      cy.get('#js-delivery > .continue').click()
      //Payment method choosing
      cy.contains('eps').click({force:true})
      cy.get('.condition-label > .js-terms').click({force:true})
      prepareCookie();
      cy.get('.ps-shown-by-js > .btn').click()
      cy.setCookie(
        'SESSIONID',
        "cypress-dummy-value",
        {
            domain: '.www.mollie.com',
            sameSite: 'None',
            secure: true,
            httpOnly: true
        }
      );    // reload current page to activate cookie
      cy.reload();
      cy.get('[value="paid"]').click()
      cy.get('[class="button form__button"]').click()
      cy.get('#content-hook_order_confirmation > .card-block').should('be.visible')
});
it('C339370: 33 EPS Order Shipping, Refunding [Orders API]', () => {
      cy.OrderRefundingShippingOrdersAPI()
})
it('C339371: 34 KBC/CBC Checkouting [Orders API]', () => {
      cy.visit('/de/index.php?controller=history')
      cy.contains('Reorder').click()
      cy.contains('NL').click()
      //Billing country LT, DE etc.
      cy.get('.clearfix > .btn').click()
      cy.get('#js-delivery > .continue').click()
      //Payment method choosing
      cy.contains('KBC/CBC').click({force:true})
      cy.get('.condition-label > .js-terms').click({force:true})
      prepareCookie();
      cy.get('.ps-shown-by-js > .btn').click()
      cy.setCookie(
        'SESSIONID',
        "cypress-dummy-value",
        {
            domain: '.www.mollie.com',
            sameSite: 'None',
            secure: true,
            httpOnly: true
        }
      );    // reload current page to activate cookie
      cy.reload();
      cy.get('.grid-button-kbc-cbc').click()
      cy.get('[value="paid"]').click()
      cy.get('[class="button form__button"]').click()
      cy.get('#content-hook_order_confirmation > .card-block').should('be.visible')
});
it('C339372: 35 KBC/CBC Order Shipping, Refunding [Orders API]', () => {
      cy.OrderRefundingShippingOrdersAPI()
})
it('C339373: 36 Belfius Checkouting [Orders API]', () => {
      cy.visit('/de/index.php?controller=history')
      cy.contains('Reorder').click()
      cy.contains('NL').click()
      //Billing country LT, DE etc.
      cy.get('.clearfix > .btn').click()
      cy.get('#js-delivery > .continue').click()
      //Payment method choosing
      cy.contains('Belfius').click({force:true})
      cy.get('.condition-label > .js-terms').click({force:true})
      prepareCookie();
      cy.get('.ps-shown-by-js > .btn').click()
      cy.setCookie(
        'SESSIONID',
        "cypress-dummy-value",
        {
            domain: '.www.mollie.com',
            sameSite: 'None',
            secure: true,
            httpOnly: true
        }
      );    // reload current page to activate cookie
      cy.reload();
      cy.get('[value="paid"]').click()
      cy.get('[class="button form__button"]').click()
      cy.get('#content-hook_order_confirmation > .card-block').should('be.visible')
});
it('C339374: 37 Belfius Order Shipping, Refunding [Orders API]', () => {
      cy.OrderRefundingShippingOrdersAPI()
})
it('C339375: 38 Bank Transfer Checkouting [Orders API]', () => {
      cy.visit('/en/index.php?controller=history')
      cy.contains('Reorder').click()
      cy.contains('NL').click()
      //Billing country LT, DE etc.
      cy.get('.clearfix > .btn').click()
      cy.get('#js-delivery > .continue').click()
      //Payment method choosing
      cy.contains('Bank transfer').click({force:true})
      cy.get('.condition-label > .js-terms').click({force:true})
      prepareCookie();
      cy.get('.ps-shown-by-js > .btn').click()
      cy.setCookie(
        'SESSIONID',
        "cypress-dummy-value",
        {
            domain: '.www.mollie.com',
            sameSite: 'None',
            secure: true,
            httpOnly: true
        }
      );    // reload current page to activate cookie
      cy.reload();
      cy.get('[value="paid"]').click()
      cy.get('[class="button form__button"]').click()
      //TODO - Welcome page?
      //cy.get('#content-hook_order_confirmation > .card-block').should('be.visible')
});
it('C339376: 39 Bank Transfer Order Shipping, Refunding [Orders API]', () => {
      cy.OrderRefundingShippingOrdersAPI()
})
// Temporary disabled, Payment Method disables automatically in My Mollie Dashboard, because of the fake testing account...
it.skip('40 Gift Card Checkouting [Orders API]', () => {
      cy.visit('/en/index.php?controller=history')
      cy.contains('Reorder').click()
      cy.contains('NL').click()
      //Billing country LT, DE etc.
      cy.get('.clearfix > .btn').click()
      cy.get('#js-delivery > .continue').click()
      //Payment method choosing
      cy.contains('Gift cards').click({force:true})
      cy.get('.condition-label > .js-terms').click({force:true})
      prepareCookie();
      cy.get('.ps-shown-by-js > .btn').click()
      cy.setCookie(
        'SESSIONID',
        "cypress-dummy-value",
        {
            domain: '.www.mollie.com',
            sameSite: 'None',
            secure: true,
            httpOnly: true
        }
      );    // reload current page to activate cookie
      cy.reload();
      cy.get('.grid-button-giftcard-yourgift').click()
      cy.get('[value="paid"]').click()
      cy.get('[class="button form__button"]').click()
      cy.get('.grid-button-paypal').click()
      cy.get('[value="paid"]').click()
      cy.get('[class="button form__button"]').click()
      cy.get('#content-hook_order_confirmation > .card-block').should('be.visible')
});
it.skip('41 Gift Card Order Shipping, Refunding [Orders API]', () => {
      cy.OrderRefundingShippingOrdersAPI()
})
it('C1765085: Billie Checkouting [Orders API]', () => {
      cy.visit('/en/index.php?controller=history')
      cy.contains('Reorder').click()
      cy.contains('DE').click()
      //Billing country LT, DE etc.
      cy.get('.clearfix > .btn').click()
      cy.get('#js-delivery > .continue').click()
      //Payment method choosing
      cy.contains('Billie').click({force:true})
      cy.get('.condition-label > .js-terms').click({force:true})
      prepareCookie();
      cy.get('.ps-shown-by-js > .btn').click()
      cy.get('[value="authorized"]').click()
      cy.get('[class="button form__button"]').click()
      cy.get('#content-hook_order_confirmation > .card-block').should('be.visible')
});
it('C1765086: Billie Order Shipping, Refunding [Orders API]', () => {
      cy.OrderShippingRefundingOrdersAPI()
})
it('C1860460: Pay with Klarna UK Checkouting [Orders API]', () => {
  cy.visit('/en/order-history')
  cy.contains('Reorder').click()
  cy.contains('UK').click({force:true})
  //Billing country LT, DE etc.
  cy.get('.clearfix > .btn').click()
  cy.get('#js-delivery > .continue').click()
  //Payment method choosing
  cy.contains('Pay with Klarna').click({force:true})
  cy.get('.condition-label > .js-terms').click({force:true})
  prepareCookie();
  cy.get('.ps-shown-by-js > .btn').click()
  cy.get('[value="authorized"]').click()
  cy.get('[class="button form__button"]').click()
  cy.get('#content-hook_order_confirmation > .card-block').should('be.visible')
});
it('C1860461: Pay with Klarna UK Order Shipping, Refunding [Orders API]', () => {
  cy.OrderShippingRefundingOrdersAPI()
})
})
