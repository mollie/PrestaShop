/// <reference types="Cypress" />
context('PS1752 Bancontact Payment Orders/Payments API basic checkout', () => {
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
      cy.on('uncaught:exception', (err, runnable) => {
      expect(err.message)
      //using mocha's async done callback to finish
      //this test so we prove that an uncaught exception
      //was thrown
      done()
      //return false to prevent the error from
      //failing this test
      return false
      })
      cy.mollie_1752_test_faster_login_DE_Orders_Api()
      cy.get('.continue').click()
      cy.get('#js-delivery > .continue').click()
      cy.contains('Bancontact').click()
      cy.get('.ps-shown-by-js > .btn').click()
      cy.get(':nth-child(2) > .checkbox > .checkbox__label').click()
      cy.get('.button').click()

      //Success page UI verification
      cy.get('.h1').should('include.text','Your order is confirmed')
      cy.get('#order-details > ul > :nth-child(2)').should('include.text','Bancontact')
  })
it('Checking the Back-Office Order Existance [Bancontact]', () => {
      cy.on('uncaught:exception', (err, runnable) => {
      expect(err.message)
      //using mocha's async done callback to finish
      //this test so we prove that an uncaught exception
      //was thrown
      done()
      //return false to prevent the error from
      //failing this test
      return false
      })
      cy.mollie_1752_test_demo_module_dashboard()
      cy.mollie_1752_test_login()
      cy.visit('https://demo.invertus.eu/clients/mollie17-test/admin1/index.php?controller=AdminOrders')
      cy.get('tbody > :nth-child(1) > :nth-child(8)').should('include.text','Bancontact')
      cy.get('tbody > :nth-child(1) > :nth-child(9)').should('include.text','Payment accepted')
      cy.get('tbody > :nth-child(1) > :nth-child(9)').click()
      cy.get('#formAddPaymentPanel').contains('bancontact')
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
it('Checking the Email Sending log in Prestashop [Bancontact]', () => {
      cy.on('uncaught:exception', (err, runnable) => {
      expect(err.message)
      //using mocha's async done callback to finish
      //this test so we prove that an uncaught exception
      //was thrown
      done()
      //return false to prevent the error from
      //failing this test
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
      cy.on('uncaught:exception', (err, runnable) => {
      expect(err.message)
      //using mocha's async done callback to finish
      //this test so we prove that an uncaught exception
      //was thrown
      done()
      //return false to prevent the error from
      //failing this test
      return false
      })
      cy.mollie_1752_test_faster_login_DE_Payments_Api()
      cy.get('.continue').click()
      cy.get('#js-delivery > .continue').click()
      cy.contains('Bancontact').click()
      cy.get('.ps-shown-by-js > .btn').click()
      cy.get(':nth-child(2) > .checkbox > .checkbox__label').click()
      cy.get('.button').click()

      //Success page UI verification
      cy.get('.h1').should('include.text','Your order is confirmed')
      cy.get('#order-details > ul > :nth-child(2)').should('include.text','Bancontact')
})
    it('Checking the Back-Office Order Existance [Bancontact]', () => {
      cy.on('uncaught:exception', (err, runnable) => {
      expect(err.message)
      //using mocha's async done callback to finish
      //this test so we prove that an uncaught exception
      //was thrown
      done()
      //return false to prevent the error from
      //failing this test
      return false
      })
      cy.mollie_1752_test_demo_module_dashboard()
      cy.mollie_1752_test_login()
      cy.visit('https://demo.invertus.eu/clients/mollie17-test/admin1/index.php?controller=AdminOrders')
      cy.get('tbody > :nth-child(1) > :nth-child(8)').should('include.text','Bancontact')
      cy.get('tbody > :nth-child(1) > :nth-child(9)').should('include.text','Payment accepted')
      cy.get('tbody > :nth-child(1) > :nth-child(9)').click()
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
      //check partial refunding
      cy.get('.form-inline > :nth-child(2) > .input-group > .form-control').type('1,5')
})
    it('Checking the Email Sending log in Prestashop [Bancontact]', () => {
      cy.on('uncaught:exception', (err, runnable) => {
      expect(err.message)
      //using mocha's async done callback to finish
      //this test so we prove that an uncaught exception
      //was thrown
      done()
      //return false to prevent the error from
      //failing this test
      return false
      })
      cy.mollie_1752_test_demo_module_dashboard()
      cy.mollie_1752_test_login()
      cy.visit('https://demo.invertus.eu/clients/mollie17-test/admin1/index.php?controller=AdminEmails')
      cy.get('tbody > :nth-child(2) > :nth-child(4)').should('include.text','order_conf')
      cy.get('tbody > :nth-child(1) > :nth-child(4)').should('include.text','payment')
    })
})
