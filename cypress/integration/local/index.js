it('Should check if home page is loaded', () => {
    cy.request({
        url: '/',
    }).then((resp) => {
        expect(resp.status).to.eq(200)
    })
})

it('Test BO login and Mollie shortcut', () => {
    cy.visit('/admin1')
    cy.get('#email').type('demo@demo.com',{delay: 0, log: false})
    cy.get('#passwd').type('demodemo',{delay: 0, log: false})
    cy.get('#submit_login').click()
    cy.contains('Mollie')
})

