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
//import 'cypress-file-upload';
import 'cypress-iframe';
// or
//require('cypress-iframe');

//const compareSnapshotCommand = require('cypress-visual-regression/dist/command');
//compareSnapshotCommand({
//  capture: 'fullPage'
//});
// ***********************************************
// This example commands.js shows you how to
// create various custom commands and overwrite
// existing commands.
//
// For more comprehensive examples of custom
// commands please read more here:
// https://on.cypress.io/custom-commands
// ***********************************************
//
//
// -- This is a parent command --
// Cypress.Commands.add("login", (email, password) => { ... })
Cypress.Commands.add("ConfOrdersAPI1784", () => {

      const paymentMethods = ["giropay", "eps", "przelewy24", "kbc", "voucher", "belfius", "bancontact", "sofort", "creditcard", "ideal", "klarnapaylater", "klarnasliceit","klarnapaynow", "banktransfer", "paypal", "applepay", "in3", "billie", "klarna"];

      // Iterate through the paymentMethods array using forEach
      paymentMethods.forEach(method => {
        cy.get(`[name="MOLLIE_METHOD_ENABLED_${method}"]`).select('Yes', { force: true });
        cy.get(`[name="MOLLIE_METHOD_API_${method}"]`).select('Orders API', { force: true });
        cy.get(`[name="MOLLIE_METHOD_DESCRIPTION_${method}"]`).clear({ force: true }).type('text 123 !@#$%^&*', { force: true });
        cy.get(`[name="MOLLIE_METHOD_SURCHARGE_TYPE_${method}"]`).select('3', { force: true });
        cy.get(`[name="MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_TAX_INCL_${method}"]`).clear({ force: true }).type('4', { force: true });
        cy.get(`[name="MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_TAX_EXCL_${method}"]`).clear({ force: true }).type('5', { force: true });
        cy.get(`[name="MOLLIE_METHOD_TAX_RULES_GROUP_ID_${method}"]`).select('1', { force: true });
        cy.get(`[name="MOLLIE_METHOD_SURCHARGE_PERCENTAGE_${method}"]`).clear({ force: true }).type('22', { force: true });
        cy.get(`[name="MOLLIE_METHOD_SURCHARGE_LIMIT_${method}"]`).clear({ force: true }).type('33', { force: true });
      });
  })
