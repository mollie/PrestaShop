/// <reference types="Cypress" />
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
let failEarly = false;
afterEach(() => {
  expect(windowConsoleError).to.not.be.called;
  if (failEarly) throw new Error("Failing Early due to an API or other module configuration problem. If running on CI, please check Cypress VIDEOS/SCREENSHOTS in the Artifacts for more details.")
})
afterEach(function() {
  if (this.currentTest.state === "failed") failEarly = true
});
describe('PS8 Module initial configuration setup', () => {
  beforeEach(() => {
      cy.viewport(1920,1080)
      login('MollieBOFOLoggingIn')
  })
it('C339305: Connecting test API successsfully', () => {
      cy.visit('/admin1/')
      cy.get('.mi-mollie').click({fore:true})
      cy.get('#subtab-AdminMollieModule').click()
      cy.get('#MOLLIE_ACCOUNT_SWITCH_on').click({force:true})
      cy.get('#MOLLIE_API_KEY_TEST').type((Cypress.env('MOLLIE_TEST_API_KEY')),{delay: 0, log: false})
      cy.get('#module_form_submit_btn').click()
})
it('C339338: Enabling Mollie carriers in Prestashop successfully', () => {
      cy.visit('/admin1/')
      cy.get('[id="subtab-AdminPaymentPreferences"]').find('[href]').eq(0).click({force:true})
      cy.get('[class="js-multiple-choice-table-select-column"]').eq(6).click()
      cy.get('[class="btn btn-primary"]').eq(3).click()
})
it('C339339: Checking the Advanced Settings tab, verifying the Front-end components, Saving the form, checking if there are no Errors in Console', () => {
      cy.visit('/admin1/')
      cy.get('.mi-mollie').click({fore:true})
      cy.get('#subtab-AdminMollieModule').click()
      cy.get('[href="#advanced_settings"]').click({force:true})
      cy.get('[id="MOLLIE_PAYMENTSCREEN_LOCALE"]').should('be.visible')
      cy.get('[id="MOLLIE_SEND_ORDER_CONFIRMATION"]').should('be.visible')
      cy.get('[id="MOLLIE_AUTHORIZABLE_PAYMENT_INVOICE_ON_STATUS"]').should('be.visible')
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
      cy.get('[name="MOLLIE_SUBSCRIPTION_ORDER_CARRIER_ID"]').should('be.visible') // checking the Subscriptions carriers select
      cy.get('#module_form_submit_btn').click({force:true}) //checking the saving
      cy.get('[class="alert alert-success"]').should('be.visible') //checking if saving returns green alert
      //cy.window() will check if there are no Errors in console
});
it('C688472: Checking the Subscriptions tab, and console errors', () => {
      cy.visit('/admin1/')
      cy.get('.mi-mollie').click({fore:true})
      cy.get('#subtab-AdminMollieModule').click()
      cy.get('#subtab-AdminMollieSubscriptionOrders').click()
      cy.get('[id="invertus_mollie_subscription_grid_panel"]').should('be.visible')
});
it('C688473: Checking the Subscriptions FAQ, and console errors', () => {
      cy.visit('/admin1/')
      cy.get('.mi-mollie').click({fore:true})
      cy.get('#subtab-AdminMollieModule').click()
      cy.get('#subtab-AdminMollieSubscriptionFAQ').click()
      cy.get(':nth-child(3) > .col-lg-12 > .card').should('be.visible')
      cy.get(':nth-child(4) > .col-lg-12 > .card').should('be.visible')
      cy.get(':nth-child(5) > .col-lg-12 > .card').should('be.visible')
      cy.get(':nth-child(6) > .col-lg-12 > .card').should('be.visible')
});
})
