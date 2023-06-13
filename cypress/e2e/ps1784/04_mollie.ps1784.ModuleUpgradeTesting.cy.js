/// <reference types="Cypress" />
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
describe('PS1784 Module Upgrade testing', () => {
  beforeEach(() => {
    cy.viewport(1920,1080)
    login('MollieBOFOLoggingIn')
    })
  it('Upgrading the module should be successful', () => {
      cy.visit('/admin1/')
    })
  })
