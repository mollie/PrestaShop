cy.CachingBOFOPS1784()

//Checking the console for errors
let windowConsoleError;
Cypress.on('window:before:load', (win) => {
windowConsoleError = cy.spy(win.console, 'error');
})
afterEach(() => {
expect(windowConsoleError).to.not.be.called;
})
describe('PS1784 Subscriptions Test Suit', () => {
  beforeEach(() => {
      cy.viewport(1920,1080)
      login('MollieBOFOLoggingIn')
  })
it('C176305 Check if Subscription options added in Product BO', () => {
  cy.visit('/admin1/')
  cy.get('#subtab-AdminCatalog > :nth-child(1)').click()
  cy.get('#subtab-AdminProducts > .link').click()
  cy.get('[data-product-id="8"]').find('[class="btn tooltip-link product-edit"]').click()
  cy.contains('Product with combinations').click()
  cy.get('[id="tab_step3"]').click()
  cy.contains('Daily').click({force:true})
  cy.get('[class="token"]').should('be.visible')
  cy.get('#create-combinations').click()
  cy.wait(5000)
  cy.reload()
  cy.wait(5000)
  cy.contains('Mollie Subscription - Daily').should('be.visible')
  cy.get('[class="attribute-quantity"]').last().find('[type="text"]').clear().type('999')
  cy.get('#submit').click()
  cy.get('.growl-message').contains('Settings updated.')
  //Check if Subscription options are in Product Page FO
  cy.visit('/SHOP2/de/')
  cy.get('[data-id-product="8"]').click() //possible PS1784 notice exception, checking with dev...
  cy.get('a').click()
  //wip ...
  //Check if Subscription options are implemented in My Account FO
  cy.visit('/SHOP2/')
  cy.get('[class="account"]').click()
  cy.contains('Subscriptions').click()
  cy.get('[class="page-content"]').should('be.visible')
  //wip ...
});
})
