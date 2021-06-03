/// <reference types="Cypress" />
context('iDeal Payment PS1752 Payment/Order API check', () => {
  beforeEach(() => {
    cy.viewport(1920,1080)
  })
    // Checking the iDEAL enabling
it('Checking the iDEAL Payment API method successfully enabling BO', () => {
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
      cy.get('#js-payment-methods-sortable').contains('iDEAL').click()
      cy.get('#payment-method-form-ideal > :nth-child(1) > .col-lg-9 > .fixed-width-xl').select('Yes')
      cy.get('#payment-method-form-ideal > :nth-child(3) > .col-lg-9 > .fixed-width-xl').select('Payments API')
      cy.get('#module_form_submit_btn').click()
      //Checking if saving OK
      cy.contains('The configuration has been saved!').should('exist').as('Save Successfull')
})
    // Starting purchasing process
it('Checkouting the item Front-Office [Payments API]', () => {
  Cypress.on('uncaught:exception', (err, runnable) => {
    // returning false here prevents Cypress from
    // failing the test
    return false
  })
      cy.visit('https://demo.invertus.eu/clients/mollie17-test/en/home/20-testproduct1.html')
      cy.get('.add > .btn').click()
      cy.get('.cart-content-btn > .btn-primary').click()
      cy.get('.text-sm-center > .btn').click()

      // Creating random user all the time
      cy.get(':nth-child(1) > .custom-radio > input').check()
      cy.get(':nth-child(3) > .col-md-6 > .form-control').type('AUT',{delay:0})
      cy.get(':nth-child(4) > .col-md-6 > .form-control').type('AUT',{delay:0})
      const uuid = () => Cypress._.random(0, 1e6)
      const id = uuid()
      const testname = `testemail${id}@testing.com`
      cy.get(':nth-child(5) > .col-md-6 > .form-control').type(testname, {delay: 0})
      cy.get(':nth-child(7) > .col-md-6 > .input-group > .form-control').type('123456',{delay:0})
      cy.get('#customer-form > .form-footer > .continue').click()
      cy.get(':nth-child(7) > .col-md-6 > .form-control').type('AUT',{delay:0})
      cy.get(':nth-child(8) > .col-md-6 > .form-control').type('AUT',{delay:0})
      cy.get(':nth-child(9) > .col-md-6 > .form-control').type('AUT',{delay:0})
      cy.get(':nth-child(10) > .col-md-6 > .form-control').type('AUT',{delay:0})
      cy.get(':nth-child(11) > .col-md-6 > .form-control').type('54466',{delay:0})
      cy.get(':nth-child(12) > .col-md-6 > .form-control').type('AUT',{delay:0})
      cy.get(':nth-child(13) > .col-md-6 > .form-control').select('Lithuania')
      cy.get(':nth-child(14) > .col-md-6 > .form-control').type('+123456',{delay:0})
      cy.get('.continue').click()
      cy.get('#js-delivery > .continue').click()
      cy.contains('iDEAL').click()
      cy.get('.ps-shown-by-js > .btn').click()
      cy.get('.payment-method-list > :nth-child(1)').click()
      cy.get(':nth-child(2) > .checkbox > .checkbox__label').click()
      cy.get('.button').click()

      //Success page UI verification
      cy.get('.h1').should('include.text','Your order is confirmed')
      cy.get('#order-details > ul > :nth-child(2)').should('include.text','iDEAL')
  })
it('Checking the Back-Office Order Existance [Payments API]', () => {
  Cypress.on('uncaught:exception', (err, runnable) => {
    // returning false here prevents Cypress from
    // failing the test
    return false
  })
      cy.mollie_1752_test_demo_module_dashboard()
      cy.mollie_1752_test_login()
      cy.visit('https://demo.invertus.eu/clients/mollie17-test/admin1/index.php?controller=AdminOrders&token=1f3f51817ce3f9adbf23aede4ad9428e')
      cy.get('tbody > :nth-child(1) > :nth-child(8)').should('include.text','iDEAL')
      cy.get('tbody > :nth-child(1) > :nth-child(9)').should('include.text','Payment accepted')
      cy.get('tbody > :nth-child(1) > :nth-child(9)').click()
      cy.get('#formAddPaymentPanel').contains('ideal')
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
it('Checking the Email Sending log in Prestashop [Payments API]', () => {
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
it('Setuping the Order API method in BO', () => {
      cy.mollie_1752_test_demo_module_dashboard()
      cy.mollie_1752_test_login()
      cy.get('#MOLLIE_API_KEY_TEST').clear().type((Cypress.env('mollie_test_api_key')),{delay: 0, log: false})
      cy.get('#MOLLIE_PROFILE_ID').clear().type((Cypress.env('mollie_test_profile_id')),{delay: 0, log: false})
      cy.get('[for="MOLLIE_IFRAME_on"]').click()
      //Checking if saving OK
      cy.get('#module_form_submit_btn').click()
      cy.contains('The configuration has been saved!').should('exist').as('Save Successfull')
      cy.get('#js-payment-methods-sortable').contains('iDEAL').click()
      cy.get('#payment-method-form-ideal > :nth-child(1) > .col-lg-9 > .fixed-width-xl').select('Yes')
      cy.get('#payment-method-form-ideal > :nth-child(3) > .col-lg-9 > .fixed-width-xl').select('Orders API')
      cy.get('#module_form_submit_btn').click()
      //Checking if saving OK
      cy.contains('The configuration has been saved!').should('exist').as('Save Successfull')
})
// Starting purchasing process with Orders API
it('Checkouting the item Front-Office [Orders API]', () => {
  Cypress.on('uncaught:exception', (err, runnable) => {
    // returning false here prevents Cypress from
    // failing the test
    return false
  })
      cy.visit('https://demo.invertus.eu/clients/mollie17-test/en/home/21-testproduct1.html')
      cy.get('.add > .btn').click()
      cy.get('.cart-content-btn > .btn-primary').click()
      cy.get('.text-sm-center > .btn').click()

      // Creating random user all the time
      cy.get(':nth-child(1) > .custom-radio > input').check()
      cy.get(':nth-child(3) > .col-md-6 > .form-control').type('AUT',{delay:0})
      cy.get(':nth-child(4) > .col-md-6 > .form-control').type('AUT',{delay:0})
      const uuid = () => Cypress._.random(0, 1e6)
      const id = uuid()
      const testname = `testemail${id}@testing.com`
      cy.get(':nth-child(5) > .col-md-6 > .form-control').type(testname, {delay: 0})
      cy.get(':nth-child(7) > .col-md-6 > .input-group > .form-control').type('123456',{delay:0})
      cy.get('#customer-form > .form-footer > .continue').click()
      cy.get(':nth-child(7) > .col-md-6 > .form-control').type('AUT',{delay:0})
      cy.get(':nth-child(8) > .col-md-6 > .form-control').type('AUT',{delay:0})
      cy.get(':nth-child(9) > .col-md-6 > .form-control').type('AUT',{delay:0})
      cy.get(':nth-child(10) > .col-md-6 > .form-control').type('AUT',{delay:0})
      cy.get(':nth-child(11) > .col-md-6 > .form-control').type('54466',{delay:0})
      cy.get(':nth-child(12) > .col-md-6 > .form-control').type('AUT',{delay:0})
      cy.get(':nth-child(13) > .col-md-6 > .form-control').select('Lithuania')
      cy.get(':nth-child(14) > .col-md-6 > .form-control').type('+123456',{delay:0})
      cy.get('.continue').click()
      cy.get('#js-delivery > .continue').click()
      cy.contains('iDEAL').click()
      cy.get('.ps-shown-by-js > .btn').click()
      cy.get('.payment-method-list > :nth-child(1)').click()
      cy.get(':nth-child(2) > .checkbox > .checkbox__label').click()
      cy.get('.button').click()

      //Success page UI verification
      cy.get('.h1').should('include.text','Your order is confirmed')
      cy.get('#order-details > ul > :nth-child(2)').should('include.text','iDEAL')
})
    it('Checking the Back-Office Order Existance [Orders API]', () => {
      Cypress.on('uncaught:exception', (err, runnable) => {
        // returning false here prevents Cypress from
        // failing the test
        return false
      })
      cy.mollie_1752_test_demo_module_dashboard()
      cy.mollie_1752_test_login()
      cy.visit('https://demo.invertus.eu/clients/mollie17-test/admin1/index.php?controller=AdminOrders&token=1f3f51817ce3f9adbf23aede4ad9428e')
      cy.get('tbody > :nth-child(1) > :nth-child(8)').should('include.text','iDEAL')
      cy.get('tbody > :nth-child(1) > :nth-child(9)').should('include.text','Payment accepted')
      cy.get('tbody > :nth-child(1) > :nth-child(9)').click()
      cy.get('#formAddPaymentPanel').contains('ideal')
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
    it('Checking the Email Sending log in Prestashop [Orders API]', () => {
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
    it('Checking iDEAL issuer popup enabled', () => {
      cy.mollie_1752_test_demo_module_dashboard()
      cy.mollie_1752_test_login()
      cy.get('#MOLLIE_ISSUERS').select('On click')
      cy.get('#module_form_submit_btn').click()
      cy.contains('The configuration has been saved!').should('exist').as('Save Successfull')
      cy.on('uncaught:exception', (err, runnable) => {
        Cypress.on('uncaught:exception', (err, runnable) => {
          // returning false here prevents Cypress from
          // failing the test
          return false
        })
      cy.visit('https://demo.invertus.eu/clients/mollie17-test/en/men/1-1-hummingbird-printed-t-shirt.html')
      cy.get('.add > .btn').click()
      cy.get('.cart-content-btn > .btn-primary').click()
      cy.get('.text-sm-center > .btn').click()

      // Creating random user all the time
      cy.get(':nth-child(1) > .custom-radio > input').check()
      cy.get(':nth-child(3) > .col-md-6 > .form-control').type('AUT',{delay:0})
      cy.get(':nth-child(4) > .col-md-6 > .form-control').type('AUT',{delay:0})
      const uuid = () => Cypress._.random(0, 1e6)
      const id = uuid()
      const testname = `testemail${id}@testing.com`
      cy.get(':nth-child(5) > .col-md-6 > .form-control').type(testname, {delay: 0})
      cy.get(':nth-child(7) > .col-md-6 > .input-group > .form-control').type('123456',{delay:0})
      cy.get('#customer-form > .form-footer > .continue').click()
      cy.get(':nth-child(7) > .col-md-6 > .form-control').type('AUT',{delay:0})
      cy.get(':nth-child(8) > .col-md-6 > .form-control').type('AUT',{delay:0})
      cy.get(':nth-child(9) > .col-md-6 > .form-control').type('AUT',{delay:0})
      cy.get(':nth-child(10) > .col-md-6 > .form-control').type('AUT',{delay:0})
      cy.get(':nth-child(11) > .col-md-6 > .form-control').type('54466',{delay:0})
      cy.get(':nth-child(12) > .col-md-6 > .form-control').type('AUT',{delay:0})
      cy.get(':nth-child(13) > .col-md-6 > .form-control').select('Lithuania')
      cy.get(':nth-child(14) > .col-md-6 > .form-control').type('+123456',{delay:0})
      cy.get('.continue').click()
      cy.get('#js-delivery > .continue').click()
      cy.contains('iDEAL').click()
      //checking the existance of bank popup function
      cy.get('#mollie-issuer-dropdown-button').should('exist')
      cy.get('#mollie-issuer-dropdown-button').click()
      cy.get('[data-ideal-issuer="ideal_ABNANL2A"]').should('exist')
})
})
