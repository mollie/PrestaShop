it('Should check if home page is loaded', () => {
    cy.request({
        url: '/',
    }).then((resp) => {
        expect(resp.status).to.eq(200)
    })
})

it('test bo', () => {
    cy.visit('https://demoshop.eu.ngrok.io/admin1').wait(5000)
})

