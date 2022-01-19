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
      cy.get('.continue').click()
      cy.get('#js-delivery > .continue').click()
      cy.contains('Pay later').click()
      cy.get('.ps-shown-by-js > .btn').click()
      cy.get(':nth-child(1) > .checkbox > .checkbox__label').click()
      cy.get('.button').click()

      //Success page UI verification
      cy.get('.h1').should('include.text','Your order is confirmed')
      cy.get('#order-details > ul > :nth-child(2)').should('include.text','Pay later')
  })
it('Checking the Back-Office Order Existance [Pay Later]', () => {
      cCypress.on('uncaught:exception', (err, runnable) => {
        // returning false here prevents Cypress from
        // failing the test
        return false
      })
      cy.mollie_1752_test_demo_module_dashboard()
      cy.mollie_1752_test_login()
      cy.visit('https://demo.invertus.eu/clients/mollie17-test/admin1/index.php?controller=AdminOrders&token=1f3f51817ce3f9adbf23aede4ad9428e')
      cy.get('tbody > :nth-child(1) > :nth-child(8)').should('include.text','Pay later')
      cy.get('tbody > :nth-child(1) > :nth-child(9)').should('include.text','Klarna payment authorized')
      cy.get('tbody > :nth-child(1) > :nth-child(9)').click()
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
it('Checking the Email Sending log in Prestashop [Pay Later]', () => {
  Cypress.on('uncaught:exception', (err, runnable) => {
    // returning false here prevents Cypress from
    // failing the test
    return false
  })
      cy.mollie_1752_test_demo_module_dashboard()
      cy.mollie_1752_test_login()
      cy.visit('https://demo.invertus.eu/clients/mollie17-test/admin1/index.php?controller=AdminEmails&token=023927e534d296d1d25aab2eaa409760')
      cy.get('tbody > :nth-child(2) > :nth-child(4)').should('include.text','order_conf')
      cy.get('tbody > :nth-child(1) > :nth-child(4)').should('include.text','payment')
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
      cy.get('.continue').click()
      cy.get('#js-delivery > .continue').click()
      cy.contains('Slice it').click()
      cy.get('.ps-shown-by-js > .btn').click()
      cy.get(':nth-child(1) > .checkbox > .checkbox__label').click()
      cy.get('.button').click()

      //Success page UI verification
      cy.get('.h1').should('include.text','Your order is confirmed')
      cy.get('#order-details > ul > :nth-child(2)').should('include.text','Slice it')
})
    it('Checking the Back-Office Order Existance [Slice it]', () => {
      Cypress.on('uncaught:exception', (err, runnable) => {
        // returning false here prevents Cypress from
        // failing the test
        return false
      })
      cy.mollie_1752_test_demo_module_dashboard()
      cy.mollie_1752_test_login()
      cy.visit('https://demo.invertus.eu/clients/mollie17-test/admin1/index.php?controller=AdminOrders&token=1f3f51817ce3f9adbf23aede4ad9428e')
      cy.get('tbody > :nth-child(1) > :nth-child(8)').should('include.text','Slice it')
      cy.get('tbody > :nth-child(1) > :nth-child(9)').should('include.text','Klarna payment authorized')
      cy.get('tbody > :nth-child(1) > :nth-child(9)').click()
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
    it('Checking the Email Sending log in Prestashop [Slice it]', () => {
      Cypress.on('uncaught:exception', (err, runnable) => {
        // returning false here prevents Cypress from
        // failing the test
        return false
      })
      cy.mollie_1752_test_demo_module_dashboard()
      cy.mollie_1752_test_login()
      cy.visit('https://demo.invertus.eu/clients/mollie17-test/admin1/index.php?controller=AdminEmails&token=023927e534d296d1d25aab2eaa409760')
      cy.get('tbody > :nth-child(2) > :nth-child(4)').should('include.text','order_conf')
      cy.get('tbody > :nth-child(1) > :nth-child(4)').should('include.text','payment')
    })
})
