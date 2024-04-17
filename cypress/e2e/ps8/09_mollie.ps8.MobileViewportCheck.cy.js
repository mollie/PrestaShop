/// <reference types="Cypress" />

describe('PS8 Viewports check', {
  retries: {
    runMode: 0,
    openMode: 0,
  },
  failFast: {
    enabled: false,
  }
},() => {
  beforeEach(() => {
  cy.viewport("iphone-x")
  cy.CachingBOFOPS8()
})
  it('C2920232: PS8 - iPhone X viewport validation flow', () => {
  cy.visit('/de/index.php?controller=history')
  cy.get('[title="Reorder"]').first().click()
  cy.contains('NL').click()
  //Billing country LT, DE etc.
  cy.get('.clearfix > .btn').click()
  cy.get('#js-delivery > .continue').click()
  //Payment method choosing
  cy.contains('Bancontact').click({force:true})
  cy.get('.condition-label > .js-terms').click({force:true})
  cy.contains('Place order').click()
  cy.get('[value="paid"]').click()
  cy.get('[class="button form__button"]').click()
  cy.get('#content-hook_order_confirmation > .card-block').should('be.visible')
});
})
