//Caching the BO and FO session
const login = (MollieBOFOLoggingIn) => {
  cy.session(MollieBOFOLoggingIn,() => {
  cy.visit('/admin1/')
  cy.url().should('contain', 'https').as('Check if HTTPS exists')
  cy.get('#email').type('demo@demo.com',{delay: 0, log: false})
  cy.get('#passwd').type('demodemo',{delay: 0, log: false})
  cy.get('#submit_login').click().wait(1000).as('Connection successsful')
  //switching the multistore PS1784
  cy.get('#header_shop > .dropdown').click()
  cy.get('.open > .dropdown-menu').find('[class="shop"]').eq(1).find('[href]').eq(0).click()
  cy.visit('/SHOP2/index.php?controller=my-account')
  cy.get('#login-form [name="email"]').eq(0).type('demo@demo.com')
  cy.get('#login-form [name="password"]').eq(0).type('demodemo')
  cy.get('#login-form [type="submit"]').eq(0).click({force:true})
  cy.get('#history-link > .link-item').click()
  })
  }

//Checing the console for errors
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
it('Check if Subscription options added in Product BO', () => {
  cy.visit('/admin1/')
  cy.get('#subtab-AdminCatalog > :nth-child(1)').click()
  cy.get('#subtab-AdminProducts > .link').click()
  cy.contains('Hummingbird printed t-shirt').click()
  cy.get('[name="show_variations"]').check()
  cy.get('[id="tab_step3"]').click()
  cy.contains('Daily').click({force:true})
  cy.get('[class="token"]').should('be.visible')
  cy.get('#create-combinations').click()
  cy.wait(5000)
  cy.contains('Mollie subscription - Daily').should('be.visible')
  cy.wait(5000)
  cy.get('[class="attribute-quantity"]').last().find('[type="text"]').clear().type('999')
  cy.get('#submit').click()
  cy.get('.growl-message').contains('Settings updated.')
});
it.only('Check if Subscription options are in Product Page FO', () => {
  cy.visit('/SHOP2/de/')
  cy.get('.products > :nth-child(1)').click()
  cy.get('a').click()
  //wip
});
it('Check if Subscription options are implemented in My Account FO', () => {
  cy.visit('/SHOP2/')
  cy.get('[class="account"]').click()
  cy.contains('Mollie subscriptions').click()
  cy.get('[class="page-content"]').should('be.visible')
  //wip
});
})
