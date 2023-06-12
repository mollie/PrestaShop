/// <reference types="Cypress" />
      //Caching the BO and FO session
      const login = (MollieBOFOLoggingIn) => {
        cy.session(MollieBOFOLoggingIn,() => {
        cy.visit('/admin1/')
        cy.url().should('contain', 'https').as('Check if HTTPS exists')
        cy.get('#email').type('demo@prestashop.com',{delay: 0, log: false})
        cy.get('#passwd').type('prestashop_demo',{delay: 0, log: false})
        cy.get('#submit_login').click().wait(1000).as('Connection successsful')
        //switching the multistore PS8
        cy.get('#header_shop > .dropdown').click()
        cy.get('.open > .dropdown-menu').find('[class="shop"]').eq(1).find('[href]').eq(0).click()
        cy.visit('/SHOP2/index.php?controller=my-account')
        cy.get('#login-form [name="email"]').eq(0).type('demo@demo.com')
        cy.get('#login-form [name="password"]').eq(0).type('prestashop_demo')
        cy.get('#login-form [type="submit"]').eq(0).click({force:true})
        cy.get('#history-link > .link-item').click()
        })
        }

        describe('PS8 Module initial configuration setup', () => {
          beforeEach(() => {
              cy.viewport(1920,1080)
              login('MollieBOFOLoggingIn')
          })
        it('C339305: Connecting test API successsfully', () => {
              cy.visit('/admin1/')
        })
        })