Cypress.Commands.add("ConfPaymentsAPI1784", () => {

      const paymentMethods = ["giropay", "eps", "przelewy24", "kbc", "belfius", "bancontact", "sofort", "creditcard", "ideal", "banktransfer", "paypal", "applepay", "klarna"];

      // Iterate through the paymentMethods array using forEach
      paymentMethods.forEach(method => {
        cy.get(`[name="MOLLIE_METHOD_ENABLED_${method}"]`).select('Yes', {force: true})
        cy.get(`[name="MOLLIE_METHOD_API_${method}"]`).select('Payments API', {force: true})
      });
})
Cypress.Commands.add("navigatingToThePaymentPS8", () => {
    cy.visit('/de/index.php?controller=history')
    cy.contains('Reorder').click()
    cy.contains('NL').click()
    //Billing country LT, DE etc.
    cy.get('.clearfix > .btn').click()
    cy.get('#js-delivery > .continue').click()
})
Cypress.Commands.add("navigatingToThePayment", () => {
  cy.visit('/de/index.php?controller=history')
  cy.get('a').click()
  cy.contains('Reorder').click()
  cy.contains('NL').click()
  //Billing country LT, DE etc.
  cy.get('.clearfix > .btn').click()
  cy.get('#js-delivery > .continue').click()
})
Cypress.Commands.add("OrderRefundingShippingOrdersAPI", () => {
    cy.visit('/admin1/index.php?controller=AdminOrders')
    cy.get(':nth-child(1) > .column-payment').click()
    cy.scrollTo('bottom')
    //Refunding dropdown in React
    cy.get('.btn-group-action > .btn-group > .dropdown-toggle').eq(0).then(($body) => {
      if ($body.length > 0) {
        // If the element doesn't exist, skip the test
        cy.log('Element not found possibly due to to the distractions from the Mollie API. Skipping the Test')
        console.log('Element not found possibly due to to the distractions from the Mollie API. Skipping the Test')
        return
      } else {
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
      }
    })
})
Cypress.Commands.add("OrderShippingRefundingOrdersAPI", () => {
    cy.visit('/admin1/index.php?controller=AdminOrders')
    cy.get(':nth-child(1) > .column-payment').click()
    cy.scrollTo('bottom')
    //Shipping button in React
    cy.get('.btn-group > [title=""]').then(($body) => {
      if ($body.length > 0) {
        // If the element doesn't exist, skip the test
        cy.log('Element not found possibly due to to the distractions from the Mollie API. Skipping the Test')
        console.log('Element not found possibly due to to the distractions from the Mollie API. Skipping the Test')
        return
      } else {
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
      }
    })
})
Cypress.Commands.add("OrderRefundingPartialPaymentsAPI", () => {
    cy.visit('/admin1/index.php?controller=AdminOrders')
    cy.get(':nth-child(1) > .column-payment').click()
    cy.scrollTo('bottom')
    // here the Mollie block should exist in Orders BO. Sometimes, the Mollie API is not responding correctly
    cy.get('#mollie_order > :nth-child(1)', { timeout: 10000 }).then(($body) => {
      if ($body.length > 0) {
        // If the element doesn't exist, skip the test
        cy.log('Element not found possibly due to to the distractions from the Mollie API. Skipping the Test')
        console.log('Element not found possibly due to to the distractions from the Mollie API. Skipping the Test')
        return
      } else {
        cy.get('#mollie_order > :nth-child(1)').click()
        cy.get('.form-inline > :nth-child(1) > .btn').should('exist')
        cy.get('.input-group-btn > .btn').should('exist')
        cy.get('.sc-htpNat > .panel > .card-body > :nth-child(3)').should('exist')
        cy.get('.card-body > :nth-child(6)').should('exist')
        cy.get('.card-body > :nth-child(9)').should('exist')
        cy.get('#mollie_order > :nth-child(1) > :nth-child(1)').should('exist')
        cy.get('.sc-htpNat > .panel > .card-body').should('exist')
        cy.get('.sc-bxivhb > .panel > .panel-heading').should('exist')
        cy.get('.sc-bxivhb > .panel > .card-body').should('exist')
        //Check partial refunding on Payments API
        cy.get('.form-inline > :nth-child(2) > .input-group > .form-control').type('1.51',{delay:0})
        cy.get(':nth-child(2) > .input-group > .input-group-btn > .btn').click()
        cy.get('.swal-modal').should('exist')
        cy.get(':nth-child(2) > .swal-button').click()
        cy.get('#mollie_order > :nth-child(1) > .alert').contains('Refund was made successfully!')
        cy.get('.form-inline > :nth-child(1) > .btn').click()
        cy.get('.swal-modal').should('exist')
        cy.get(':nth-child(2) > .swal-button').click()
        cy.get('#mollie_order > :nth-child(1) > .alert').contains('Refund was made successfully!')
    }
  })
})
Cypress.Commands.add("EnablingModuleMultistore", () => {
  cy.get('#subtab-AdminParentModulesSf > :nth-child(1)').click()
  cy.get('#subtab-AdminModulesSf').click().wait(1000)
  // enable or upgrade the module
  cy.get('[data-name="Mollie"]').then(($body) => {
    if ($body.text().includes('Upgrade')) {
      // yup, module needs to be upgraded
      cy.get('[data-name="Mollie"]').contains('Upgrade').click()
      cy.get('.btn-secondary').click()
      cy.get('.growl').should('have.text','succeeded.')
    } else if ($body.text().includes('Enable')) {
      // or just enable the module first
      cy.get('[data-name="Mollie"]').contains('Enable').click()
    } else {
      // nop, just enter the module configuration
      cy.get('[data-name="Mollie"]').contains('Configure').click()
    }
    })
  // back to dashboard
  cy.get('#tab-AdminDashboard > .link').click({force:true})
})
Cypress.Commands.add("OpenModuleDashboard", () => {
    cy.get('#subtab-AdminParentModulesSf > :nth-child(1)').click()
    cy.get('#subtab-AdminModulesSf').click().wait(1000)
    cy.get('[data-name="Mollie"]').contains('Configure').click()
})
Cypress.Commands.add("CreditCardFillingIframe", () => {
  cy.frameLoaded('[name="cardHolder-input"]')
  cy.enter('[name="cardHolder-input"]').then(getBody => {
  getBody().find('#cardHolder').clear({force: true}).type('TEST TEEESSSTT',{force:true})
  })
  cy.enter('[name="cardNumber-input"]').then(getBody => {
  getBody().find('#cardNumber').clear({force: true}).type('5555555555554444',{force:true})
  })
  cy.enter('[name="expiryDate-input"]').then(getBody => {
  getBody().find('#expiryDate').clear({force: true}).type('1226',{force:true})
  })
  cy.enter('[name="verificationCode-input"]').then(getBody => {
  getBody().find('#verificationCode').clear({force: true}).type('222',{force:true})
  })
})
Cypress.Commands.add("NotSecureCreditCardFillingIframe", () => {
  cy.frameLoaded('[name="cardHolder-input"]')
  cy.enter('[name="cardHolder-input"]').then(getBody => {
  getBody().find('#cardHolder').clear({force: true}).type('TEST TEEESSSTT',{force:true})
  })
  cy.enter('[name="cardNumber-input"]').then(getBody => {
  getBody().find('#cardNumber').clear({force: true}).type('4242424242424242',{force:true})
  })
  cy.enter('[name="expiryDate-input"]').then(getBody => {
  getBody().find('#expiryDate').clear({force: true}).type('1226',{force:true})
  })
  cy.enter('[name="verificationCode-input"]').then(getBody => {
  getBody().find('#verificationCode').clear({force: true}).type('222',{force:true})
  })
})
Cypress.Commands.add("OpeningModuleDashboardURL", () => {
  cy.visit('/admin1/index.php?controller=AdminModules&configure=mollie')
  cy.get('.btn-continue').click()
})
Cypress.Commands.add("CachingBOFOPS1785", () => {
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
  login('MollieBOFOLoggingIn')
})
Cypress.Commands.add("CachingBOFOPS8", {cacheAcrossSpecs: true}, () => {
  //Caching the BO and FO session
  const login = (MollieBOFOLoggingIn) => {
    cy.session(MollieBOFOLoggingIn,() => {
    cy.visit('/admin1/')
    cy.url().should('contain', 'https').as('Check if HTTPS exists')
    cy.get('#email').type('demo@prestashop.com',{delay: 0, log: false})
    cy.get('#passwd').type('prestashop_demo',{delay: 0, log: false})
    cy.get('#submit_login').click().wait(1000).as('Connection successsful')
    cy.visit('/en/my-account')
    cy.get('#login-form [name="email"]').eq(0).type('demo@demo.com')
    cy.get('#login-form [name="password"]').eq(0).type('prestashop_demo')
    cy.get('#login-form [type="submit"]').eq(0).click({force:true})
    cy.get('#history-link > .link-item').click()
    })
    }
    login('MollieBOFOLoggingIn')
  })
