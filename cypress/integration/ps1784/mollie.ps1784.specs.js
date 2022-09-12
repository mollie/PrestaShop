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
      //switching the multistore PS1784
      cy.get('#header_shop > .dropdown').click()
      cy.get('.open > .dropdown-menu').find('[class="shop"]').eq(1).find('[href]').eq(0).click()
      cy.visit('/SHOP2/index.php?controller=my-account')
      cy.get('#login-form [name="email"]').eq(0).type('demo@demo.com')
      cy.get('#login-form [name="password"]').eq(0).type('demodemo')
      cy.get('#login-form [type="submit"]').eq(0).click({force:true})
      cy.get('#history-link > .link-item').click()
      })
      }
describe('PS1784 Tests Suite', () => {
  beforeEach(() => {
      cy.viewport(1920,1080)
      login('MollieBOFOLoggingIn')
  })
it('01 Connecting test API successsfully', () => {
      cy.visit('/admin1/')
      //Enabling Multistore context for PS1784
      cy.get('#subtab-AdminMollieModule > .link').click()
      cy.get('[name="activateModule"]').check()
      cy.get('#MOLLIE_ACCOUNT_SWITCH_on').click()
      cy.get('#MOLLIE_API_KEY_TEST').type((Cypress.env('MOLLIE_TEST_API_KEY')),{delay: 0, log: false})
      cy.get('#module_form_submit_btn').click()
})
it('02 Enabling Mollie carriers successfully', () => {
      cy.visit('/admin1/')
      cy.get('[id="subtab-AdminPaymentPreferences"]').find('[href]').eq(0).click({force:true})
      cy.get('[class="js-multiple-choice-table-select-column"]').eq(6).click()
      cy.get('[class="btn btn-primary"]').eq(3).click()
})
it('03 Enabling All payments in Module BO [Orders API]', () => {
      cy.visit('/admin1/')
      cy.get('#subtab-AdminMollieModule > .link').click()
      cy.ConfOrdersAPI1784()
      cy.get('[type="submit"]').first().click()
      cy.get('[class="alert alert-success"]').should('be.visible')
})
it('04 Checking the Advanced Settings tab, verifying the Front-end components, Saving the form, checking if there are no Errors in Console', () => {
      cy.visit('/admin1/')
      cy.get('#subtab-AdminMollieModule > .link').click()
      cy.get('[href="#advanced_settings"]').click()
      cy.get('[id="MOLLIE_PAYMENTSCREEN_LOCALE"]').should('be.visible')
      cy.get('[id="MOLLIE_SEND_ORDER_CONFIRMATION"]').should('be.visible')
      cy.get('[id="MOLLIE_KLARNA_INVOICE_ON"]').should('be.visible')
      cy.get('[class="help-block"]').should('be.visible')
      cy.get('[id="MOLLIE_STATUS_AWAITING"]').should('be.visible')
      cy.get('[id="MOLLIE_STATUS_PAID"]').should('be.visible')
      cy.get('[name="MOLLIE_MAIL_WHEN_PAID"]').should('exist')
      cy.get('[name="MOLLIE_MAIL_WHEN_COMPLETED"]').should('exist')
      cy.get('[name="MOLLIE_STATUS_COMPLETED"]').should('exist')
      cy.get('[name="MOLLIE_MAIL_WHEN_CANCELED"]').should('exist')
      cy.get('[name="MOLLIE_STATUS_CANCELED"]').should('exist')
      cy.get('[name="MOLLIE_MAIL_WHEN_EXPIRED"]').should('exist')
      cy.get('[name="MOLLIE_STATUS_EXPIRED"]').should('exist')
      cy.get('[name="MOLLIE_MAIL_WHEN_REFUNDED"]').should('exist')
      cy.get('[name="MOLLIE_STATUS_REFUNDED"]').should('exist')
      cy.get('[name="MOLLIE_STATUS_OPEN"]').should('exist')
      cy.get('[name="MOLLIE_MAIL_WHEN_SHIPPING"]').should('exist')
      cy.get('[name="MOLLIE_STATUS_SHIPPING"]').should('exist')
      cy.get('[name="MOLLIE_STATUS_PARTIAL_REFUND"]').should('exist')
      cy.get('[name="MOLLIE_IMAGES"]').should('exist')
      cy.get('[name="MOLLIE_CSS"]').should('exist')
      cy.get('[id="MOLLIE_TRACKING_URLS__container"]').should('exist')
      cy.get('[id="MOLLIE_AS_MAIN_info"]').should('exist')
      cy.get('[id="MOLLIE_AS_STATUSES_info"]').should('exist')
      cy.get('[name="MOLLIE_DISPLAY_ERRORS"]').should('exist')
      cy.get('[name="MOLLIE_DEBUG_LOG"]').should('exist')
      cy.get('#module_form_submit_btn').click() //checking the saving
      cy.get('[class="alert alert-success"]').should('be.visible') //checking if saving returns green alert
      //cy.window() will check if there are no Errors in console
});
it('05 Bancontact Checkouting [Orders API]', () => {
      cy.visit('/SHOP2/de/index.php?controller=history')
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
it('06 Bancontact Order BO Shiping, Refunding [Orders API]', () => {
      cy.visit('/admin1/index.php?controller=AdminOrders')
      cy.get(':nth-child(1) > .column-payment').click()
      //Refunding dropdown in React
      cy.get('.btn-group-action > .btn-group > .dropdown-toggle').eq(0).click()
      cy.get('[role="button"]').eq(2).click()
      cy.get('[class="swal-button swal-button--confirm"]').click()
      cy.get('[class="alert alert-success"]').should('be.visible')
      //Shipping button in React
      cy.get('.btn-group > [title=""]').eq(0).click()
      cy.get('[class="swal-button swal-button--confirm"]').click()
      cy.get('.swal-modal').should('exist')
      cy.get('#input-carrier').clear({force: true}).type('FedEx',{delay:0})
      cy.get('#input-code').clear({force: true}).type('123456',{delay:0})
      cy.get('#input-url').clear({force: true}).type('https://www.invertus.eu',{delay:0})
      cy.get(':nth-child(2) > .swal-button').click()
      cy.get('#mollie_order > :nth-child(1) > .alert').contains('Shipment was made successfully!')
      cy.get('[class="alert alert-success"]').should('be.visible')
})
it('07 iDEAL Checkouting [Orders API]', () => {
      cy.visit('/SHOP2/de/index.php?controller=history')
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
it('08 iDEAL Order BO Shiping, Refunding [Orders API]', () => {
      cy.visit('/admin1/index.php?controller=AdminOrders')
      cy.get(':nth-child(1) > .column-payment').click()
      //Refunding dropdown in React
      cy.get('.btn-group-action > .btn-group > .dropdown-toggle').eq(0).click()
      cy.get('[role="button"]').eq(2).click()
      cy.get('[class="swal-button swal-button--confirm"]').click()
      cy.get('[class="alert alert-success"]').should('be.visible')
      //Shipping button in React
      cy.get('.btn-group > [title=""]').eq(0).click()
      cy.get('[class="swal-button swal-button--confirm"]').click()
      cy.get('.swal-modal').should('exist')
      cy.get('#input-carrier').clear({force: true}).type('FedEx',{delay:0})
      cy.get('#input-code').clear({force: true}).type('123456',{delay:0})
      cy.get('#input-url').clear({force: true}).type('https://www.invertus.eu',{delay:0})
      cy.get(':nth-child(2) > .swal-button').click()
      cy.get('#mollie_order > :nth-child(1) > .alert').contains('Shipment was made successfully!')
      cy.get('[class="alert alert-success"]').should('be.visible')
})
it('09 Klarna Slice It Checkouting [Orders API]', () => {
      cy.visit('/SHOP2/de/index.php?controller=history')
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
it('10 Klarna Slice It Order BO Shiping, Refunding [Orders API]', () => {
      cy.visit('/admin1/index.php?controller=AdminOrders')
      cy.get(':nth-child(1) > .column-payment').click()
      //Shipping button in React
      cy.get('.btn-group > [title=""]').eq(0).click()
      cy.get('[class="swal-button swal-button--confirm"]').click()
      cy.get('.swal-modal').should('exist')
      cy.get('#input-carrier').clear({force: true}).type('FedEx',{delay:0})
      cy.get('#input-code').clear({force: true}).type('123456',{delay:0})
      cy.get('#input-url').clear({force: true}).type('https://www.invertus.eu',{delay:0})
      cy.get(':nth-child(2) > .swal-button').click()
      cy.get('#mollie_order > :nth-child(1) > .alert').contains('Shipment was made successfully!')
      cy.get('[class="alert alert-success"]').should('be.visible')
      //Refunding dropdown in React
      cy.get('.btn-group-action > .btn-group > .dropdown-toggle').eq(0).click()
      cy.get('[role="button"]').eq(2).click()
      cy.get('[class="swal-button swal-button--confirm"]').click()
      cy.get('[class="alert alert-success"]').should('be.visible')
})
it('11 Klarna Pay Later Checkouting [Orders API]', () => {
      cy.visit('/SHOP2/de/index.php?controller=history')
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
it('12 Klarna Pay Later Order BO Shiping, Refunding [Orders API]', () => {
      cy.visit('/admin1/index.php?controller=AdminOrders')
      cy.get(':nth-child(1) > .column-payment').click()
      //Shipping button in React
      cy.get('.btn-group > [title=""]').eq(0).click()
      cy.get('[class="swal-button swal-button--confirm"]').click()
      cy.get('.swal-modal').should('exist')
      cy.get('#input-carrier').clear({force: true}).type('FedEx',{delay:0})
      cy.get('#input-code').clear({force: true}).type('123456',{delay:0})
      cy.get('#input-url').clear({force: true}).type('https://www.invertus.eu',{delay:0})
      cy.get(':nth-child(2) > .swal-button').click()
      cy.get('#mollie_order > :nth-child(1) > .alert').contains('Shipment was made successfully!')
      cy.get('[class="alert alert-success"]').should('be.visible')
      //Refunding dropdown in React
      cy.get('.btn-group-action > .btn-group > .dropdown-toggle').eq(0).click()
      cy.get('[role="button"]').eq(2).click()
      cy.get('[class="swal-button swal-button--confirm"]').click()
      cy.get('[class="alert alert-success"]').should('be.visible')
})
it('Klarna Pay Now Checkouting [Orders API]', () => {
  cy.visit('/SHOP2/de/index.php?controller=history')
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
it('Klarna Pay Now Order BO Shiping, Refunding [Orders API]', () => {
  cy.visit('/admin1/index.php?controller=AdminOrders')
  cy.get(':nth-child(1) > .column-payment').click()
  //Shipping button in React
  cy.get('.btn-group > [title=""]').eq(0).click()
  cy.get('[class="swal-button swal-button--confirm"]').click()
  cy.get('.swal-modal').should('exist')
  cy.get('#input-carrier').clear({force: true}).type('FedEx',{delay:0})
  cy.get('#input-code').clear({force: true}).type('123456',{delay:0})
  cy.get('#input-url').clear({force: true}).type('https://www.invertus.eu',{delay:0})
  cy.get(':nth-child(2) > .swal-button').click()
  cy.get('#mollie_order > :nth-child(1) > .alert').contains('Shipment was made successfully!')
  cy.get('[class="alert alert-success"]').should('be.visible')
  //Refunding dropdown in React
  cy.get('.btn-group-action > .btn-group > .dropdown-toggle').eq(0).click()
  cy.get('[role="button"]').eq(2).click()
  cy.get('[class="swal-button swal-button--confirm"]').click()
  cy.get('[class="alert alert-success"]').should('be.visible')
})
it('13 Credit Card Checkouting [Orders API]', () => {
      //Enabling the Single-Click for now
      cy.visit('/admin1/')
      cy.get('#subtab-AdminMollieModule > .link').click()
      cy.get('#MOLLIE_SINGLE_CLICK_PAYMENT_on').click()
      cy.get('[type="submit"]').first().click()
      cy.get('[class="alert alert-success"]').should('be.visible')
      cy.visit('/SHOP2/en/index.php?controller=history')
      cy.get('a').click()
      cy.contains('Reorder').click()
      //Billing country LT, DE etc.
      cy.get('.clearfix > .btn').click()
      cy.get('#js-delivery > .continue').click()
      //Payment method choosing
      cy.contains('Credit card').click({force:true})
      //Credit card inputing
      cy.frameLoaded('[data-testid=mollie-container--cardHolder] > iframe')
      cy.enter('[data-testid=mollie-container--cardHolder] > iframe').then(getBody => {
      getBody().find('#cardHolder').clear({force: true}).type('TEST TEEESSSTT')
      })
      cy.enter('[data-testid=mollie-container--cardNumber] > iframe').then(getBody => {
      getBody().find('#cardNumber').clear({force: true}).type('5555555555554444')
      })
      cy.enter('[data-testid=mollie-container--expiryDate] > iframe').then(getBody => {
      getBody().find('#expiryDate').clear({force: true}).type('1222')
      })
      cy.enter('[data-testid=mollie-container--verificationCode] > iframe').then(getBody => {
      getBody().find('#verificationCode').clear({force: true}).type('222')
      })
      cy.get('.condition-label > .js-terms').click({force:true})
      cy.get('#mollie-save-card').check()
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
it('14 Check if customerId is passed during the 2nd payment using Single Click Payment [Orders API]', () => {
  cy.visit('/SHOP2/en/index.php?controller=history')
  cy.get('a').click()
  cy.contains('Reorder').click()
  //Billing country LT, DE etc.
  cy.get('.clearfix > .btn').click()
  cy.get('#js-delivery > .continue').click()
  //Payment method choosing
  cy.contains('Credit card').click({force:true})
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
      //temporary disabling the flow, might be bug
      // cy.get('[value="paid"]').click()
      // cy.get('[class="button form__button"]').click()
      // cy.get('#content-hook_order_confirmation > .card-block').should('be.visible')
      cy.visit('/admin1/')
      //Disabling the single-click - no need again
      cy.get('#subtab-AdminMollieModule > .link').click()
      cy.get('#MOLLIE_SINGLE_CLICK_PAYMENT_off').click()
      cy.get('[type="submit"]').first().click()
      cy.get('[class="alert alert-success"]').should('be.visible')
})
it('15 Credit Card Order BO Shiping, Refunding [Orders API]', () => {
      cy.visit('/admin1/index.php?controller=AdminOrders')
      cy.get(':nth-child(1) > .column-payment').click()
      //Refunding dropdown in React
      cy.get('.btn-group-action > .btn-group > .dropdown-toggle').eq(0).click()
      cy.get('[role="button"]').eq(2).click()
      cy.get('[class="swal-button swal-button--confirm"]').click()
      cy.get('[class="alert alert-success"]').should('be.visible')
      //Shipping button in React
      cy.get('.btn-group > [title=""]').eq(0).click()
      cy.get('[class="swal-button swal-button--confirm"]').click()
      cy.get('.swal-modal').should('exist')
      cy.get('#input-carrier').clear({force: true}).type('FedEx',{delay:0})
      cy.get('#input-code').clear({force: true}).type('123456',{delay:0})
      cy.get('#input-url').clear({force: true}).type('https://www.invertus.eu',{delay:0})
      cy.get(':nth-child(2) > .swal-button').click()
      cy.get('#mollie_order > :nth-child(1) > .alert').contains('Shipment was made successfully!')
      cy.get('[class="alert alert-success"]').should('be.visible')
})
it('16 IN3 Checkouting [Orders API]', () => {
  cy.visit('/SHOP2/de/index.php?controller=history')
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
it('17 IN3 Order BO Shiping, Refunding [Orders API]', () => {
  cy.visit('/admin1/index.php?controller=AdminOrders')
  cy.get(':nth-child(1) > .column-payment').click()
  //Refunding dropdown in React
  cy.get('.btn-group-action > .btn-group > .dropdown-toggle').eq(0).click()
  cy.get('[role="button"]').eq(2).click()
  cy.get('[class="swal-button swal-button--confirm"]').click()
  cy.get('[class="alert alert-success"]').should('be.visible')
  //Shipping button in React
  cy.get('.btn-group > [title=""]').eq(0).click()
  cy.get('[class="swal-button swal-button--confirm"]').click()
  cy.get('.swal-modal').should('exist')
  cy.get('#input-carrier').clear({force: true}).type('FedEx',{delay:0})
  cy.get('#input-code').clear({force: true}).type('123456',{delay:0})
  cy.get('#input-url').clear({force: true}).type('https://www.invertus.eu',{delay:0})
  cy.get(':nth-child(2) > .swal-button').click()
  cy.get('#mollie_order > :nth-child(1) > .alert').contains('Shipment was made successfully!')
  cy.get('[class="alert alert-success"]').should('be.visible')
})
it('18 IN3 should not be shown under 5000 EUR [Orders API]', () => {
  cy.visit('/SHOP2/de/')
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
it('19 IN3 Checking that IN3 logo exists OK [Orders API]', () => {
  cy.visit('/admin1/')
  cy.get('#subtab-AdminMollieModule > .link').click()
  cy.get('[href="#advanced_settings"]').click()
  cy.get('[name="MOLLIE_IMAGES"]').select('big')
  cy.get('[type="submit"]').first().click()
  cy.get('[class="alert alert-success"]').should('be.visible')
  cy.visit('/SHOP2/de/index.php?controller=history')
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
  cy.get('#subtab-AdminMollieModule > .link').click()
  cy.get('[href="#advanced_settings"]').click()
  cy.get('[name="MOLLIE_IMAGES"]').select('hide')
  cy.get('[type="submit"]').first().click()
  cy.get('[class="alert alert-success"]').should('be.visible')
})
it('Paypal Checkouting [Orders API]', () => {
  cy.visit('/SHOP2/de/index.php?controller=history')
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
it('Paypal Order Shipping, Refunding [Orders API]', () => {
  cy.visit('/admin1/index.php?controller=AdminOrders')
  cy.get(':nth-child(1) > .column-payment').click()
  //Refunding dropdown in React
  cy.get('.btn-group-action > .btn-group > .dropdown-toggle').eq(0).click()
  cy.get('[role="button"]').eq(2).click()
  cy.get('[class="swal-button swal-button--confirm"]').click()
  cy.get('[class="alert alert-success"]').should('be.visible')
  //Shipping button in React
  cy.get('.btn-group > [title=""]').eq(0).click()
  cy.get('[class="swal-button swal-button--confirm"]').click()
  cy.get('.swal-modal').should('exist')
  cy.get('#input-carrier').clear({force: true}).type('FedEx',{delay:0})
  cy.get('#input-code').clear({force: true}).type('123456',{delay:0})
  cy.get('#input-url').clear({force: true}).type('https://www.invertus.eu',{delay:0})
  cy.get(':nth-child(2) > .swal-button').click()
  cy.get('#mollie_order > :nth-child(1) > .alert').contains('Shipment was made successfully!')
  cy.get('[class="alert alert-success"]').should('be.visible')
})
it('SOFORT Checkouting [Orders API]', () => {
  cy.visit('/SHOP2/de/index.php?controller=history')
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
it('SOFORT Order Shipping, Refunding [Orders API]', () => {
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
it('Przelewy24 Checkouting [Orders API]', () => {
  cy.visit('/SHOP2/de/index.php?controller=history')
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
it('Przelewy24 Order Shipping, Refunding [Orders API]', () => {
  cy.visit('/admin1/index.php?controller=AdminOrders')
  cy.get(':nth-child(1) > .column-payment').click()
      //Refunding dropdown in React
      cy.get('.btn-group-action > .btn-group > .dropdown-toggle').eq(0).click()
      cy.get('[role="button"]').eq(2).click()
      cy.get('[class="swal-button swal-button--confirm"]').click()
      cy.get('[class="alert alert-success"]').should('be.visible')
      //Shipping button in React
      cy.get('.btn-group > [title=""]').eq(0).click()
      cy.get('[class="swal-button swal-button--confirm"]').click()
      cy.get('.swal-modal').should('exist')
      cy.get('#input-carrier').clear({force: true}).type('FedEx',{delay:0})
      cy.get('#input-code').clear({force: true}).type('123456',{delay:0})
      cy.get('#input-url').clear({force: true}).type('https://www.invertus.eu',{delay:0})
      cy.get(':nth-child(2) > .swal-button').click()
      cy.get('#mollie_order > :nth-child(1) > .alert').contains('Shipment was made successfully!')
      cy.get('[class="alert alert-success"]').should('be.visible')
})
it('Giropay Checkouting [Orders API]', () => {
  cy.visit('/SHOP2/de/index.php?controller=history')
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
it('Giropay Order Shipping, Refunding [Orders API]', () => {
  cy.visit('/admin1/index.php?controller=AdminOrders')
  cy.get(':nth-child(1) > .column-payment').click()
      //Refunding dropdown in React
      cy.get('.btn-group-action > .btn-group > .dropdown-toggle').eq(0).click()
      cy.get('[role="button"]').eq(2).click()
      cy.get('[class="swal-button swal-button--confirm"]').click()
      cy.get('[class="alert alert-success"]').should('be.visible')
      //Shipping button in React
      cy.get('.btn-group > [title=""]').eq(0).click()
      cy.get('[class="swal-button swal-button--confirm"]').click()
      cy.get('.swal-modal').should('exist')
      cy.get('#input-carrier').clear({force: true}).type('FedEx',{delay:0})
      cy.get('#input-code').clear({force: true}).type('123456',{delay:0})
      cy.get('#input-url').clear({force: true}).type('https://www.invertus.eu',{delay:0})
      cy.get(':nth-child(2) > .swal-button').click()
      cy.get('#mollie_order > :nth-child(1) > .alert').contains('Shipment was made successfully!')
      cy.get('[class="alert alert-success"]').should('be.visible')
})
it('EPS Checkouting [Orders API]', () => {
  cy.visit('/SHOP2/de/index.php?controller=history')
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
it('EPS Order Shipping, Refunding [Orders API]', () => {
  cy.visit('/admin1/index.php?controller=AdminOrders')
  cy.get(':nth-child(1) > .column-payment').click()
      //Refunding dropdown in React
      cy.get('.btn-group-action > .btn-group > .dropdown-toggle').eq(0).click()
      cy.get('[role="button"]').eq(2).click()
      cy.get('[class="swal-button swal-button--confirm"]').click()
      cy.get('[class="alert alert-success"]').should('be.visible')
      //Shipping button in React
      cy.get('.btn-group > [title=""]').eq(0).click()
      cy.get('[class="swal-button swal-button--confirm"]').click()
      cy.get('.swal-modal').should('exist')
      cy.get('#input-carrier').clear({force: true}).type('FedEx',{delay:0})
      cy.get('#input-code').clear({force: true}).type('123456',{delay:0})
      cy.get('#input-url').clear({force: true}).type('https://www.invertus.eu',{delay:0})
      cy.get(':nth-child(2) > .swal-button').click()
      cy.get('#mollie_order > :nth-child(1) > .alert').contains('Shipment was made successfully!')
      cy.get('[class="alert alert-success"]').should('be.visible')
})
it('KBC/CBC Checkouting [Orders API]', () => {
  cy.visit('/SHOP2/de/index.php?controller=history')
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
it('KBC/CBC Order Shipping, Refunding [Orders API]', () => {
  cy.visit('/admin1/index.php?controller=AdminOrders')
  cy.get(':nth-child(1) > .column-payment').click()
      //Refunding dropdown in React
      cy.get('.btn-group-action > .btn-group > .dropdown-toggle').eq(0).click()
      cy.get('[role="button"]').eq(2).click()
      cy.get('[class="swal-button swal-button--confirm"]').click()
      cy.get('[class="alert alert-success"]').should('be.visible')
      //Shipping button in React
      cy.get('.btn-group > [title=""]').eq(0).click()
      cy.get('[class="swal-button swal-button--confirm"]').click()
      cy.get('.swal-modal').should('exist')
      cy.get('#input-carrier').clear({force: true}).type('FedEx',{delay:0})
      cy.get('#input-code').clear({force: true}).type('123456',{delay:0})
      cy.get('#input-url').clear({force: true}).type('https://www.invertus.eu',{delay:0})
      cy.get(':nth-child(2) > .swal-button').click()
      cy.get('#mollie_order > :nth-child(1) > .alert').contains('Shipment was made successfully!')
      cy.get('[class="alert alert-success"]').should('be.visible')
})
it('Belfius Checkouting [Orders API]', () => {
  cy.visit('/SHOP2/de/index.php?controller=history')
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
it('Belfius Order Shipping, Refunding [Orders API]', () => {
  cy.visit('/admin1/index.php?controller=AdminOrders')
  cy.get(':nth-child(1) > .column-payment').click()
      //Refunding dropdown in React
      cy.get('.btn-group-action > .btn-group > .dropdown-toggle').eq(0).click()
      cy.get('[role="button"]').eq(2).click()
      cy.get('[class="swal-button swal-button--confirm"]').click()
      cy.get('[class="alert alert-success"]').should('be.visible')
      //Shipping button in React
      cy.get('.btn-group > [title=""]').eq(0).click()
      cy.get('[class="swal-button swal-button--confirm"]').click()
      cy.get('.swal-modal').should('exist')
      cy.get('#input-carrier').clear({force: true}).type('FedEx',{delay:0})
      cy.get('#input-code').clear({force: true}).type('123456',{delay:0})
      cy.get('#input-url').clear({force: true}).type('https://www.invertus.eu',{delay:0})
      cy.get(':nth-child(2) > .swal-button').click()
      cy.get('#mollie_order > :nth-child(1) > .alert').contains('Shipment was made successfully!')
      cy.get('[class="alert alert-success"]').should('be.visible')
})
it('Bank Transfer Checkouting [Orders API]', () => {
  cy.visit('/SHOP2/en/index.php?controller=history')
  cy.get('a').click()
  cy.contains('Reorder').click()
  cy.contains('NL').click()
  //Billing country LT, DE etc.
  cy.get('.clearfix > .btn').click()
  cy.get('#js-delivery > .continue').click()
  //Payment method choosing
  // waiting for enabling IN3 payment
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
  //TODO - should be validation screen or what?
  //cy.get('#content-hook_order_confirmation > .card-block').should('be.visible')
});
it.only('Bank Transfer Order Shipping, Refunding [Orders API]', () => {
  cy.visit('/admin1/index.php?controller=AdminOrders')
  cy.get(':nth-child(1) > .column-payment').click()
      //Refunding dropdown in React
      cy.get('.btn-group-action > .btn-group > .dropdown-toggle').eq(0).click()
      cy.get('[role="button"]').eq(2).click()
      cy.get('[class="swal-button swal-button--confirm"]').click()
      cy.get('[class="alert alert-success"]').should('be.visible')
      //Shipping button in React
      cy.get('.btn-group > [title=""]').eq(0).click()
      cy.get('[class="swal-button swal-button--confirm"]').click()
      cy.get('.swal-modal').should('exist')
      cy.get('#input-carrier').clear({force: true}).type('FedEx',{delay:0})
      cy.get('#input-code').clear({force: true}).type('123456',{delay:0})
      cy.get('#input-url').clear({force: true}).type('https://www.invertus.eu',{delay:0})
      cy.get(':nth-child(2) > .swal-button').click()
      cy.get('#mollie_order > :nth-child(1) > .alert').contains('Shipment was made successfully!')
      cy.get('[class="alert alert-success"]').should('be.visible')
})
it('20 Enabling All payments in Module BO [Payments API]', () => {
      cy.visit('/admin1/')
      cy.get('#subtab-AdminMollieModule > .link').click()
      cy.ConfPaymentsAPI1784()
      cy.get('[type="submit"]').first().click()
      cy.get('[class="alert alert-success"]').should('be.visible')
})
it('21 Check if Bancontact QR payment dropdown exists [Payments API]', () => {
  cy.visit('/admin1/')
  cy.get('#subtab-AdminMollieModule > .link').click()
  cy.get('[name="MOLLIE_BANCONTACT_QR_CODE_ENABLED"]').should('exist')
})
it('22 Bancontact Checkouting [Payments API]', () => {
      cy.visit('/SHOP2/de/index.php?controller=history')
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
it('23 Bancontact Order BO Shiping, Refunding [Payments API]', () => {
      cy.visit('/admin1/index.php?controller=AdminOrders')
      cy.get(':nth-child(1) > .column-payment').click()
      cy.get('#mollie_order > :nth-child(1)').should('exist')
      cy.get('.form-inline > :nth-child(1) > .btn').should('exist')
      cy.get('.input-group-btn > .btn').should('exist')
      cy.get('.sc-htpNat > .panel > .card-body > :nth-child(3)').should('exist')
      cy.get('.card-body > :nth-child(6)').should('exist')
      cy.get('.card-body > :nth-child(9)').should('exist')
      cy.get('#mollie_order > :nth-child(1) > :nth-child(1)').should('exist')
      cy.get('.sc-htpNat > .panel > .card-body').should('exist')
      cy.get('.sc-bxivhb > .panel > .panel-heading').should('exist')
      cy.get('.sc-bxivhb > .panel > .card-body').should('exist')
      //check partial refunding on Payments API
      cy.get('.form-inline > :nth-child(2) > .input-group > .form-control').type('1.51',{delay:0})
      cy.get(':nth-child(2) > .input-group > .input-group-btn > .btn').click()
      cy.get('.swal-modal').should('exist')
      cy.get(':nth-child(2) > .swal-button').click()
      cy.get('#mollie_order > :nth-child(1) > .alert').contains('Refund was made successfully!')
      cy.get('.form-inline > :nth-child(1) > .btn').click()
      cy.get('.swal-modal').should('exist')
      cy.get(':nth-child(2) > .swal-button').click()
      cy.get('#mollie_order > :nth-child(1) > .alert').contains('Refund was made successfully!')
})
it('24 iDEAL Checkouting [Payments API]', () => {
      cy.visit('/SHOP2/en/index.php?controller=history')
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
it('25 iDEAL Order BO Shiping, Refunding [Payments API]', () => {
      cy.visit('/admin1/index.php?controller=AdminOrders')
      cy.get(':nth-child(1) > .column-payment').click()
      cy.get('#mollie_order > :nth-child(1)').should('exist')
      cy.get('.form-inline > :nth-child(1) > .btn').should('exist')
      cy.get('.input-group-btn > .btn').should('exist')
      cy.get('.sc-htpNat > .panel > .card-body > :nth-child(3)').should('exist')
      cy.get('.card-body > :nth-child(6)').should('exist')
      cy.get('.card-body > :nth-child(9)').should('exist')
      cy.get('#mollie_order > :nth-child(1) > :nth-child(1)').should('exist')
      cy.get('.sc-htpNat > .panel > .card-body').should('exist')
      cy.get('.sc-bxivhb > .panel > .panel-heading').should('exist')
      cy.get('.sc-bxivhb > .panel > .card-body').should('exist')
      //check partial refunding on Payments API
      cy.get('.form-inline > :nth-child(2) > .input-group > .form-control').type('1.51',{delay:0})
      cy.get(':nth-child(2) > .input-group > .input-group-btn > .btn').click()
      cy.get('.swal-modal').should('exist')
      cy.get(':nth-child(2) > .swal-button').click()
      cy.get('#mollie_order > :nth-child(1) > .alert').contains('Refund was made successfully!')
      cy.get('.form-inline > :nth-child(1) > .btn').click()
      cy.get('.swal-modal').should('exist')
      cy.get(':nth-child(2) > .swal-button').click()
      cy.get('#mollie_order > :nth-child(1) > .alert').contains('Refund was made successfully!')
})
it('26 Credit Card Checkouting [Payments API]', () => {
      cy.visit('/SHOP2/en/index.php?controller=history')
      cy.get('a').click()
      cy.contains('Reorder').click()
      //Billing country LT, DE etc.
      cy.get('.clearfix > .btn').click()
      cy.get('#js-delivery > .continue').click()
      //Payment method choosing
      cy.contains('Credit card').click({force:true})
      //Credit card inputing
      cy.frameLoaded('[data-testid=mollie-container--cardHolder] > iframe')
      cy.enter('[data-testid=mollie-container--cardHolder] > iframe').then(getBody => {
      getBody().find('#cardHolder').clear({force: true}).type('TEST TEEESSSTT')
      })
      cy.enter('[data-testid=mollie-container--cardNumber] > iframe').then(getBody => {
      getBody().find('#cardNumber').clear({force: true}).type('5555555555554444')
      })
      cy.enter('[data-testid=mollie-container--expiryDate] > iframe').then(getBody => {
      getBody().find('#expiryDate').clear({force: true}).type('1222')
      })
      cy.enter('[data-testid=mollie-container--verificationCode] > iframe').then(getBody => {
      getBody().find('#verificationCode').clear({force: true}).type('222')
      })
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
it('27 Credit Card Order BO Shiping, Refunding [Payments API]', () => {
      cy.visit('/admin1/index.php?controller=AdminOrders')
      cy.get(':nth-child(1) > .column-payment').click()
      cy.get('#mollie_order > :nth-child(1)').should('exist')
      cy.get('.form-inline > :nth-child(1) > .btn').should('exist')
      cy.get('.input-group-btn > .btn').should('exist')
      cy.get('.sc-htpNat > .panel > .card-body > :nth-child(3)').should('exist')
      cy.get('.card-body > :nth-child(6)').should('exist')
      cy.get('.card-body > :nth-child(9)').should('exist')
      cy.get('#mollie_order > :nth-child(1) > :nth-child(1)').should('exist')
      cy.get('.sc-htpNat > .panel > .card-body').should('exist')
      cy.get('.sc-bxivhb > .panel > .panel-heading').should('exist')
      cy.get('.sc-bxivhb > .panel > .card-body').should('exist')
      //check partial refunding on Payments API
      cy.get('.form-inline > :nth-child(2) > .input-group > .form-control').type('1.51',{delay:0})
      cy.get(':nth-child(2) > .input-group > .input-group-btn > .btn').click()
      cy.get('.swal-modal').should('exist')
      cy.get(':nth-child(2) > .swal-button').click()
      cy.get('#mollie_order > :nth-child(1) > .alert').contains('Refund was made successfully!')
      cy.get('.form-inline > :nth-child(1) > .btn').click()
      cy.get('.swal-modal').should('exist')
      cy.get(':nth-child(2) > .swal-button').click()
      cy.get('#mollie_order > :nth-child(1) > .alert').contains('Refund was made successfully!')
})
it('28 Credit Card Guest Checkouting [Payments API]', () => {
      cy.clearCookies()
      //Payments API item
      cy.visit('/SHOP2/en/', { headers: {"Accept-Encoding": "gzip, deflate"}})
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
      cy.contains('Credit card').click({force:true})
      //Credit card inputing
      cy.frameLoaded('[data-testid=mollie-container--cardHolder] > iframe')
      cy.enter('[data-testid=mollie-container--cardHolder] > iframe').then(getBody => {
      getBody().find('#cardHolder').clear({force: true}).type('TEST TEEESSSTT')
      })
      cy.enter('[data-testid=mollie-container--cardNumber] > iframe').then(getBody => {
      getBody().find('#cardNumber').clear({force: true}).type('5555555555554444')
      })
      cy.enter('[data-testid=mollie-container--expiryDate] > iframe').then(getBody => {
      getBody().find('#expiryDate').clear({force: true}).type('1222')
      })
      cy.enter('[data-testid=mollie-container--verificationCode] > iframe').then(getBody => {
      getBody().find('#verificationCode').clear({force: true}).type('222')
      })
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
it('29 Credit Card Guest Checkouting with not 3DS secure card [Payments API]', () => {
  cy.clearCookies()
  //Payments API item
  cy.visit('/SHOP2/en/', { headers: {"Accept-Encoding": "gzip, deflate"}})
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
  cy.contains('Credit card').click({force:true})
  //Credit card inputing
  cy.frameLoaded('[data-testid=mollie-container--cardHolder] > iframe')
  cy.enter('[data-testid=mollie-container--cardHolder] > iframe').then(getBody => {
  getBody().find('#cardHolder').clear({force: true}).type('TEST TEEESSSTT')
  })
  cy.enter('[data-testid=mollie-container--cardNumber] > iframe').then(getBody => {
  getBody().find('#cardNumber').clear({force: true}).type('4242424242424242')
  })
  cy.enter('[data-testid=mollie-container--expiryDate] > iframe').then(getBody => {
  getBody().find('#expiryDate').clear({force: true}).type('1222')
  })
  cy.enter('[data-testid=mollie-container--verificationCode] > iframe').then(getBody => {
  getBody().find('#verificationCode').clear({force: true}).type('222')
  })
  cy.get('.condition-label > .js-terms').click({force:true})
  cy.get('.ps-shown-by-js > .btn').click()
  cy.get('#content-hook_order_confirmation > .card-block').should('be.visible')
})
})
