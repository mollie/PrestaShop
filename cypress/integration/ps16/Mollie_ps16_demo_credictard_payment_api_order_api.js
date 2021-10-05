/// <reference types="Cypress" />
context('Purchase automation PS16 Mollie Payment/Order API Credit Card Front Office Back Office Check',
{
    retries: {
      runMode: 2,
      openMode: 2,
    }
}, () => {
  beforeEach(() => {
    cy.viewport(1920,1080)
  })
    // Checking the Credit Card enabling
    it('Checking the Credit Card Payment API method successfully enabling BO', () => {
      Cypress.on('uncaught:exception', (err, runnable) => {
        // returning false here prevents Cypress from
        // failing the test
        return false
      })
      cy.mollie_test16_admin()
      cy.login_mollie16_test()
      cy.get('#maintab-AdminMollieModule > .title').click()
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
      Cypress.on('uncaught:exception', (err, runnable) => {
        // returning false here prevents Cypress from
        // failing the test
        return false
      })
      cy.visit('https://demo.invertus.eu/clients/mollie16-test/en/home/9-test1.html')
      cy.get('.exclusive > span').click()
      cy.get('.button-medium > span').click()
      cy.get('.cart_navigation > .button > span').click()

      cy.ps16_random_user()
      cy.get('#submitAddress > span').click()
      cy.get('.cart_navigation > .button > span').click()
      cy.get('label').click()
      cy.get('.cart_navigation > .button > span').click()
      cy.contains('Credit').click()

      //Credit card inputing
      cy.get('.fancybox-inner > .mollie-iframe-container').should('be.visible').as('popup container')
      cy.get('.fancybox-item').should('be.visible').as('popup X')
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
      cy.get('.fancybox-inner > .mollie-iframe-container > .row > .col-lg-3 > form > .btn').click()
      cy.setCookie(
        'SESSIONID',
        "cypress-dummy-value",
        {
            domain: '.www.mollie.com',
            sameSite: 'None',
            secure: true,
            httpOnly: true
        }
      );    // reload current page to activate cookie
      cy.reload();
      cy.get(':nth-child(2) > .checkbox > .checkbox__label').click()
      cy.get('.button').click()

      //Success page UI verification
      cy.get('#mollie-ok').should('include.text','Thank you')

  })
    it('Checking the Back-Office Order Existance, Refunding [Payments API]', () => {
      Cypress.on('uncaught:exception', (err, runnable) => {
        // returning false here prevents Cypress from
        // failing the test
        return false
      })
      cy.mollie_test16_admin()
      cy.login_mollie16_test()
      cy.visit('https://demo.invertus.eu/clients/mollie16-test/admin1/index.php?controller=AdminOrders&token=2e9e601079755e680c5f058da5aa16d3')
      cy.get('tbody > :nth-child(1) > :nth-child(8)').should('include.text','Credit Card')
      cy.get('tbody > :nth-child(1) > :nth-child(9)').should('include.text','Payment accepted')
      cy.get(':nth-child(1) > :nth-child(14) > .btn-group > .btn').click()
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
      Cypress.on('uncaught:exception', (err, runnable) => {
        // returning false here prevents Cypress from
        // failing the test
        return false
      })
      cy.mollie_test16_admin()
      cy.login_mollie16_test()
      cy.visit('https://demo.invertus.eu/clients/mollie16-test/admin1/index.php?controller=AdminEmails&token=023927e534d296d1d25aab2eaa409760')
      cy.get('.table > tbody > :nth-child(1) > :nth-child(4)').should('include.text','order_conf')
      cy.get('.table > tbody > :nth-child(2) > :nth-child(4)').should('include.text','payment')
})
      it('Setuping the Order API method in BO', () => {
        Cypress.on('uncaught:exception', (err, runnable) => {
          // returning false here prevents Cypress from
          // failing the test
          return false
        })
        cy.mollie_test16_admin()
        cy.login_mollie16_test()
        cy.get('#maintab-AdminMollieModule > .title').click()
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
  Cypress.on('uncaught:exception', (err, runnable) => {
    // returning false here prevents Cypress from
    // failing the test
    return false
  })
  cy.visit('https://demo.invertus.eu/clients/mollie16-test/en/home/10-test1.html')
  cy.get('.exclusive > span').click()
  cy.get('.button-medium > span').click()
  cy.get('.cart_navigation > .button > span').click()
  cy.ps16_random_user()
  cy.get('#submitAddress > span').click()
  cy.get('.cart_navigation > .button > span').click()
  cy.get('label').click()
  cy.get('.cart_navigation > .button > span').click()
  cy.contains('Credit').click()

  //Credit card inputing
  cy.get('.fancybox-inner > .mollie-iframe-container').should('be.visible').as('popup container')
  cy.get('.fancybox-item').should('be.visible').as('popup X')
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
  cy.get('.fancybox-inner > .mollie-iframe-container > .row > .col-lg-3 > form > .btn').click()
  cy.setCookie(
        'SESSIONID',
        "cypress-dummy-value",
        {
            domain: '.www.mollie.com',
            sameSite: 'None',
            secure: true,
            httpOnly: true
        }
      );    // reload current page to activate cookie
      cy.reload();
  cy.get(':nth-child(2) > .checkbox > .checkbox__label').click()
  cy.get('.button').click()

  //Success page UI verification
  cy.get('#mollie-ok').should('include.text','Thank you')

})
it('Checking the Back-Office Order Existance, Refunding, Shipping [Orders API]', () => {
  Cypress.on('uncaught:exception', (err, runnable) => {
    // returning false here prevents Cypress from
    // failing the test
    return false
  })
  cy.mollie_test16_admin()
  cy.login_mollie16_test()
  cy.visit('https://demo.invertus.eu/clients/mollie16-test/admin1/index.php?controller=AdminOrders')
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
  it('Checking the Email Sending log in Prestashop [Orders API]', () => {
    Cypress.on('uncaught:exception', (err, runnable) => {
      // returning false here prevents Cypress from
      // failing the test
      return false
    })
  cy.mollie_test16_admin()
  cy.login_mollie16_test()
  cy.visit('https://demo.invertus.eu/clients/mollie16-test/admin1/index.php?controller=AdminEmails&token=023927e534d296d1d25aab2eaa409760')
  cy.get('.table > tbody > :nth-child(1) > :nth-child(4)').should('include.text','order_conf')
  cy.get('.table > tbody > :nth-child(2) > :nth-child(4)').should('include.text','payment')
})
})
