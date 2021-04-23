/// <reference types="Cypress" />
context('PS177 Shipping [Credit Card, iDeal, Klarna Pay Pater, Klarna Slice It]', () => {
  beforeEach(() => {
        // likely want to do this in a support file
    // so it's applied to all spec files
    // cypress/support/index.js

    Cypress.on('uncaught:exception', (err, runnable) => {
      // returning false here prevents Cypress from
      // failing the test
      return false
    })
    cy.viewport(1920,1080)
    cy.mollie_test17_admin()
    cy.login_mollie17_test()
    cy.get('#subtab-AdminParentOrders > :nth-child(1) > span').click()
    cy.get('#subtab-AdminOrders > .link').click()
  })
it('Credit Card Shipping', () => {
      cy.get('#order_osname').select('Payment accepted')
      cy.get('#order_payment').clear().type('Credit Card',{delay:0})
      cy.get(':nth-child(12) > .btn').click()
      //Orders API order sum
      cy.contains('200.00').click()
      cy.get('.btn-group > [title=""]').click()
      cy.get('.swal-modal').should('exist')
      cy.get(':nth-child(2) > .swal-button').click()
      cy.get('#input-carrier').type('FedEx',{delay:0})
      cy.get('#input-code').type('123456',{delay:0})
      cy.get('#input-url').type('https://www.invertus.eu',{delay:0})
      cy.get(':nth-child(2) > .swal-button').click()
      cy.get('#mollie_order > :nth-child(1) > .alert').contains('Shipment was made successfully!')
      cy.get('#subtab-AdminOrders > .link').click()
      cy.get('.js-grid-reset-button > .btn').click()
})
      it('iDeal Shipping', () => {
        cy.get('#order_osname').select('Payment accepted')
        cy.get('#order_payment').clear().type('iDeal',{delay:0})
        cy.get(':nth-child(12) > .btn').click()
        //Orders API order sum
        cy.contains('200.00').click()
        cy.get('.btn-group > [title=""]').click()
        cy.get('.swal-modal').should('exist')
        cy.get(':nth-child(2) > .swal-button').click()
        cy.get('#input-carrier').type('FedEx',{delay:0})
        cy.get('#input-code').type('123456',{delay:0})
        cy.get('#input-url').type('https://www.invertus.eu',{delay:0})
        cy.get(':nth-child(2) > .swal-button').click()
        cy.get('#mollie_order > :nth-child(1) > .alert').contains('Shipment was made successfully!')
        cy.get('#subtab-AdminOrders > .link').click()
        cy.get('.js-grid-reset-button > .btn').click()

})
      it('Bancontact Shipping', () => {
        cy.get('#order_osname').select('Payment accepted')
        cy.get('#order_payment').clear().type('Bancontact',{delay:0})
        cy.get(':nth-child(12) > .btn').click()
        //Orders API order sum
        cy.contains('200.00').click()
        //if else conditions will be added if needed in future for all payments, just to be sure...
        cy.get('body').then(($body) => {
          if ($body.find('.sc-jTzLTM > .panel > .card-body').panel) {
            // element found, do something here...
            cy.get('.btn-group > [title=""]').click()
            cy.get('.swal-modal').should('exist')
            cy.get(':nth-child(2) > .swal-button').click()
            cy.get('#input-carrier').type('FedEx',{delay:0})
            cy.get('#input-code').type('123456',{delay:0})
            cy.get('#input-url').type('https://www.invertus.eu',{delay:0})
            cy.get(':nth-child(2) > .swal-button').click()
            cy.get('#mollie_order > :nth-child(1) > .alert').contains('Shipment was made successfully!')
            cy.get('#subtab-AdminOrders > .link').click()
            cy.get('.js-grid-reset-button > .btn').click()
          } else {
            // do something else...
            cy.get('#subtab-AdminOrders > .link').click()
            cy.get('tbody > :nth-child(2) > :nth-child(8)').click().wait(5000)
            cy.get('.btn-group > [title=""]').click()
            cy.get('.swal-modal').should('exist')
            cy.get(':nth-child(2) > .swal-button').click()
            cy.get('#input-carrier').type('FedEx',{delay:0})
            cy.get('#input-code').type('123456',{delay:0})
            cy.get('#input-url').type('https://www.invertus.eu',{delay:0})
            cy.get(':nth-child(2) > .swal-button').click()
            cy.get('#mollie_order > :nth-child(1) > .alert').contains('Shipment was made successfully!')
            cy.get('#subtab-AdminOrders > .link').click()
            cy.get('.js-grid-reset-button > .btn').click()
          }
        })
})
      it('Klarna Slice it Shipping', () => {
        cy.get('#order_osname').select('Klarna payment authorized')
        cy.get('#order_payment').clear().type('Slice it',{delay:0})
        cy.get(':nth-child(12) > .btn').click()
        //Orders API order sum
        cy.contains('200.00').click()
        cy.get('.btn-group > [title=""]').click()
        cy.get('.swal-modal').should('exist')
        cy.get(':nth-child(2) > .swal-button').click()
        cy.get('#input-carrier').type('FedEx',{delay:0})
        cy.get('#input-code').type('123456',{delay:0})
        cy.get('#input-url').type('https://www.invertus.eu',{delay:0})
        cy.get(':nth-child(2) > .swal-button').click()
        cy.get('#mollie_order > :nth-child(1) > .alert').contains('Shipment was made successfully!')
        cy.get('#subtab-AdminOrders > .link').click()
        cy.get('.js-grid-reset-button > .btn').click()

})
      it('Klarna Pay later Shipping', () => {
        cy.get('#order_osname').select('Klarna payment authorized')
        cy.get('#order_payment').clear().type('Pay later',{delay:0})
        cy.get(':nth-child(12) > .btn').click()
        //Orders API order sum
        cy.contains('200.00').click()
        cy.get('.btn-group > [title=""]').click()
        cy.get('.swal-modal').should('exist')
        cy.get(':nth-child(2) > .swal-button').click()
        cy.get('#input-carrier').type('FedEx',{delay:0})
        cy.get('#input-code').type('123456',{delay:0})
        cy.get('#input-url').type('https://www.invertus.eu',{delay:0})
        cy.get(':nth-child(2) > .swal-button').click()
        cy.get('#mollie_order > :nth-child(1) > .alert').contains('Shipment was made successfully!')
        cy.get('#subtab-AdminOrders > .link').click()
        cy.get('.js-grid-reset-button > .btn').click()

})
})
