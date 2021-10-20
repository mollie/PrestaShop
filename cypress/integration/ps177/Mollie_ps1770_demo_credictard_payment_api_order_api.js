/// <reference types="Cypress" />
context('Purchase automation PS1770 Mollie Payment/Order API Credit Card Front Office Back Office Check', () => {
  beforeEach(() => {
    cy.viewport(1920,1080)
  })
    // Checking the Credit Card enabling
    it('Checking the Credit Card Payment API method successfully enabling BO', () => {
      cy.mollie_test17_admin()
      cy.login_mollie17_test()
      cy.get('#subtab-AdminMollieModule > .link').click()
      //switching the multistore
      cy.get('#header_shop > .dropdown').click()
      cy.get('#header_shop > .dropdown > .dropdown-menu').click(100,100)
      //
      cy.get('#MOLLIE_API_KEY_TEST').clear().type((Cypress.env('mollie_test_api_key')),{delay: 0, log: false})
      cy.get('#MOLLIE_PROFILE_ID').clear().type((Cypress.env('mollie_test_profile_id')),{delay: 0, log: false})
      cy.get('[for="MOLLIE_IFRAME_on"]').click()
      //Checking if saving OK
      cy.get('#module_form_submit_btn').click()
      cy.contains('The configuration has been saved!').should('exist').as('Save Successfull')
      cy.contains('Credit card').click()
      cy.get('#payment-method-form-creditcard > :nth-child(1) > .col-lg-9 > .fixed-width-xl').select('Yes')
      cy.get('#payment-method-form-creditcard > :nth-child(3) > .col-lg-9 > .fixed-width-xl').select('Payments API')
      cy.get('#module_form_submit_btn').click()
      //Checking if saving OK
      cy.contains('The configuration has been saved!').should('exist').as('Save Successfull')
})
    // Starting purchasing process
    it('Checkouting the item Front-Office [Payments API]', () => {
      //Payments API item
      cy.visit('https://mollie1770test.invertusdemo.com/en/men/1-1-hummingbird-printed-t-shirt.html')
      cy.get('.add > .btn').click()
      cy.get('.cart-content-btn > .btn-primary').click()
      cy.get('.text-sm-center > .btn').click()

      // Creating random user all the time
      cy.get(':nth-child(1) > .custom-radio > input').check()
      cy.get('#customer-form > section > :nth-child(2) > .col-md-6 > .form-control').type('AUT',{delay:0})
      cy.get(':nth-child(3) > .col-md-6 > .form-control').type('AUT',{delay:0})
      const uuid = () => Cypress._.random(0, 1e6)
      const id = uuid()
      const testname = `testemail${id}@testing.com`
      cy.get(':nth-child(4) > .col-md-6 > .form-control').type(testname, {delay: 0})
      cy.get(':nth-child(6) > .col-md-6 > .input-group > .form-control').type('123456',{delay:0})
      cy.get(':nth-child(9) > .col-md-6 > .custom-checkbox > label > input').check()
      cy.get(':nth-child(11) > .col-md-6 > .custom-checkbox > label > input').check()
      cy.get('#customer-form > .form-footer > .continue').click()

      cy.get(':nth-child(6) > .col-md-6 > .form-control').type('123456',{delay:0})
      cy.get(':nth-child(7) > .col-md-6 > .form-control').type('123456',{delay:0}).as('vat number')
      cy.get(':nth-child(8) > .col-md-6 > .form-control').type('ADDR',{delay:0}).as('address')
      cy.get(':nth-child(10) > .col-md-6 > .form-control').type('54469',{delay:0}).as('zip')
      cy.get(':nth-child(11) > .col-md-6 > .form-control').type('CIT',{delay:0}).as('city')
      cy.get(':nth-child(12) > .col-md-6 > .form-control').select('Lithuania').as('country')
      cy.get(':nth-child(13) > .col-md-6 > .form-control').type('085',{delay:0}).as('telephone')
      cy.get('.form-footer > .continue').click()
      cy.get('#js-delivery > .continue').click()
      cy.contains('Credit').click()

      //Credit card inputing
      cy.frameLoaded('[data-testid=mollie-container--cardHolder] > iframe')
      cy.enter('[data-testid=mollie-container--cardHolder] > iframe').then(getBody => {
      getBody().find('#cardHolder').type('TEST TEEESSSTT')
  })
      cy.enter('[data-testid=mollie-container--cardNumber] > iframe').then(getBody => {
      getBody().find('#cardNumber').type('5555555555554444')
  })
      cy.enter('[data-testid=mollie-container--expiryDate] > iframe').then(getBody => {
      getBody().find('#expiryDate').type('1222')
})
      cy.enter('[data-testid=mollie-container--verificationCode] > iframe').then(getBody => {
      getBody().find('#verificationCode').type('222')
})
      cy.get('.js-terms').click()
      cy.get('.ps-shown-by-js > .btn').click()
      cy.get(':nth-child(2) > .checkbox > .checkbox__label').click()
      cy.get('.button').click()

      //Success page UI verification
      cy.get('.h1').should('include.text','Your order is confirmed')
      cy.get('#order-details > ul > :nth-child(2)').should('include.text','Credit Card')

  })
    it('Checking the Back-Office Order Existance, Refunding [Payments API]', () => {
      cy.mollie_test17_admin()
      cy.login_mollie17_test()
      cy.get('#subtab-AdminParentOrders > :nth-child(1) > span').click()
      cy.get('#subtab-AdminOrders > .link').click()
      cy.get('tbody > :nth-child(1) > :nth-child(8)').should('include.text','Bancontact')
      cy.get('tbody > :nth-child(1) > :nth-child(9)').should('include.text','Payment accepted')
      cy.get(':nth-child(1) > .column-payment').click()
      cy.get('#view_order_payments_block > .card-body').contains('bancontact')
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
      it('Checking the Email Sending log in Prestashop [Payments API]', () => {
      cy.mollie_test17_admin()
      cy.login_mollie17_test()
      cy.get('#subtab-AdminAdvancedParameters > :nth-child(1) > span').click()
      cy.get('#subtab-AdminEmails > .link').click()
      cy.get('#email_logs_grid_table > tbody > :nth-child(2) > :nth-child(4)').should('include.text','order_conf')
      cy.get('#email_logs_grid_table > tbody > :nth-child(1) > :nth-child(4)').should('include.text','payment')
})
      it('Setuping the Order API method in BO', () => {
        cy.mollie_test17_admin()
        cy.login_mollie17_test()
        cy.get('#subtab-AdminMollieModule > .link').click()
        //switching the multistore
        cy.get('#header_shop > .dropdown').click()
        cy.get('#header_shop > .dropdown > .dropdown-menu').click(100,100)
        //
        cy.get('#MOLLIE_API_KEY_TEST').clear().type((Cypress.env('mollie_test_api_key')),{delay: 0, log: false})
        cy.get('#MOLLIE_PROFILE_ID').clear().type((Cypress.env('mollie_test_profile_id')),{delay: 0, log: false})
        cy.get('[for="MOLLIE_IFRAME_on"]').click()
        //Checking if saving OK
        cy.get('#module_form_submit_btn').click()
        cy.contains('The configuration has been saved!').should('exist').as('Save Successfull')
        cy.contains('Credit card').click()
        cy.get('#payment-method-form-creditcard > :nth-child(1) > .col-lg-9 > .fixed-width-xl').select('Yes')
        cy.get('#payment-method-form-creditcard > :nth-child(3) > .col-lg-9 > .fixed-width-xl').select('Orders API')
        cy.get('#module_form_submit_btn').click()
        //Checking if saving OK
        cy.contains('The configuration has been saved!').should('exist').as('Save Successfull')
})
// Starting purchasing process with Orders API
it('Checkouting the item Front-Office [Orders API]', () => {
  //Orders API item
  cy.visit('https://mollie1770test.invertusdemo.com/en/women/2-brown-bear-printed-sweater.html')
  cy.get('.add > .btn').click()
  cy.get('.cart-content-btn > .btn-primary').click()
  cy.get('.text-sm-center > .btn').click()

  // Creating random user all the time
  cy.get(':nth-child(1) > .custom-radio > input').check()
  cy.get('#customer-form > section > :nth-child(2) > .col-md-6 > .form-control').type('AUT',{delay:0})
  cy.get(':nth-child(3) > .col-md-6 > .form-control').type('AUT',{delay:0})
  const uuid = () => Cypress._.random(0, 1e6)
  const id = uuid()
  const testname = `testemail${id}@testing.com`
  cy.get(':nth-child(4) > .col-md-6 > .form-control').type(testname, {delay: 0})
  cy.get(':nth-child(6) > .col-md-6 > .input-group > .form-control').type('123456',{delay:0})
  cy.get(':nth-child(9) > .col-md-6 > .custom-checkbox > label > input').check()
  cy.get(':nth-child(11) > .col-md-6 > .custom-checkbox > label > input').check()
  cy.get('#customer-form > .form-footer > .continue').click()

  cy.get(':nth-child(6) > .col-md-6 > .form-control').type('123456',{delay:0})
  cy.get(':nth-child(7) > .col-md-6 > .form-control').type('123456',{delay:0}).as('vat number')
  cy.get(':nth-child(8) > .col-md-6 > .form-control').type('ADDR',{delay:0}).as('address')
  cy.get(':nth-child(10) > .col-md-6 > .form-control').type('54469',{delay:0}).as('zip')
  cy.get(':nth-child(11) > .col-md-6 > .form-control').type('CIT',{delay:0}).as('city')
  cy.get(':nth-child(12) > .col-md-6 > .form-control').select('Lithuania').as('country')
  cy.get(':nth-child(13) > .col-md-6 > .form-control').type('085',{delay:0}).as('telephone')
  cy.get('.form-footer > .continue').click()
  cy.get('#js-delivery > .continue').click()
  cy.contains('Credit').click()

  //Credit card inputing
  cy.frameLoaded('[data-testid=mollie-container--cardHolder] > iframe')
  cy.enter('[data-testid=mollie-container--cardHolder] > iframe').then(getBody => {
  getBody().find('#cardHolder').type('TEST TEEESSSTT')
})
  cy.enter('[data-testid=mollie-container--cardNumber] > iframe').then(getBody => {
  getBody().find('#cardNumber').type('5555555555554444')
})
  cy.enter('[data-testid=mollie-container--expiryDate] > iframe').then(getBody => {
  getBody().find('#expiryDate').type('1222')
})
  cy.enter('[data-testid=mollie-container--verificationCode] > iframe').then(getBody => {
  getBody().find('#verificationCode').type('222')
})
  cy.get('.js-terms').click()
  cy.get('.ps-shown-by-js > .btn').click()
  cy.get(':nth-child(2) > .checkbox > .checkbox__label').click()
  cy.get('.button').click()

  //Success page UI verification
  cy.get('.h1').should('include.text','Your order is confirmed')
  cy.get('#order-details > ul > :nth-child(2)').should('include.text','Credit Card')

})
it('Checking the Back-Office Order Existance, Refunding, Shipping [Orders API]', () => {
  cy.mollie_test17_admin()
  cy.login_mollie17_test()
  cy.get('#subtab-AdminParentOrders > :nth-child(1) > span').click()
  cy.get('#subtab-AdminOrders > .link').click()
  cy.get(':nth-child(1) > .column-payment').click()
  //Refunding dropdown in React
  cy.get('.btn-group-action > .btn-group > .dropdown-toggle').click()
  cy.get('[role="button"]').eq(2).click()
  cy.get('[class="swal-button swal-button--confirm"]').click()
  cy.get('[class="alert alert-success"]').should('be.visible')
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
  cy.get('#mollie_order > :nth-child(1) > :nth-child(1)').should('exist')
  cy.get('.sc-htpNat > .panel > .card-body').should('exist')
  cy.get('.btn-group > .dropdown-toggle')
  .click()
  cy.get('.btn-group > .dropdown-menu > :nth-child(1) > a').should('exist')
  cy.get('.dropdown-menu > :nth-child(2) > a').should('exist')
})
  it('Checking the Email Sending log in Prestashop [Orders API]', () => {
  cy.mollie_test17_admin()
  cy.login_mollie17_test()
  cy.get('#subtab-AdminAdvancedParameters > :nth-child(1) > span').click()
  cy.get('#subtab-AdminEmails > .link').click()
  cy.get('.card-body').contains('order_conf')
  cy.get('.card-body').contains('payment')
})
})
