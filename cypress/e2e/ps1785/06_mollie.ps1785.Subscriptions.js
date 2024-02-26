/// <reference types="Cypress" />

//Checking the console for errors
let windowConsoleError;
Cypress.on('window:before:load', (win) => {
windowConsoleError = cy.spy(win.console, 'error');
})
afterEach(() => {
expect(windowConsoleError).to.not.be.called;
})
describe('PS1785 Subscriptions Test Suite', () => {
  beforeEach(() => {
      cy.viewport(1920,1080)
      cy.CachingBOFOPS1785()
  })
it.skip('C176305: Check if Subscription options added in Product BO', () => { // temporary skip, somehow duplicated Subscriptions UI appearing in the product page
  cy.visit('/admin1/')
  cy.get('#subtab-AdminCatalog > :nth-child(1)').click()
  cy.get('#subtab-AdminProducts > .link').click()
  cy.get('[data-product-id="8"]').find('[class="btn tooltip-link product-edit"]').click()
  cy.contains('Product with combinations').click()
  cy.get('[id="tab_step3"]').click()
  cy.contains('Daily').click({force:true})
  cy.contains('None').click({force:true})
  cy.get('[class="token"]').should('be.visible')
  cy.get('#create-combinations').click()
  cy.wait(5000)
  cy.reload()
  cy.wait(5000)
  cy.contains('Mollie Subscription - Daily').should('be.visible')
  cy.contains('Mollie Subscription - None').should('be.visible')
  cy.get('[class="attribute-quantity"]').first().find('[type="text"]').clear().type('999')
  cy.get('[class="attribute-quantity"]').last().find('[type="text"]').clear().type('999')
  cy.get('#submit').click()
  cy.get('.growl-message').contains('Settings updated.')
})
it.skip('C1672516: Check if Subscription options are in Product Page FO and then register the Subscription product by purchasing it', () => {
  cy.visit('/de/')
  cy.get('[data-id-product="8"]').click()
  cy.get('[aria-label="Subscription"]').should('be.visible') //asserting if there is a Subscription dropdown in product page
  cy.contains('Add to cart').click()
  cy.contains('Proceed to checkout').click()
  cy.contains('Proceed to checkout').click()
  cy.contains('DE').click()
  cy.get('.clearfix > .btn').click()
  cy.get('#js-delivery > .continue').click()
  //Payment method choosing
  cy.contains('Karte').click({force:true})
  //Credit card inputing
  cy.CreditCardFillingIframe()
  cy.get('.condition-label > .js-terms').click({force:true})
  cy.get('.ps-shown-by-js > .btn').click()
  cy.get('[value="paid"]').click()
  cy.get('[class="button form__button"]').click()
});
it.skip('C1672517: Check if Subscription options are implemented in My Account FO', () => {
  cy.visit('/en/')
  cy.get('[class="account"]').click()
  cy.contains('Subscriptions').click()
  cy.get('[class="page-content"]').should('be.visible')
});
})
