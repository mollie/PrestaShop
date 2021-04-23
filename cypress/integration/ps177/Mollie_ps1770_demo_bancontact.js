/// <reference types="Cypress" />
context('PS1770 Bancontact Payment Orders/Payments API basic checkout', () => {
  beforeEach(() => {
    cy.viewport(1920,1080)
  })
//     it('Uploading the latest module-artifact to the Prestashop', () => {
//       cy.mollie_test17_admin()
//       cy.login_mollie17_test()
//       cy.server()
//       cy.route({
//         method: 'GET',
//         url: '**/index.php**',
//         status: 500,
//         response: 500
// })
//       cy.route({
//         method: 'POST',
//         url: '**/index.php**',
//         status: 500,
//         response: 500
// })
//
//       cy.get('#subtab-AdminParentModulesSf > :nth-child(1)').click()
//       cy.get('#subtab-AdminModulesSf > .link').click()
//       cy.get('#page-header-desc-configuration-add_module').click()
//       cy.get('.module-import-start').attachFile('fixture.zip', { subjectType: 'drag-n-drop' }).wait(15000)
//       cy.get('.module-import-success-msg').should('be.visible').as('Module installed!')
//
// })
    it('Enabling Bancontact payment [Orders API]', () => {
      cy.mollie_test17_admin()
      cy.login_mollie17_test()
      cy.get('#subtab-AdminMollieModule > .link').click()
      cy.get('#MOLLIE_API_KEY_TEST').clear().type('test_pACCABA9KvWGjvW9StKn7QTDNgMvzh',{delay: 0})
      cy.get('#MOLLIE_PROFILE_ID').clear().type('pfl_jUAQPFDdTR',{delay: 0})
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
      cy.contains('Bancontact').click()
      cy.get('.js-terms').click()
      cy.get('.ps-shown-by-js > .btn').click()
      cy.get(':nth-child(2) > .checkbox > .checkbox__label').click()
      cy.get('.button').click()

      //Success page UI verification
      cy.get('.h1').should('include.text','Your order is confirmed')
      cy.get('#order-details > ul > :nth-child(2)').should('include.text','Bancontact')
  })
    it('Checking the Back-Office Order Existance [Bancontact]', () => {
      cy.mollie_test17_admin()
      cy.login_mollie17_test()
      cy.get('#subtab-AdminParentOrders > :nth-child(1) > span').click()
      cy.get('#subtab-AdminOrders > .link').click()
      cy.get('tbody > :nth-child(1) > :nth-child(8)').should('include.text','Bancontact')
      cy.get(':nth-child(1) > .choice-type').should('include.text','Payment accepted')
      cy.get(':nth-child(1) > .column-payment').click()
      cy.get('#historyTabContent > .card > .card-body > .table > tbody > :nth-child(2) > :nth-child(1)').should('include.text','Awaiting Mollie payment')
      cy.get('#mollie_order > :nth-child(1)').should('exist')
      cy.get('.sc-htpNat > .panel').should('exist')
      cy.get('.sc-jTzLTM > .panel').should('exist')
      cy.get('.btn-group > [title=""]').should('exist')
      cy.get('.btn-group > .btn-primary').should('exist')
      cy.get('tfoot > tr > td > .btn-group > :nth-child(2)').should('exist')
      cy.get('.sc-htpNat > .panel > .card-body > :nth-child(3)').should('exist')
      cy.get('.card-body > :nth-child(6)').should('exist')
      cy.get('#mollie_order > :nth-child(1) > :nth-child(1)').should('exist')
      cy.get('.sc-htpNat > .panel > .card-body').should('exist')
      cy.get('.btn-group > .dropdown-toggle')
      .click()
      cy.get('.btn-group > .dropdown-menu > :nth-child(1) > a').should('exist')
      cy.get('.dropdown-menu > :nth-child(2) > a').should('exist')
      cy.get('#view_order_payments_block > .card-body').contains('bancontact').should('exist')
      cy.get('.sc-htpNat > .panel').contains('bancontact').should('exist')
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
      cy.get('#MOLLIE_API_KEY_TEST').clear().type('test_pACCABA9KvWGjvW9StKn7QTDNgMvzh',{delay: 0})
      cy.get('#MOLLIE_PROFILE_ID').clear().type('pfl_jUAQPFDdTR',{delay: 0})
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
      cy.contains('Bancontact').click()
      cy.get('.js-terms').click()
      cy.get('.ps-shown-by-js > .btn').click()
      cy.get(':nth-child(2) > .checkbox > .checkbox__label').click()
      cy.get('.button').click()

      //Success page UI verification
      cy.get('.h1').should('include.text','Your order is confirmed')
      cy.get('#order-details > ul > :nth-child(2)').should('include.text','Bancontact')
})
    it('Checking the Back-Office Order Existance [Bancontact]', () => {
      cy.mollie_test17_admin()
      cy.login_mollie17_test()
      cy.get('#subtab-AdminParentOrders > :nth-child(1) > span').click()
      cy.get('#subtab-AdminOrders > .link').click()
      cy.get('tbody > :nth-child(1) > :nth-child(8)').should('include.text','Bancontact')
      cy.get(':nth-child(1) > .choice-type').should('include.text','Payment accepted')
      cy.get(':nth-child(1) > .column-payment').click()
      cy.get('#historyTabContent > .card > .card-body > .table > tbody > :nth-child(2) > :nth-child(1)').should('include.text','Awaiting Mollie payment')
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
        cy.mollie_test17_admin()
        cy.login_mollie17_test()
        cy.get('#subtab-AdminAdvancedParameters > :nth-child(1) > span').click()
        cy.get('#subtab-AdminEmails > .link').click()
        cy.get('#email_logs_grid_table > tbody > :nth-child(2) > :nth-child(4)').should('include.text','order_conf')
        cy.get('#email_logs_grid_table > tbody > :nth-child(1) > :nth-child(4)').should('include.text','payment')
})
})
