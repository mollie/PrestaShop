/// <reference types="Cypress" />
context('Klarna [Pay Later, Slice It] Payment PS16 Orders API check',
{
    retries: {
      runMode: 2,
      openMode: 2,
    }
}, () => {
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
it('Checking the Back-Office Order Existance, Refunding, Shipping [Klarna Pay Later]', () => {
  cy.mollie_test16_admin()
  cy.login_mollie16_test()
  cy.visit('https://demo.invertus.eu/clients/mollie16-test/admin1/index.php?controller=AdminOrders')
  cy.get('[class=" odd"]').eq(0).click().wait(3000)
  //Shipping button in React
  cy.get('.btn-group > [title=""]').click()
  cy.get('[class="swal-button swal-button--confirm"]').click()
  cy.get('.swal-modal').should('exist')
  cy.get('#input-carrier').type('FedEx',{delay:0})
  cy.get('#input-code').type('123456',{delay:0})
  cy.get('#input-url').type('https://www.invertus.eu',{delay:0})
  cy.get(':nth-child(2) > .swal-button').click()
  cy.get('#mollie_order > :nth-child(1) > .alert').contains('Shipment was made successfully!')
  cy.get('[class="alert alert-success"]').should('be.visible')
  //Refunding dropdown in React
  cy.get('.btn-group-action > .btn-group > .dropdown-toggle').click()
  cy.get('[role="button"]').eq(0).click()
  cy.get('[class="swal-button swal-button--confirm"]').click()
  cy.get('[class="alert alert-success"]').should('be.visible')
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
it('Checking the Back-Office Order Existance, Refunding, Shipping [Klarna Slice It]', () => {
    cy.mollie_test16_admin()
    cy.login_mollie16_test()
    cy.visit('https://demo.invertus.eu/clients/mollie16-test/admin1/index.php?controller=AdminOrders')
    cy.get('[class=" odd"]').eq(0).click().wait(3000)
    //Shipping button in React
    cy.get('.btn-group > [title=""]').click()
    cy.get('[class="swal-button swal-button--confirm"]').click()
    cy.get('.swal-modal').should('exist')
    cy.get('#input-carrier').type('FedEx',{delay:0})
    cy.get('#input-code').type('123456',{delay:0})
    cy.get('#input-url').type('https://www.invertus.eu',{delay:0})
    cy.get(':nth-child(2) > .swal-button').click()
    cy.get('#mollie_order > :nth-child(1) > .alert').contains('Shipment was made successfully!')
    cy.get('[class="alert alert-success"]').should('be.visible')
    //Refunding dropdown in React
    cy.get('.btn-group-action > .btn-group > .dropdown-toggle').click()
    cy.get('[role="button"]').eq(0).click()
    cy.get('[class="swal-button swal-button--confirm"]').click()
    cy.get('[class="alert alert-success"]').should('be.visible')
  })
it('Checking the Email Sending log in Prestashop [Klarna Slice It]', () => {
    cy.mollie_test16_admin()
    cy.login_mollie16_test()
    cy.visit('https://demo.invertus.eu/clients/mollie16-test/admin1/index.php?controller=AdminEmails&token=023927e534d296d1d25aab2eaa409760')
    cy.get('.table > tbody > :nth-child(1) > :nth-child(4)').should('include.text','order_conf')
    cy.get('.table > tbody > :nth-child(2) > :nth-child(4)').should('include.text','payment')
  })
})
