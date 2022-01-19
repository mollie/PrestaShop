/// <reference types="Cypress" />
context('PS17 Shipping [Credit Card, iDeal, Klarna Pay Pater, Klarna Slice It]', () => {
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
      cy.visit('https://demo.invertus.eu/clients/mollie17-test/admin1/')
      cy.mollie_1752_test_login()
      cy.visit('https://demo.invertus.eu/clients/mollie17-test/admin1/index.php?controller=AdminOrders')
  })
it('Credit Card Shipping', () => {
      cy.get(':nth-child(9) > .filter').select('Payment accepted').wait(1000)
      cy.get(':nth-child(8) > .filter').type('Credit Card',{delay:0})
      cy.get('#submitFilterButtonorder').click()
      //Orders API order sum
      cy.contains('200.00').click().wait(3000)
      cy.get('.btn-group > [title=""]').click()
      cy.get('.swal-modal').should('exist')
      cy.get(':nth-child(2) > .swal-button').click()
      cy.get('#input-carrier').type('FedEx',{delay:0})
      cy.get('#input-code').type('123456',{delay:0})
      cy.get('#input-url').type('https://www.invertus.eu',{delay:0})
      cy.get(':nth-child(2) > .swal-button').click()
      cy.get('#mollie_order > :nth-child(1) > .alert').contains('Shipment was made successfully!')

})
      it('iDeal Shipping', () => {
      cy.get(':nth-child(9) > .filter').select('Payment accepted').wait(1000)
      cy.get(':nth-child(8) > .filter').type('iDeal',{delay:0})
      cy.get('#submitFilterButtonorder').click()
      //Orders API order sum
      cy.contains('200.00').click().wait(3000)
      //if else conditions will be added if needed in future for all payments, just to be sure...
      cy.get('body').then(($body) => {
        if ($body.find('.sc-jTzLTM > .panel').panel) {
          // element found, do something here...
          cy.get('.btn-group > [title=""]').click()
          cy.get('.swal-modal').should('exist')
          cy.get(':nth-child(2) > .swal-button').click()
          cy.get('#input-carrier').type('FedEx',{delay:0})
          cy.get('#input-code').type('123456',{delay:0})
          cy.get('#input-url').type('https://www.invertus.eu',{delay:0})
          cy.get(':nth-child(2) > .swal-button').click()
          cy.get('#mollie_order > :nth-child(1) > .alert').contains('Shipment was made successfully!')
        } else {
          // do something else...
          cy.visit('https://demo.invertus.eu/clients/mollie17-test/admin1/index.php?controller=AdminOrders')
          cy.get(':nth-child(9) > .filter').select('Payment accepted').wait(1000)
          cy.get(':nth-child(8) > .filter').type('iDeal',{delay:0})
          cy.get('#submitFilterButtonorder').click()
          cy.get('tbody > :nth-child(2) > :nth-child(8)').click()
          cy.get('.btn-group > [title=""]').click()
          cy.get('.swal-modal').should('exist')
          cy.get(':nth-child(2) > .swal-button').click()
          cy.get('#input-carrier').type('FedEx',{delay:0})
          cy.get('#input-code').type('123456',{delay:0})
          cy.get('#input-url').type('https://www.invertus.eu',{delay:0})
          cy.get(':nth-child(2) > .swal-button').click()
          cy.get('#mollie_order > :nth-child(1) > .alert').contains('Shipment was made successfully!')
        }
      })

})
      it('Bancontact Shipping', () => {
      cy.get(':nth-child(9) > .filter').select('Payment accepted').wait(1000)
      cy.get(':nth-child(8) > .filter').type('Bancontact',{delay:0})
      cy.get('#submitFilterButtonorder').click()
      //Orders API order sum
      cy.contains('200.00').click().wait(3000)
      cy.get('.btn-group > [title=""]').click()
      cy.get('.swal-modal').should('exist')
      cy.get(':nth-child(2) > .swal-button').click()
      cy.get('#input-carrier').type('FedEx',{delay:0})
      cy.get('#input-code').type('123456',{delay:0})
      cy.get('#input-url').type('https://www.invertus.eu',{delay:0})
      cy.get(':nth-child(2) > .swal-button').click()
      cy.get('#mollie_order > :nth-child(1) > .alert').contains('Shipment was made successfully!')
})
      it('Klarna Slice It Shipping', () => {
      cy.get(':nth-child(9) > .filter').select('Klarna payment authorized').wait(1000)
      cy.get(':nth-child(8) > .filter').type('Slice it',{delay:0})
      cy.get('#submitFilterButtonorder').click()
      //Orders API order sum
      cy.contains('200.00').click().wait(3000)
      cy.get('.btn-group > [title=""]').click()
      cy.get('.swal-modal').should('exist')
      cy.get(':nth-child(2) > .swal-button').click()
      cy.get('#input-carrier').type('FedEx',{delay:0})
      cy.get('#input-code').type('123456',{delay:0})
      cy.get('#input-url').type('https://www.invertus.eu',{delay:0})
      cy.get(':nth-child(2) > .swal-button').click()
      cy.get('#mollie_order > :nth-child(1) > .alert').contains('Shipment was made successfully!')
})
      it('Klarna Pay Later Shipping', () => {
      cy.get(':nth-child(9) > .filter').select('Klarna payment authorized').wait(1000)
      cy.get(':nth-child(8) > .filter').type('Pay later',{delay:0})
      cy.get('#submitFilterButtonorder').click()
      //Orders API order sum
      cy.contains('200.00').click().wait(3000)
      cy.get('.btn-group > [title=""]').click()
      cy.get('.swal-modal').should('exist')
      cy.get(':nth-child(2) > .swal-button').click()
      cy.get('#input-carrier').type('FedEx',{delay:0})
      cy.get('#input-code').type('123456',{delay:0})
      cy.get('#input-url').type('https://www.invertus.eu',{delay:0})
      cy.get(':nth-child(2) > .swal-button').click()
      cy.get('#mollie_order > :nth-child(1) > .alert').contains('Shipment was made successfully!')
})
})
