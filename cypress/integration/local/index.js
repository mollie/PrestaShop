it('Should check if home page is loaded', () => {
    cy.request({
        url: 'https://demoshop.ngrok.io/',
    }).then((resp) => {
        expect(resp.status).to.eq(200)
    })
})
it('Access BO', () => {
    cy.visit('https://demoshop.ngrok.io/admin1/').wait(5000)
})