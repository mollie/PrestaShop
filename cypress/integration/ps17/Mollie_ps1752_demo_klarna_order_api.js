/// <reference types="Cypress" />
context('Klarna [Pay Later, Slice It] Payment PS1752 Order API check', () => {
  beforeEach(() => {
    cy.viewport(1920,1080)
  })
it('Checking the Klarna [Pay Later] Order API method successfully enabling BO', () => {
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
      cy.get('#js-payment-methods-sortable').contains('Pay later').click()
      cy.get('#payment-method-form-klarnapaylater > :nth-child(1) > .col-lg-9 > .fixed-width-xl').select('Yes')
      cy.get('#payment-method-form-klarnapaylater > :nth-child(3) > .col-lg-9 > .fixed-width-xl').select('Orders API')
      cy.get('#module_form_submit_btn').click()
      //Checking if saving OK
      cy.contains('The configuration has been saved!').should('exist').as('Save Successfull')
})
    // Starting purchasing process
it('Checkouting the item Front-Office [Pay Later]', () => {
  Cypress.on('uncaught:exception', (err, runnable) => {
    // returning false here prevents Cypress from
    // failing the test
    return false
  })
      cy.mollie_1752_test_faster_login_DE_Orders_Api()
      cy.contains('GERMANY').click()
      cy.get('.continue').click()
      cy.get('#js-delivery > .continue').click()
      cy.contains('Pay later').click({force:true})
      cy.get('.ps-shown-by-js > .btn').click()
      cy.get(':nth-child(1) > .checkbox > .checkbox__label').click()
      cy.get('.button').click()

      //Success page UI verification
      cy.get('.h1').should('include.text','Your order is confirmed')
      cy.get('#order-details > ul > :nth-child(2)').should('include.text','Pay later')
  })
it('Checking the Back-Office Order Existance, Refunding, Shipping [Pay Later]', () => {
      Cypress.on('uncaught:exception', (err, runnable) => {
        // returning false here prevents Cypress from
        // failing the test
        return false
      })
      cy.mollie_1752_test_demo_module_dashboard()
      cy.mollie_1752_test_login()
      //For Orders API only
      cy.visit('https://demo.invertus.eu/clients/mollie17-test/admin1/index.php?controller=AdminOrders')
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
it('Checking the Email Sending log in Prestashop [Pay Later]', () => {
  Cypress.on('uncaught:exception', (err, runnable) => {
    // returning false here prevents Cypress from
    // failing the test
    return false
  })
      cy.mollie_1752_test_demo_module_dashboard()
      cy.mollie_1752_test_login()
      cy.visit('https://demo.invertus.eu/clients/mollie17-test/admin1/index.php?controller=AdminEmails&token=023927e534d296d1d25aab2eaa409760')
      cy.get('[class="js-grid-table table "]').contains('order_conf')
      cy.get('[class="js-grid-table table "]').contains('payment')
})
it('Setuping the Order API method in BO [Slice it]', () => {
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
      cy.get('#js-payment-methods-sortable').contains('Slice it').click()
      cy.get('#payment-method-form-klarnasliceit > :nth-child(1) > .col-lg-9 > .fixed-width-xl').select('Yes')
      cy.get('#payment-method-form-klarnasliceit > :nth-child(3) > .col-lg-9 > .fixed-width-xl').select('Orders API')
      cy.get('#module_form_submit_btn').click()
      //Checking if saving OK
      cy.contains('The configuration has been saved!').should('exist').as('Save Successfull')
})
it('Checkouting the item Front-Office [Slice It]', () => {
  Cypress.on('uncaught:exception', (err, runnable) => {
    // returning false here prevents Cypress from
    // failing the test
    return false
  })
      cy.mollie_1752_test_faster_login_DE_Orders_Api()
      cy.contains('GERMANY').click()
      cy.get('.continue').click()
      cy.get('#js-delivery > .continue').click()
      cy.contains('Slice it').click({force:true})
      cy.get('.ps-shown-by-js > .btn').click()
      cy.get(':nth-child(1) > .checkbox > .checkbox__label').click()
      cy.get('.button').click()

      //Success page UI verification
      cy.get('.h1').should('include.text','Your order is confirmed')
      cy.get('#order-details > ul > :nth-child(2)').should('include.text','Slice it')
})
    it('Checking the Back-Office Order Existance, Refunding, Shipping [Slice it]', () => {
      Cypress.on('uncaught:exception', (err, runnable) => {
        // returning false here prevents Cypress from
        // failing the test
        return false
      })
      cy.mollie_1752_test_demo_module_dashboard()
      cy.mollie_1752_test_login()
      //For Orders API only
      cy.visit('https://demo.invertus.eu/clients/mollie17-test/admin1/index.php?controller=AdminOrders')
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
    it('Checking the Email Sending log in Prestashop [Slice it]', () => {
      Cypress.on('uncaught:exception', (err, runnable) => {
        // returning false here prevents Cypress from
        // failing the test
        return false
      })
      cy.mollie_1752_test_demo_module_dashboard()
      cy.mollie_1752_test_login()
      cy.visit('https://demo.invertus.eu/clients/mollie17-test/admin1/index.php?controller=AdminEmails&token=023927e534d296d1d25aab2eaa409760')
      cy.get('[class="js-grid-table table "]').contains('order_conf')
      cy.get('[class="js-grid-table table "]').contains('payment')
    })
})
