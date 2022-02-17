it('Should check if home page is loaded', () => {
    cy.request({
        url: 'http://demoshop.ngrok.io/admin1/',
    }).then((resp) => {
        expect(resp.status).to.eq(200)
    }).wait(5000)
})