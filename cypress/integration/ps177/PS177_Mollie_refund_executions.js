/// <reference types="Cypress" />
context('PS177 Partial Refunding, Full Refunding [Credit Card, iDeal, Klarna Pay Pater, Klarna Slice It]', () => {
  beforeEach(() => {
        // likely want to do this in a support file
    // so it's applied to all spec files
    // cypress/support/index.js

    Cypress.on('uncaught:exception', (err, runnable) => {
      // returning false here prevents Cypress from
      // failing the test
      return false
    })
    // likely want to do this in a support file
    // so it's applied to all spec files
    // cypress/support/index.js
    Cypress.on('uncaught:exception', (err, runnable) => {
    // we expect a 3rd party library error with message 'list not defined'
    // and don't want to fail the test so we return false
    if (err.message.includes('done not defined')) {
    return false
    }
    // we still want to ensure there are no other unexpected
    // errors, so we let them fail the test
    })
    cy.viewport(1920,1080)
    cy.mollie_test17_admin()
    cy.login_mollie17_test()
    cy.wait(1000)
    cy.get('#subtab-AdminParentOrders > :nth-child(1) > span').click()
    cy.get('#subtab-AdminOrders > .link').click()
  })
it('Credit Card Refunding', () => {
      cy.get('#order_osname').select('Payment accepted')
      cy.get('#order_payment').clear().type('Credit Card',{delay:0})
      cy.get(':nth-child(13) > .btn').click()
      //Orders API order sum
      cy.contains('200.00').click().wait(7000)
      cy.get('.btn-group-action > .btn-group > .dropdown-toggle').click()
      cy.get('.btn-group-action > .btn-group > .dropdown-menu > :nth-child(1) > a').click()
      cy.get('.swal-modal').should('exist')
      cy.get(':nth-child(2) > .swal-button').click()
      cy.get('#mollie_order > :nth-child(1) > .alert').contains('Refund was made successfully!')
      cy.get('#subtab-AdminOrders > .link').click()
      //Payments API order sum
      cy.contains('100.00').click().wait(7000)
      cy.get('.form-inline > :nth-child(2) > .input-group > .form-control').type('1.51',{delay:0})
      cy.get(':nth-child(2) > .input-group > .input-group-btn > .btn').click()
      cy.get('.swal-modal').should('exist')
      cy.get(':nth-child(2) > .swal-button').click()
      cy.get('#mollie_order > :nth-child(1) > .alert').contains('Refund was made successfully!')
      cy.get('.form-inline > :nth-child(1) > .btn').click()
      cy.get('.swal-modal').should('exist')
      cy.get(':nth-child(2) > .swal-button').click()
      cy.get('#mollie_order > :nth-child(1) > .alert').contains('Refund was made successfully!').as('Successful PS177 Credit Card Refunding')
      cy.get('#subtab-AdminOrders > .link').click()
      cy.get('.js-grid-reset-button > .btn').click()
})
      it('iDeal Refunding', () => {
        cy.get('#order_osname').select('Payment accepted')
        cy.get('#order_payment').clear().type('iDeal',{delay:0})
        cy.get(':nth-child(13) > .btn').click()
        //Orders API order sum
        cy.contains('200.00').click().wait(7000)
        cy.get('.btn-group-action > .btn-group > .dropdown-toggle').click()
        cy.get('.btn-group-action > .btn-group > .dropdown-menu > :nth-child(1) > a').click()
        cy.get('.swal-modal').should('exist')
        cy.get(':nth-child(2) > .swal-button').click()
        cy.get('#mollie_order > :nth-child(1) > .alert').contains('Refund was made successfully!')
        cy.get('#subtab-AdminOrders > .link').click()
        //Payments API order sum
        cy.contains('100.00').click().wait(7000)
        cy.get('.form-inline > :nth-child(2) > .input-group > .form-control').type('1.51',{delay:0})
        cy.get(':nth-child(2) > .input-group > .input-group-btn > .btn').click()
        cy.get('.swal-modal').should('exist')
        cy.get(':nth-child(2) > .swal-button').click()
        cy.get('#mollie_order > :nth-child(1) > .alert').contains('Refund was made successfully!')
        cy.get('.form-inline > :nth-child(1) > .btn').click()
        cy.get('.swal-modal').should('exist')
        cy.get(':nth-child(2) > .swal-button').click()
        cy.get('#mollie_order > :nth-child(1) > .alert').contains('Refund was made successfully!').as('Successful PS177 iDeal Refunding')
        cy.get('#subtab-AdminOrders > .link').click()
        cy.get('.js-grid-reset-button > .btn').click()

})
      it('Bancontact Refunding', () => {
        cy.get('#subtab-AdminOrders > .link').click()
        cy.get('#order_osname').select('Payment accepted')
        cy.get('#order_payment').clear().type('Bancontact',{delay:0})
        cy.get(':nth-child(13) > .btn').click()
        //Orders API order sum
        cy.contains('200.00').click().wait(5000)
        cy.get('.btn-group-action > .btn-group > .dropdown-toggle').click()
        cy.get('.btn-group-action > .btn-group > .dropdown-menu > :nth-child(1) > a').click()
        cy.get('.swal-modal').should('exist')
        cy.get(':nth-child(2) > .swal-button').click()
        cy.get('#mollie_order > :nth-child(1) > .alert').contains('Refund was made successfully!')
        cy.get('#subtab-AdminOrders > .link').click()
        //Payments API order sum
        cy.contains('100.00').click().wait(7000)
        cy.get('.form-inline > :nth-child(2) > .input-group > .form-control').type('1.51',{delay:0})
        cy.get(':nth-child(2) > .input-group > .input-group-btn > .btn').click()
        cy.get('.swal-modal').should('exist')
        cy.get(':nth-child(2) > .swal-button').click()
        cy.get('#mollie_order > :nth-child(1) > .alert').contains('Refund was made successfully!')
        cy.get('.form-inline > :nth-child(1) > .btn').click()
        cy.get('.swal-modal').should('exist')
        cy.get(':nth-child(2) > .swal-button').click()
        cy.get('#mollie_order > :nth-child(1) > .alert').contains('Refund was made successfully!').as('Successful PS177 Bancontact Refunding')
        cy.get('#subtab-AdminOrders > .link').click()
        cy.get('.js-grid-reset-button > .btn').click()

})
      it('Klarna Slice it Refunding', () => {
      cy.get('#order_osname').select('Klarna payment authorized').wait(1000)
      cy.get('#order_payment').clear().type('Slice it',{delay:0})
      cy.get(':nth-child(13) > .btn').click()
      //Orders API order sum
      cy.contains('200.00').click().wait(7000)
      cy.get('.btn-group > .btn-primary').click()
      cy.get('.swal-modal').should('exist')
      cy.get(':nth-child(2) > .swal-button').click()
      cy.get('#input-carrier').type('FedEx')
      cy.get('#input-code').type('123456')
      cy.get('#input-url').type('https://www.invertus.eu')
      cy.get(':nth-child(2) > .swal-button').click()
      cy.get('#mollie_order > :nth-child(1) > .alert').contains('Shipment was made successfully!')
      cy.get('.btn-group-action > .btn-group > .dropdown-toggle').click()
      cy.get('.btn-group-action > .btn-group > .dropdown-menu > :nth-child(1) > a').click()
      cy.get('.swal-modal').should('exist')
      cy.get(':nth-child(2) > .swal-button').click()
      cy.get('#mollie_order > :nth-child(1) > .alert').contains('Refund was made successfully!')
      cy.get('#subtab-AdminOrders > .link').click()
      cy.get('.js-grid-reset-button > .btn').click()

})
      it('Klarna Pay later Refunding', () => {
        cy.get('#order_osname').select('Klarna payment authorized').wait(1000)
        cy.get('#order_payment').clear().type('Pay later',{delay:0})
        cy.get(':nth-child(13) > .btn').click()
        //Orders API order sum
        cy.contains('200.00').click().wait(7000)
        cy.get('.btn-group > [title=""]').click()
        cy.get('.swal-modal').should('exist')
        cy.get(':nth-child(2) > .swal-button').click()
        cy.get('#input-carrier').type('FedEx')
        cy.get('#input-code').type('123456')
        cy.get('#input-url').type('https://www.invertus.eu')
        cy.get(':nth-child(2) > .swal-button').click()
        cy.get('#mollie_order > :nth-child(1) > .alert').contains('Shipment was made successfully!')
        cy.get('.btn-group-action > .btn-group > .dropdown-toggle').click()
        cy.get('.btn-group-action > .btn-group > .dropdown-menu > :nth-child(1) > a').click()
        cy.get('.swal-modal').should('exist')
        cy.get(':nth-child(2) > .swal-button').click()
        cy.get('#mollie_order > :nth-child(1) > .alert').contains('Refund was made successfully!')
        cy.get('#subtab-AdminOrders > .link').click()
        cy.get('.js-grid-reset-button > .btn').click()

})
})
