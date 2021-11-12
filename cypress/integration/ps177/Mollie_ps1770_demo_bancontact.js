/// <reference types="Cypress" />
context('PS1770 Bancontact Payment Orders/Payments API basic checkout', () => {
  beforeEach(() => {
    cy.viewport(1920,1080)
  })
    it('Enabling Bancontact payment [Orders API]', () => {
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
      cy.mollie_1770_test_faster_login_DE_Orders_Api()
      cy.contains('Germany').click()
      cy.get('.clearfix > .btn').click()
      cy.get('#js-delivery > .continue').click()
      cy.contains('Bancontact').click({force:true})
      cy.get('.js-terms').click()
      cy.get('.ps-shown-by-js > .btn').click()
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
      cy.get('.h1').should('include.text','Your order is confirmed')
      cy.get('#order-details > ul > :nth-child(2)').should('include.text','Bancontact')
  })
    it('Checking the Back-Office Order Existance, Refunding, Shipping [Bancontact]', () => {
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
})
    it('Checking the Email Sending log in Prestashop [Bancontact]', () => {
      cy.mollie_test17_admin()
      cy.login_mollie17_test()
      cy.get('#subtab-AdminAdvancedParameters > :nth-child(1) > span').click()
      cy.get('#subtab-AdminEmails > .link').click()
      cy.get('#email_logs_grid_table > tbody > :nth-child(2) > :nth-child(4)').should('include.text','order_conf')
      cy.get('#email_logs_grid_table > tbody > :nth-child(1) > :nth-child(4)').should('include.text','payment')
})
    it('Enabling Bancontact payment [Payments API]', () => {
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
      cy.mollie_1770_test_faster_login_DE_Payments_Api()
      cy.contains('Germany').click()
      cy.get('.clearfix > .btn').click()
      cy.get('#js-delivery > .continue').click()
      cy.contains('Bancontact').click({force:true})
      cy.get('.js-terms').click()
      cy.get('.ps-shown-by-js > .btn').click()
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
      cy.get('.h1').should('include.text','Your order is confirmed')
      cy.get('#order-details > ul > :nth-child(2)').should('include.text','Bancontact')
})
    it('Checking the Back-Office Order Existance, Refunding [Bancontact]', () => {
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
    it('Checking the Email Sending log in Prestashop [Bancontact]', () => {
        cy.mollie_test17_admin()
        cy.login_mollie17_test()
        cy.get('#subtab-AdminAdvancedParameters > :nth-child(1) > span').click()
        cy.get('#subtab-AdminEmails > .link').click()
        cy.get('.card-body').contains('order_conf')
        cy.get('.card-body').contains('payment')
})
})
