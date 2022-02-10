it('Should check if home page is loaded', () => {
    cy.request({
        url: '/',
    }).then((resp) => {
        expect(resp.status).to.eq(200)
    })
})

it('test bo', () => {
    cy.visit('http://demoshop.eu.ngrok.io/admin1')
})

