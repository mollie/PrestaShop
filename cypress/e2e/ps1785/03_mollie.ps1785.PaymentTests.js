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
  cy.get('#email').type('demo@demo.com',{delay: 0, log: false})
  cy.get('#passwd').type('demodemo',{delay: 0, log: false})
  cy.get('#submit_login').click().wait(1000).as('Connection successsful')
  cy.visit('/en/my-account')
  cy.get('#login-form [name="email"]').eq(0).type('demo@demo.com')
  cy.get('#login-form [name="password"]').eq(0).type('demodemo')
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
describe('PS1785 Tests Suite', () => {
  beforeEach(() => {
      login('MollieBOFOLoggingIn')
      cy.viewport(1920,1080)
  })
it('C339342: 05 Vouchers Checkouting [Orders API]', () => {
      cy.visit('/de/index.php?controller=history')
      cy.get('a').click()
      cy.contains('Reorder').click()
      cy.contains('LT').click()
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
it('C339343: 06 Vouchers Order BO Refunding, Shipping (Paid part only) [Orders API]', () => {
      cy.OrderRefundingShippingOrdersAPI()
      cy.get('[class="card-body"]').find('[class="alert alert-warning"]').should('exist') //additional checking if the warning alert for vouchers exist
})
it('C339344: 07 Bancontact Checkouting [Orders API]', () => {
      cy.visit('/de/index.php?controller=history')
      cy.get('a').click()
      cy.contains('Reorder').click()
      cy.contains('LT').click()
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
      cy.get('a').click()
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
      cy.get('a').click()
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
      cy.get('a').click()
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
      cy.get('a').click()
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
      cy.get('a').click()
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
      cy.get('a').click()
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
it('C339357: 20 IN3 Checkouting [Orders API]', () => {
      cy.visit('/de/index.php?controller=history')
      cy.get('a').click()
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
      cy.visit('/de/')
      cy.contains('Hummingbird printed sweater').click()
      cy.get('[class="btn btn-primary add-to-cart"]').click()
      cy.get('.cart-content-btn > .btn-primary').click()
      cy.get('.text-sm-center > .btn').click()
      cy.contains('NL').click()
      //Billing country LT, DE etc.
      cy.get('.clearfix > .btn').click()
      cy.get('#js-delivery > .continue').click()
      //Payment method choosing
      cy.contains('in3').should('not.exist')
      cy.get('.logo').click()
      cy.get('.blockcart').click()
      cy.get('.remove-from-cart > .material-icons').click()
})
it('C339360: 23 IN3 Checking that IN3 logo exists OK [Orders API]', () => {
      cy.visit('/admin1/')
      cy.OpeningModuleDashboardURL()
      cy.get('[href="#advanced_settings"]').click({force:true})
      cy.get('[name="MOLLIE_IMAGES"]').select('big')
      cy.get('[type="submit"]').first().click({force:true})
      cy.get('[class="alert alert-success"]').should('be.visible')
      cy.visit('/de/index.php?controller=history')
      cy.get('a').click()
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
      cy.get('a').click()
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
      cy.get('a').click()
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
      cy.get('a').click()
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
      cy.get('a').click()
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
      cy.get('a').click()
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
      cy.get('a').click()
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
      cy.get('a').click()
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
      cy.get('a').click()
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
      cy.get('a').click()
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
it('C339377: 42 [SWITCH TO PAYMENTS API] Enabling All payments in Module BO [Payments API]', () => {
      cy.visit('/admin1/')
      cy.OpeningModuleDashboardURL()
      cy.ConfPaymentsAPI1784()
      cy.get('[type="submit"]').first().click({force:true})
      cy.get('[class="alert alert-success"]').should('be.visible')
})
it('C339378: 43 Check if Bancontact QR payment dropdown exists [Payments API]', () => {
      cy.visit('/admin1/')
      cy.OpeningModuleDashboardURL()
      cy.get('[name="MOLLIE_BANCONTACT_QR_CODE_ENABLED"]').should('exist')
})
it('C339379: 44 Bancontact Checkouting [Payments API]', () => {
      cy.visit('/de/index.php?controller=history')
      cy.get('a').click()
      //
      cy.contains('Reorder').click()
      cy.contains('LT').click()
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
it('C339380: 45 Bancontact Order BO Refunding, Partial Refunding [Payments API]', () => {
      cy.OrderRefundingPartialPaymentsAPI()
})
it('C339381: 46 iDEAL Checkouting [Payments API]', () => {
      cy.visit('/en/index.php?controller=history')
      cy.get('a').click()
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
it('C339382: 47 iDEAL Order BO Refunding, Partial Refunding [Payments API]', () => {
      cy.OrderRefundingPartialPaymentsAPI()
})
it('C339383: 48 Credit Card Checkouting [Payments API]', () => {
      cy.visit('/en/index.php?controller=history')
      cy.get('a').click()
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
it('C339384: 49 Credit Card Order BO Refunding, Partial Refunding [Payments API]', () => {
      cy.OrderRefundingPartialPaymentsAPI()
})
it('C339385: 50 Credit Card Guest Checkouting [Payments API]', () => {
      cy.clearCookies()
      //Payments API item
      cy.visit('/en/', { headers: {"Accept-Encoding": "gzip, deflate"}})
      cy.get('[class="h3 product-title"]').eq(0).click()
      cy.get('.add > .btn').click()
      cy.get('.cart-content-btn > .btn-primary').click()
      cy.get('.text-sm-center > .btn').click()
      // Creating random user all the time
      cy.get(':nth-child(1) > .custom-radio > input').check()
      cy.get('#field-firstname').type('AUT',{delay:0})
      cy.get(':nth-child(3) > .col-md-6 > .form-control').type('AUT',{delay:0})
      const uuid = () => Cypress._.random(0, 1e6)
      const id = uuid()
      const testname = `testemail${id}@testing.com`
      cy.get(':nth-child(4) > .col-md-6 > .form-control').type(testname, {delay: 0})
      cy.get(':nth-child(6) > .col-md-6 > .input-group > .form-control').type('123456',{delay:0})
      cy.get(':nth-child(9) > .col-md-6 > .custom-checkbox > label > input').check()
      cy.get('#customer-form > .form-footer > .continue').click()
      cy.reload()
      cy.get(':nth-child(6) > .col-md-6 > .form-control').type('123456',{delay:0})
      cy.get(':nth-child(7) > .col-md-6 > .form-control').type('123456',{delay:0}).as('vat number')
      cy.get(':nth-child(8) > .col-md-6 > .form-control').type('ADDR',{delay:0}).as('address')
      cy.get('#field-address2').type('ADDR2',{delay:0}).as('address2')
      cy.get(':nth-child(10) > .col-md-6 > .form-control').type('54469',{delay:0}).as('zip')
      cy.get(':nth-child(11) > .col-md-6 > .form-control').type('CIT',{delay:0}).as('city')
      cy.get(':nth-child(12) > .col-md-6 > .form-control').select('Lithuania').as('country')
      cy.get(':nth-child(13) > .col-md-6 > .form-control').type('+370 000',{delay:0}).as('telephone')
      cy.get('.form-footer > .continue').click()
      cy.get('#js-delivery > .continue').click()
      cy.contains('Card').click({force:true})
      //Credit card inputing
      cy.CreditCardFillingIframe()
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
it('C339386: 51 Credit Card Guest Checkouting with not 3DS secure card [Payments API]', () => {
      cy.clearCookies()
      //Payments API item
      cy.visit('/en/', { headers: {"Accept-Encoding": "gzip, deflate"}})
      cy.get('[class="h3 product-title"]').eq(0).click()
      cy.get('.add > .btn').click()
      cy.get('.cart-content-btn > .btn-primary').click()
      cy.get('.text-sm-center > .btn').click()
      // Creating random user all the time
      cy.get(':nth-child(1) > .custom-radio > input').check()
      cy.get('#field-firstname').type('AUT',{delay:0})
      cy.get(':nth-child(3) > .col-md-6 > .form-control').type('AUT',{delay:0})
      const uuid = () => Cypress._.random(0, 1e6)
      const id = uuid()
      const testname = `testemail${id}@testing.com`
      cy.get(':nth-child(4) > .col-md-6 > .form-control').type(testname, {delay: 0})
      cy.get(':nth-child(6) > .col-md-6 > .input-group > .form-control').type('123456',{delay:0})
      cy.get(':nth-child(9) > .col-md-6 > .custom-checkbox > label > input').check()
      cy.get('#customer-form > .form-footer > .continue').click()
      cy.reload()
      cy.get(':nth-child(6) > .col-md-6 > .form-control').type('123456',{delay:0})
      cy.get(':nth-child(7) > .col-md-6 > .form-control').type('123456',{delay:0}).as('vat number')
      cy.get(':nth-child(8) > .col-md-6 > .form-control').type('ADDR',{delay:0}).as('address')
      cy.get(':nth-child(10) > .col-md-6 > .form-control').type('54469',{delay:0}).as('zip')
      cy.get(':nth-child(11) > .col-md-6 > .form-control').type('CIT',{delay:0}).as('city')
      cy.get(':nth-child(12) > .col-md-6 > .form-control').select('Lithuania').as('country')
      cy.get(':nth-child(13) > .col-md-6 > .form-control').type('+370 000',{delay:0}).as('telephone')
      cy.get('.form-footer > .continue').click()
      cy.get('#js-delivery > .continue').click()
      cy.contains('Card').click({force:true})
      //Credit card inputing
      cy.NotSecureCreditCardFillingIframe()
      cy.get('.condition-label > .js-terms').click({force:true})
      cy.get('.ps-shown-by-js > .btn').click()
      cy.get('#content-hook_order_confirmation > .card-block').should('be.visible')
})
it('C339387: 52 Paypal Checkouting [Payments API]', () => {
      cy.visit('/de/index.php?controller=history')
      cy.get('a').click()
      //
      cy.contains('Reorder').click()
      cy.contains('LT').click()
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
it('C339388: 53 Paypal BO Refunding, Partial Refunding [Payments API]', () => {
      cy.visit('/admin1/index.php?controller=AdminOrders')
      cy.get(':nth-child(1) > .column-payment').click()
      //Check partial refunding on Payments API - seems that Paypal has only Partial Refunding without Refund button
      cy.get('.form-inline > :nth-child(2) > .input-group > .form-control').type('1.51',{delay:0})
      cy.get(':nth-child(2) > .input-group > .input-group-btn > .btn').click()
      cy.get('.swal-modal').should('exist')
      cy.get(':nth-child(2) > .swal-button').click()
      cy.get('#mollie_order > :nth-child(1) > .alert').contains('Refund was made successfully!')
});
it('C339389: 54 SOFORT Checkouting [Payments API]', () => {
      cy.visit('/de/index.php?controller=history')
      cy.get('a').click()
      //
      cy.contains('Reorder').click()
      cy.contains('LT').click()
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
it('C339390: 55 SOFORT BO Refunding, Partial Refunding [Payments API]', () => {
      cy.visit('/admin1/index.php?controller=AdminOrders')
      cy.get(':nth-child(1) > .column-payment').click()
      cy.get('#mollie_order > :nth-child(1)').should('exist')
      //Refunding is unavailable - information from Mollie Dashboard - but checking the UI itself
});
it('C339391: 56 Przelewy24 Checkouting [Payments API]', () => {
      cy.visit('/de/index.php?controller=history')
      cy.get('a').click()
      //
      cy.contains('Reorder').click()
      cy.contains('LT').click()
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
      cy.get('.input-float > input').type('testing@testing.com')
      cy.get('[class="button form__button"]').click()
      cy.get('[value="paid"]').click()
      cy.get('[class="button form__button"]').click()
      cy.get('#content-hook_order_confirmation > .card-block').should('be.visible')
});
it('C339392: 57 Przelewy24 BO Refunding, Partial Refunding [Payments API]', () => {
      cy.OrderRefundingPartialPaymentsAPI()
});
it('C339393: 58 Giropay Checkouting [Payments API]', () => {
      cy.visit('/de/index.php?controller=history')
      cy.get('a').click()
      //
      cy.contains('Reorder').click()
      cy.contains('LT').click()
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
it('C339394: 59 Giropay BO Refunding, Partial Refunding [Payments API]', () => {
      cy.OrderRefundingPartialPaymentsAPI()
});
it('C339395: 60 EPS Checkouting [Payments API]', () => {
      cy.visit('/de/index.php?controller=history')
      cy.get('a').click()
      //
      cy.contains('Reorder').click()
      cy.contains('LT').click()
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
it('C339396: 61 EPS BO Refunding, Partial Refunding [Payments API]', () => {
      cy.OrderRefundingPartialPaymentsAPI()
});
it('C339397: 62 KBC/CBC Checkouting [Payments API]', () => {
      cy.visit('/en/index.php?controller=history')
      cy.get('a').click()
      //
      cy.contains('Reorder').click()
      cy.contains('LT').click()
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
it('C339398: 63 KBC/CBC BO Refunding, Partial Refunding [Payments API]', () => {
      cy.OrderRefundingPartialPaymentsAPI()
});
it('C339399: 64 Belfius Checkouting [Payments API]', () => {
      cy.visit('/en/index.php?controller=history')
      cy.get('a').click()
      //
      cy.contains('Reorder').click()
      cy.contains('LT').click()
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
it('C339400: 65 Belfius BO Refunding, Partial Refunding [Payments API]', () => {
      cy.OrderRefundingPartialPaymentsAPI()
});
it.skip('C339401: 66 Bank Transfer Checkouting [Payments API]', () => { // skipping temporary, bug
      cy.visit('/en/index.php?controller=history')
      cy.get('a').click()
      //
      cy.contains('Reorder').click()
      cy.contains('LT').click()
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
      cy.get('#content-hook_order_confirmation > .card-block').should('be.visible')
});
it.skip('C339402: 67 Bank Transfer BO Refunding, Partial Refunding [Payments API]', () => { // skipping temporary, bug
      cy.OrderRefundingPartialPaymentsAPI()
});
})
