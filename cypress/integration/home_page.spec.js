describe('Home page of prestashop', function() {
  it('Checks if page loads', function() {
    cy.visit('/')
    cy.request({
      url: '/'
    })
      .then((resp) => {
        expect(resp.status).to.eq(200)
      })
  })
})
