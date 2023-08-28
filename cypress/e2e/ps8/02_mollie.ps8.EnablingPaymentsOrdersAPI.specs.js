/// <reference types="Cypress" />
//Caching the BO and FO session
const login = (MollieBOFOLoggingIn) => {
  cy.session(MollieBOFOLoggingIn,() => {
  cy.visit('/admin1/')
  cy.url().should('contain', 'https').as('Check if HTTPS exists')
  cy.get('#email').type('demo@demo.com',{delay: 0, log: false})
  cy.get('#passwd').type('prestashop_demo',{delay: 0, log: false})
  cy.get('#submit_login').click().wait(1000).as('Connection successsful')
  })
  }
//Checking the console for errors
let windowConsoleError;
Cypress.on('window:before:load', (win) => {
  windowConsoleError = cy.spy(win.console, 'error');
})
let failEarly = false;
afterEach(() => {
  expect(windowConsoleError).to.not.be.called;
  if (failEarly) throw new Error("Failing Early due to an API or other module configuration problem. If running on CI, please check Cypress VIDEOS/SCREENSHOTS in the Artifacts for more details.")
})
afterEach(function() {
  if (this.currentTest.state === "failed") failEarly = true
});
describe('PS8 Enabling Payments', () => {
  beforeEach(() => {
      cy.viewport(1920,1080)
      login('MollieBOFOLoggingIn')
  })
it('C339341: 04 Enabling All payments in Module BO [Orders API]', () => {
    cy.visit('/admin1/')
    cy.OpeningModuleDashboardURL()
    cy.ConfOrdersAPI8()
    cy.get('[type="submit"]').first().click({force:true})
    cy.get('[class="alert alert-success"]').should('be.visible')
})
})
