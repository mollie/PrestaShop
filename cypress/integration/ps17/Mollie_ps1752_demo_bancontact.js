/// <reference types="Cypress" />
context('PS1752 Bancontact Payment Orders/Payments API basic checkout',
{
    retries: {
      runMode: 2,
      openMode: 2,
    }
}, () => {
  beforeEach(() => {
    cy.viewport(1920,1080)
  })
it('Enabling Bancontact payment [Orders API]', () => {
      cy.mollie_1752_test_demo_module_dashboard()
      cy.mollie_1752_test_login()
      cy.get('#MOLLIE_API_KEY_TEST').clear().type((Cypress.env('mollie_test_api_key')),{delay: 0, log: false})
      cy.get('#MOLLIE_PROFILE_ID').clear().type((Cypress.env('mollie_test_profile_id')),{delay: 0, log: false})
      cy.get('[for="MOLLIE_IFRAME_on"]').click()
      //Checking if saving OK
      cy.get('#module_form_submit_btn').click()
      cy.contains('The configuration has been saved!').should('exist').as('Save Successfull')
      //disabling issuer popup
      cy.get('#MOLLIE_ISSUERS').select('Payment page')
      cy.get('#js-payment-methods-sortable').contains('Bancontact').click()
      cy.get('#payment-method-form-bancontact > :nth-child(1) > .col-lg-9 > .fixed-width-xl').select('Yes')
      cy.get('#payment-method-form-bancontact > :nth-child(3) > .col-lg-9 > .fixed-width-xl').select('Orders API')
      cy.get('#module_form_submit_btn').click()
      //Checking if saving OK
      cy.contains('The configuration has been saved!').should('exist').as('Save Successfull')
})
    // Starting purchasing process
it('Checkouting the item in FO [Orders API]', () => {
  Cypress.on('uncaught:exception', (err, runnable) => {
    // returning false here prevents Cypress from
    // failing the test
    return false
  })
      cy.mollie_1752_test_faster_login_DE_Orders_Api()
      cy.get('.continue').click()
      cy.get('#js-delivery > .continue').click()
      cy.contains('Bancontact').click({force:true})
      cy.get('.ps-shown-by-js > .btn').click()
      cy.get(':nth-child(2) > .checkbox > .checkbox__label').click()
      cy.get('.button').click()

      //Success page UI verification
      cy.get('.h1').should('include.text','Your order is confirmed')
      cy.get('#order-details > ul > :nth-child(2)').should('include.text','Bancontact')
  })
it('Checking the Back-Office Order Existance, Refundng, Shipping [Bancontact]', () => {
  Cypress.on('uncaught:exception', (err, runnable) => {
    // returning false here prevents Cypress from
    // failing the test
    return false
  })
      cy.mollie_1752_test_demo_module_dashboard()
      cy.mollie_1752_test_login()
      cy.visit('https://demo.invertus.eu/clients/mollie17-test/admin1/index.php?controller=AdminOrders')
      cy.get('[class=" odd"]').eq(0).click().wait(3000)
      //Refunding dropdown in React
      cy.get('.btn-group-action > .btn-group > .dropdown-toggle').click()
      cy.get('[role="button"]').eq(0).click()
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
})
it('Checking the Email Sending log in Prestashop [Bancontact]', () => {
  Cypress.on('uncaught:exception', (err, runnable) => {
    // returning false here prevents Cypress from
    // failing the test
    return false
  })
      cy.mollie_1752_test_demo_module_dashboard()
      cy.mollie_1752_test_login()
      cy.visit('https://demo.invertus.eu/clients/mollie17-test/admin1/index.php?controller=AdminEmails')
      cy.get('tbody > :nth-child(2) > :nth-child(4)').should('include.text','order_conf')
      cy.get('tbody > :nth-child(1) > :nth-child(4)').should('include.text','payment')
})
it('Enabling Bancontact payment [Payments API]', () => {
      cy.mollie_1752_test_demo_module_dashboard()
      cy.mollie_1752_test_login()
      cy.get('#MOLLIE_API_KEY_TEST').clear().type((Cypress.env('mollie_test_api_key')),{delay: 0, log: false})
      cy.get('#MOLLIE_PROFILE_ID').clear().type((Cypress.env('mollie_test_profile_id')),{delay: 0, log: false})
      cy.get('[for="MOLLIE_IFRAME_on"]').click()
      //Checking if saving OK
      cy.get('#module_form_submit_btn').click()
      cy.contains('The configuration has been saved!').should('exist').as('Save Successfull')
      //disabling issuer popup
      cy.get('#MOLLIE_ISSUERS').select('Payment page')
      cy.get('#js-payment-methods-sortable').contains('Bancontact').click()
      cy.get('#payment-method-form-bancontact > :nth-child(1) > .col-lg-9 > .fixed-width-xl').select('Yes')
      cy.get('#payment-method-form-bancontact > :nth-child(3) > .col-lg-9 > .fixed-width-xl').select('Payments API')
      cy.get('#module_form_submit_btn').click()
      //Checking if saving OK
      cy.contains('The configuration has been saved!').should('exist').as('Save Successfull')
})
it('Checkouting the item in FO [Payments API]', () => {
  Cypress.on('uncaught:exception', (err, runnable) => {
    // returning false here prevents Cypress from
    // failing the test
    return false
  })
      cy.mollie_1752_test_faster_login_DE_Payments_Api()
      cy.get('.continue').click()
      cy.get('#js-delivery > .continue').click()
      cy.contains('Bancontact').click({force:true})
      cy.get('.ps-shown-by-js > .btn').click()
      cy.get(':nth-child(2) > .checkbox > .checkbox__label').click()
      cy.get('.button').click()

      //Success page UI verification
      cy.get('.h1').should('include.text','Your order is confirmed')
      cy.get('#order-details > ul > :nth-child(2)').should('include.text','Bancontact')
})
    it('Checking the Back-Office Order Existance, Refunding [Bancontact]', () => {
      Cypress.on('uncaught:exception', (err, runnable) => {
        // returning false here prevents Cypress from
        // failing the test
        return false
      })
      cy.mollie_1752_test_demo_module_dashboard()
      cy.mollie_1752_test_login()
      cy.visit('https://demo.invertus.eu/clients/mollie17-test/admin1/index.php?controller=AdminOrders')
      //Refunding checking for Payments API
      cy.get('tbody > :nth-child(1) > :nth-child(8)').should('include.text','Bancontact')
      cy.get('tbody > :nth-child(1) > :nth-child(9)').should('include.text','Payment accepted')
      cy.get(':nth-child(1) > :nth-child(15) > .btn-group > .btn').click()
      cy.get('#formAddPaymentPanel').contains('bancontact')
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
    it('Checking the Email Sending log in Prestashop [Bancontact]', () => {
      Cypress.on('uncaught:exception', (err, runnable) => {
        // returning false here prevents Cypress from
        // failing the test
        return false
      })
      cy.mollie_1752_test_demo_module_dashboard()
      cy.mollie_1752_test_login()
      cy.visit('https://demo.invertus.eu/clients/mollie17-test/admin1/index.php?controller=AdminEmails')
      cy.get('tbody > :nth-child(2) > :nth-child(4)').should('include.text','order_conf')
      cy.get('tbody > :nth-child(1) > :nth-child(4)').should('include.text','payment')
    })
})