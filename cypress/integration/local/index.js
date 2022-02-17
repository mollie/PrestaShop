/// <reference types="Cypress" />
context('Cypress test', () => {
  beforeEach(() => {   
  })
it('Access BO, check Mollie shortcut', () => {
    cy.visit('https://demoshop.ngrok.io/admin1/')
    cy.get('#email').type('demo@prestashop.com',{delay: 0, log: false})
    cy.get('#passwd').type('prestashop_demo',{delay: 0, log: false})
    cy.get('#submit_login').click()
})
})