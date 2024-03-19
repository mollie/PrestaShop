/// <reference types="Cypress" />

describe('PS1785 Visual Regression tests suite', {
  retries: {
    runMode: 0,
    openMode: 0,
  },
  failFast: {
    enabled: false,
  }
},() => {
  beforeEach(() => {
    cy.CachingBOFOPS1785()
})
it('PS1785 - Testing the visual regression of General Settings page', () => {
  cy.visit('/admin1/')
  cy.get('.mi-mollie').click({fore:true})
  cy.get('#subtab-AdminMollieModule').click()
  cy.matchImage();
});
it('PS1785 - Testing the visual regression of Advanced Settings page', () => {
  cy.visit('/admin1/')
  cy.get('.mi-mollie').click({fore:true})
  cy.get('#subtab-AdminMollieModule').click()
  cy.contains('Advanced settings').click()
  cy.matchImage();
});
it('PS1785 - Testing the visual regression of Subscriptions FAQ', () => {
  cy.visit('/admin1/')
  cy.get('.mi-mollie').click({fore:true})
  cy.get('#subtab-AdminMollieModule').click()
  cy.get('#subtab-AdminMollieSubscriptionFAQ').click()
  cy.matchImage();
});
it('PS1785 - Testing the visual regression of Payments in the Checkout', () => {
  cy.navigatingToThePayment()
  cy.matchImage();
});

})
