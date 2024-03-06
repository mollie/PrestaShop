/// <reference types="Cypress" />

//Checking the console for errors
let windowConsoleError;
Cypress.on('window:before:load', (win) => {
windowConsoleError = cy.spy(win.console, 'error');
})
afterEach(() => {
expect(windowConsoleError).to.not.be.called;
})
describe('PS8 Subscriptions Test Suite', () => {
  beforeEach(() => {
      cy.viewport(1920,1080)
      cy.CachingBOFOPS8()
      cy.intercept('GET', '**/*.jpg', { // intercepting the UI with no images, for faster test run
        statusCode: 200,
        body: '', // Empty response
      })
      cy.intercept('GET', '**/*.png', {
        statusCode: 200,
        body: '', // Empty response
      })
  })
it('C176305: Check if Subscription options added in Product BO', () => {
  cy.visit('/admin1/')
  cy.get('#subtab-AdminCatalog > :nth-child(1)').click()
  cy.get('#subtab-AdminProducts > .link').click()
  cy.get('tbody').find('tr:nth-child(12)').find('a').eq(0).click() // clicks on the product ID #8
  cy.get('.product-type-preview').click()
  cy.get('.modal-content').within(() => {
    cy.get('[data-value="combinations"]').click()
    cy.contains('Change product type').click()
    cy.wait(1000)
    cy.contains('Change product type').click()
  })
  cy.contains('Combinations').click()
  cy.contains('Generate combinations').click()
  cy.contains('Daily').click({force:true})
  cy.contains('None').click({force:true})
  cy.get('.modal-footer > .btn-primary').click()
  cy.contains('Mollie Subscription - Daily').should('be.visible')
  cy.contains('Mollie Subscription - None').should('be.visible')
  cy.wait(3000)
  cy.get('#combination_list')
  .find('tr:contains("Mollie")') // Filter to only rows containing "Mollie"
  .find('td')
  .find('input[type="number"]')
  .each(($input) => {
    cy.wrap($input).clear().type('888')
  })
  cy.get('#save-combinations-edition').click()
  cy.get('#product_footer_save').click()
  cy.contains('Successful update').should('be.visible')
  cy.wait(2000)
  cy.screenshot()
})
it('C1672516: Check if Subscription options are in Product Page FO', () => { // PS 8.x test is not working on Cypress, deleting the Cart session somehow, checking for alternative test
  cy.visit('/en/')
  cy.get('[data-id-product="8"]').first().click()
  cy.get('[aria-label="Subscription"]').should('be.visible') // asserting if there is a Subscription dropdown in product page
});
it('C1672517: Check if Subscription options are implemented in My Account FO', () => {
  cy.visit('/en/')
  cy.get('[class="account"]').click()
  cy.contains('Subscriptions').click()
  cy.get('[class="page-content"]').should('be.visible')
});
})
