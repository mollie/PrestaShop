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
  cy.OpeningModuleDashboardURL()
  cy.matchImage();
});
it('PS1785 - Testing the visual regression of Advanced Settings page', () => {
  cy.OpeningModuleDashboardURL()
  cy.get('#subtab-AdminMollieAdvancedSettingsParent a').first().click({force:true})
  cy.get('#mollie-advanced-settings-root', {timeout: 30000}).should('be.visible')
  cy.matchImage();
});
it('PS1785 - Testing the visual regression of Subscriptions FAQ', () => {
  cy.OpeningModuleDashboardURL()
  cy.get('#subtab-AdminMollieSubscriptionFAQ').click()
  cy.matchImage();
});
it('PS1785 - Testing the visual regression of Payments in the Checkout', () => {
  cy.navigatingToThePayment()
  cy.matchImage();
});

})
