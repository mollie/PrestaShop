/// <reference types="Cypress" />
context('Klarna [Pay Later, Slice It] Payment PS16 Orders API check', () => {
  beforeEach(() => {
    cy.viewport(1920,1080)
  })
it('Checking the Klarna Pay Later Orders API method successfully enabling BO', () => {
      cy.mollie_test16_admin()
      cy.login_mollie16_test()
      cy.get('#maintab-AdminMollieModule > .title').click()
      cy.get('#MOLLIE_API_KEY_TEST').clear().type((Cypress.env('mollie_test_api_key')),{delay: 0, log: false})
      cy.get('#MOLLIE_PROFILE_ID').clear().type((Cypress.env('mollie_test_profile_id')),{delay: 0, log: false})
      cy.get('[for="MOLLIE_IFRAME_on"]').click()
      //Checking if saving OK
      cy.get('#module_form_submit_btn').click()
      cy.contains('The configuration has been saved!').should('exist').as('Save Successfull')
      //disabling issuer popup
      cy.get('#MOLLIE_ISSUERS').select('Payment page')
      cy.get('#js-payment-methods-sortable').contains('Pay later').click()
      cy.get('#payment-method-form-klarnapaylater > :nth-child(1) > .col-lg-9 > .fixed-width-xl').select('Yes')
      cy.get('#payment-method-form-klarnapaylater > :nth-child(3) > .col-lg-9 > .fixed-width-xl').select('Orders API')
      cy.get('#module_form_submit_btn').click()
      //Checking if saving OK
      cy.contains('The configuration has been saved!').should('exist').as('Save Successfull')
})
    // Starting purchasing process
it('Checkouting the item Front-Office [Klarna Pay Later]', () => {
  Cypress.on('uncaught:exception', (err, runnable) => {
    // returning false here prevents Cypress from
    // failing the test
    return false
  })
      cy.mollie_16124_test_faster_login_DE_Orders_Api()
      cy.get('.cart_navigation > .button > span').click()
      cy.get('.cart_navigation > .button > span').click()
      cy.get('.cart_navigation > .button > span').click()
      cy.get('label').click({force: true})
      cy.get('.cart_navigation > .button > span').click({force: true})
      cy.get('#mollie_link_klarnapaylater').click()
      cy.get(':nth-child(1) > .checkbox > .checkbox__label').click()
      cy.get('.button').click()

      //Success page UI verification
      cy.get('#mollie-ok').should('include.text','Thank you')
  })
it('Checking the Back-Office Order Existance [Klarna Pay Later]', () => {
  cy.mollie_test16_admin()
  cy.login_mollie16_test()
  cy.visit('https://demo.invertus.eu/clients/mollie16-test/admin1/index.php?controller=AdminOrders&token=2e9e601079755e680c5f058da5aa16d3')
  cy.get('tbody > :nth-child(1) > :nth-child(8)').contains('Pay later')
  cy.get('tbody > :nth-child(1) > :nth-child(9)').contains('Klarna payment authorized')
  cy.get(':nth-child(1) > :nth-child(14) > .btn-group > .btn').click()
  cy.get('#formAddPaymentPanel').contains('klarnapaylater')
  cy.get('#mollie_order > :nth-child(1)').should('exist')
  cy.get('.sc-htpNat > .panel').should('exist')
  cy.get('.sc-jTzLTM > .panel').should('exist')
  cy.get('.btn-group > [title=""]').should('exist')
  cy.get('.btn-group > .btn-primary').should('exist')
  cy.get('tfoot > tr > td > .btn-group > :nth-child(2)').should('exist')
  cy.get('.sc-htpNat > .panel > .card-body > :nth-child(3)').should('exist')
  cy.get('.card-body > :nth-child(6)').should('exist')
  cy.get('.card-body > :nth-child(9)').should('exist')
  cy.get('#mollie_order > :nth-child(1) > :nth-child(1)').should('exist')
  cy.get('.sc-htpNat > .panel > .card-body').should('exist')
  cy.get('.btn-group-action > .btn-group > .dropdown-toggle').click()
  cy.get('.btn-group > .dropdown-menu > :nth-child(1) > a').should('exist')
  cy.get('.dropdown-menu > :nth-child(2) > a').should('exist')
})
it('Checking the Email Sending log in Prestashop [Klarna Pay Later]', () => {
  cy.mollie_test16_admin()
  cy.login_mollie16_test()
  cy.visit('https://demo.invertus.eu/clients/mollie16-test/admin1/index.php?controller=AdminEmails&token=023927e534d296d1d25aab2eaa409760')
  cy.get('.table > tbody > :nth-child(1) > :nth-child(4)').should('include.text','order_conf')
  cy.get('.table > tbody > :nth-child(2) > :nth-child(4)').should('include.text','payment')
})
it('Checking the Klarna Slice It Orders API method successfully enabling BO', () => {
  cy.mollie_test16_admin()
  cy.login_mollie16_test()
  cy.get('#maintab-AdminMollieModule > .title').click()
  cy.get('#MOLLIE_API_KEY_TEST').clear().type((Cypress.env('mollie_test_api_key')),{delay: 0, log: false})
  cy.get('#MOLLIE_PROFILE_ID').clear().type((Cypress.env('mollie_test_profile_id')),{delay: 0, log: false})
  cy.get('[for="MOLLIE_IFRAME_on"]').click()
  //Checking if saving OK
  cy.get('#module_form_submit_btn').click()
  cy.contains('The configuration has been saved!').should('exist').as('Save Successfull')
  //disabling issuer popup
  cy.get('#MOLLIE_ISSUERS').select('Payment page')
  cy.get('#js-payment-methods-sortable').contains('Slice it').click()
  cy.get('#payment-method-form-klarnasliceit > :nth-child(1) > .col-lg-9 > .fixed-width-xl').select('Yes')
  cy.get('#payment-method-form-klarnasliceit > :nth-child(3) > .col-lg-9 > .fixed-width-xl').select('Orders API')
  cy.get('#module_form_submit_btn').click()
  //Checking if saving OK
  cy.contains('The configuration has been saved!').should('exist').as('Save Successfull')
})
it('Checkouting the item Front-Office [Klarna Slice It]', () => {
  Cypress.on('uncaught:exception', (err, runnable) => {
    // returning false here prevents Cypress from
    // failing the test
    return false
  })
      cy.mollie_16124_test_faster_login_DE_Orders_Api()
      cy.get('.cart_navigation > .button > span').click()
      cy.get('.cart_navigation > .button > span').click()
      cy.get('.cart_navigation > .button > span').click()
      cy.get('label').click({force: true})
      cy.get('.cart_navigation > .button > span').click({force: true})
      cy.get('#mollie_link_klarnasliceit').click()
      cy.get(':nth-child(1) > .checkbox > .checkbox__label').click()
      cy.get('.button').click()

      //Success page UI verification
      cy.get('#mollie-ok').should('include.text','Thank you')
  })
it('Checking the Back-Office Order Existance [Klarna Slice It]', () => {
    cy.mollie_test16_admin()
    cy.login_mollie16_test()
    cy.visit('https://demo.invertus.eu/clients/mollie16-test/admin1/index.php?controller=AdminOrders&token=2e9e601079755e680c5f058da5aa16d3')
    cy.get('tbody > :nth-child(1) > :nth-child(8)').contains('Slice it')
    cy.get('tbody > :nth-child(1) > :nth-child(9)').contains('Klarna payment authorized')
    cy.get(':nth-child(1) > :nth-child(14) > .btn-group > .btn').click()
    cy.get('#formAddPaymentPanel').contains('klarnasliceit')
    cy.get('#mollie_order > :nth-child(1)').should('exist')
    cy.get('.sc-htpNat > .panel').should('exist')
    cy.get('.sc-jTzLTM > .panel').should('exist')
    cy.get('.btn-group > [title=""]').should('exist')
    cy.get('.btn-group > .btn-primary').should('exist')
    cy.get('tfoot > tr > td > .btn-group > :nth-child(2)').should('exist')
    cy.get('.sc-htpNat > .panel > .card-body > :nth-child(3)').should('exist')
    cy.get('.card-body > :nth-child(6)').should('exist')
    cy.get('.card-body > :nth-child(9)').should('exist')
    cy.get('#mollie_order > :nth-child(1) > :nth-child(1)').should('exist')
    cy.get('.sc-htpNat > .panel > .card-body').should('exist')
    cy.get('.btn-group-action > .btn-group > .dropdown-toggle').click()
    cy.get('.btn-group > .dropdown-menu > :nth-child(1) > a').should('exist')
    cy.get('.dropdown-menu > :nth-child(2) > a').should('exist')
  })
it('Checking the Email Sending log in Prestashop [Klarna Slice It]', () => {
    cy.mollie_test16_admin()
    cy.login_mollie16_test()
    cy.visit('https://demo.invertus.eu/clients/mollie16-test/admin1/index.php?controller=AdminEmails&token=023927e534d296d1d25aab2eaa409760')
    cy.get('.table > tbody > :nth-child(1) > :nth-child(4)').should('include.text','order_conf')
    cy.get('.table > tbody > :nth-child(2) > :nth-child(4)').should('include.text','payment')
  })
})
