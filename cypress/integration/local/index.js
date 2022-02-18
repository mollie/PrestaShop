/// <reference types="Cypress" />
context('Cypress test', () => {
  beforeEach(() => {   
  })
it('Access BO, check Mollie shortcut', () => {
    cy.visit('https://demoshop.ngrok.io/admin1/')
    cy.get('#email').type('demo@demo.com',{delay: 0, log: false})
    cy.get('#passwd').type('demodemo',{delay: 0, log: false})
    cy.get('#submit_login').click()
    cy.url().should('contain','https').screenshot()
})
})