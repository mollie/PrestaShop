/// <reference types="Cypress" />
context('Purchase automation PS16 Mollie Payment/Order API Credit Card Front Office Back Office Check', () => {
  beforeEach(() => {
    cy.viewport(1920,1080)
  })
//    it('Uploading the latest module-artifact to the Prestashop', () => {
//      cy.login_mollie16_test()
//      cy.server()
//      cy.route({
//        method: 'GET',
//        url: '**/index.php**',
//        status: 500,
//        response: 500
//})
//      cy.route({
//        method: 'POST',
//        url: '**/index.php**',
//        status: 500,
//        response: 500
//})
//
//      cy.get('#subtab-AdminParentModulesSf > :nth-child(1)').click()
//      cy.get('#subtab-AdminModulesSf > .link').click()
//      cy.get('#page-header-desc-configuration-add_module').click()
//      cy.get('.module-import-start').attachFile('fixture.zip', { subjectType: 'drag-n-drop' }).wait(15000)
//      cy.get('.module-import-success-msg').should('be.visible').as('Module installed!')

//})
    // Checking the Credit Card enabling
    it('Checking the Credit Card Payment API method successfully enabling BO', () => {
      cy.on('uncaught:exception', (err, runnable) => {
    expect(err.message)

    // using mocha's async done callback to finish
    // this test so we prove that an uncaught exception
    // was thrown
    done()

    // return false to prevent the error from
    // failing this test
    return false
})
      cy.mollie_test16_admin()
      cy.login_mollie16_test()
      cy.get('#maintab-AdminMollieModule > .title').click()
      cy.get('#MOLLIE_API_KEY_TEST').clear().type('test_pACCABA9KvWGjvW9StKn7QTDNgMvzh',{delay: 0})
      cy.get('#MOLLIE_PROFILE_ID').clear().type('pfl_jUAQPFDdTR',{delay: 0})
      cy.get('[for="MOLLIE_IFRAME_on"]').click()
      //Checking if saving OK
      cy.get('#module_form_submit_btn').click()
      cy.contains('The configuration has been saved!').should('exist').as('Save Successfull')
      cy.contains('Credit card').click()
      cy.get('#payment-method-form-creditcard > :nth-child(1) > .col-lg-9 > .fixed-width-xl').select('Yes')
      cy.get('#payment-method-form-creditcard > :nth-child(3) > .col-lg-9 > .fixed-width-xl').select('Payments API')
      cy.get('#module_form_submit_btn').click()
      //Checking if saving OK
      cy.contains('The configuration has been saved!').should('exist').as('Save Successfull')
})
    // Starting purchasing process
    it('Checkouting the item Front-Office [Payments API]', () => {
      cy.on('uncaught:exception', (err, runnable) => {
    expect(err.message)

    // using mocha's async done callback to finish
    // this test so we prove that an uncaught exception
    // was thrown
    done()

    // return false to prevent the error from
    // failing this test
    return false
})
      cy.visit('https://demo.invertus.eu/clients/mollie16-test/en/home/9-test1.html')
      cy.get('.exclusive > span').click()
      cy.get('.button-medium > span').click()
      cy.get('.cart_navigation > .button > span').click()

      cy.ps16_random_user()
      cy.get('#submitAddress > span').click()
      cy.get('.cart_navigation > .button > span').click()
      cy.get('label').click()
      cy.get('.cart_navigation > .button > span').click()
      cy.contains('Credit').click()

      //Credit card inputing
      cy.get('.fancybox-inner > .mollie-iframe-container').should('be.visible').as('popup container')
      cy.get('.fancybox-item').should('be.visible').as('popup X')
      cy.frameLoaded('[data-testid=mollie-container--cardHolder] > iframe')
      cy.enter('[data-testid=mollie-container--cardHolder] > iframe').then(getBody => {
      getBody().find('#cardHolder').type('TEST TEEESSSTT')
  })
      cy.enter('[data-testid=mollie-container--cardNumber] > iframe').then(getBody => {
      getBody().find('#cardNumber').type('5555555555554444')
  })
      cy.enter('[data-testid=mollie-container--expiryDate] > iframe').then(getBody => {
      getBody().find('#expiryDate').type('1222')
})
      cy.enter('[data-testid=mollie-container--verificationCode] > iframe').then(getBody => {
      getBody().find('#verificationCode').type('222')
})
      cy.get('.fancybox-inner > .mollie-iframe-container > .row > .col-lg-3 > form > .btn').click()
      cy.get(':nth-child(2) > .checkbox > .checkbox__label').click()
      cy.get('.button').click()

      //Success page UI verification
      cy.get('#mollie-ok').should('include.text','Thank you')

  })
    it('Checking the Back-Office Order Existance [Payments API]', () => {
      cy.on('uncaught:exception', (err, runnable) => {
    expect(err.message)

    // using mocha's async done callback to finish
    // this test so we prove that an uncaught exception
    // was thrown
    done()

    // return false to prevent the error from
    // failing this test
    return false
})
      cy.mollie_test16_admin()
      cy.login_mollie16_test()
      cy.visit('https://demo.invertus.eu/clients/mollie16-test/admin1/index.php?controller=AdminOrders&token=2e9e601079755e680c5f058da5aa16d3')
      cy.get('tbody > :nth-child(1) > :nth-child(8)').should('include.text','Credit Card')
      cy.get('tbody > :nth-child(1) > :nth-child(9)').should('include.text','Payment accepted')
      cy.get(':nth-child(1) > :nth-child(13) > .btn-group > .btn').click()
      cy.get('#mollie_order > :nth-child(1)').should('exist')
      cy.get('.form-inline > :nth-child(1) > .btn').should('exist')
      cy.get('.input-group-btn > .btn').should('exist')
      cy.get('.sc-htpNat > .panel > .card-body > :nth-child(3)').should('exist')
      cy.get('.card-body > :nth-child(6)').should('exist')
      cy.get('.card-body > :nth-child(9)').should('exist')
      cy.on('uncaught:exception', (err, runnable) => {
    expect(err.message)

    // using mocha's async done callback to finish
    // this test so we prove that an uncaught exception
    // was thrown
    done()

    // return false to prevent the error from
    // failing this test
    return false
})
      cy.get('#mollie_order > :nth-child(1) > :nth-child(1)').should('exist')
      cy.get('.sc-htpNat > .panel > .card-body').should('exist')
      cy.get('.sc-bxivhb > .panel > .panel-heading').should('exist')
      cy.get('.sc-bxivhb > .panel > .card-body').should('exist')
      //check partial refunding
      cy.get('.form-inline > :nth-child(2) > .input-group > .form-control').type('1,5')
})
      it('Checking the Email Sending log in Prestashop [Payments API]', () => {
        cy.on('uncaught:exception', (err, runnable) => {
      expect(err.message)

      // using mocha's async done callback to finish
      // this test so we prove that an uncaught exception
      // was thrown
      done()

      // return false to prevent the error from
      // failing this test
      return false
  })
      cy.mollie_test16_admin()
      cy.login_mollie16_test()
      cy.visit('https://demo.invertus.eu/clients/mollie16-test/admin1/index.php?controller=AdminEmails&token=023927e534d296d1d25aab2eaa409760')
      cy.get('.table > tbody > :nth-child(1) > :nth-child(4)').should('include.text','order_conf')
      cy.get('.table > tbody > :nth-child(2) > :nth-child(4)').should('include.text','payment')
})
      it('Setuping the Order API method in BO', () => {
        cy.on('uncaught:exception', (err, runnable) => {
      expect(err.message)

      // using mocha's async done callback to finish
      // this test so we prove that an uncaught exception
      // was thrown
      done()

      // return false to prevent the error from
      // failing this test
      return false
  })
        cy.mollie_test16_admin()
        cy.login_mollie16_test()
        cy.get('#maintab-AdminMollieModule > .title').click()
        cy.get('#MOLLIE_API_KEY_TEST').clear().type('test_pACCABA9KvWGjvW9StKn7QTDNgMvzh',{delay: 0})
        cy.get('#MOLLIE_PROFILE_ID').clear().type('pfl_jUAQPFDdTR',{delay: 0})
        cy.get('[for="MOLLIE_IFRAME_on"]').click()
        //Checking if saving OK
        cy.get('#module_form_submit_btn').click()
        cy.contains('The configuration has been saved!').should('exist').as('Save Successfull')
        cy.contains('Credit card').click()
        cy.get('#payment-method-form-creditcard > :nth-child(1) > .col-lg-9 > .fixed-width-xl').select('Yes')
        cy.get('#payment-method-form-creditcard > :nth-child(3) > .col-lg-9 > .fixed-width-xl').select('Orders API')
        cy.get('#module_form_submit_btn').click()
        //Checking if saving OK
        cy.contains('The configuration has been saved!').should('exist').as('Save Successfull')
})
// Starting purchasing process with Orders API
it('Checkouting the item Front-Office [Orders API]', () => {
  cy.on('uncaught:exception', (err, runnable) => {
expect(err.message)

// using mocha's async done callback to finish
// this test so we prove that an uncaught exception
// was thrown
done()

// return false to prevent the error from
// failing this test
return false
})
  cy.visit('https://demo.invertus.eu/clients/mollie16-test/en/home/10-test1.html')
  cy.get('.exclusive > span').click()
  cy.get('.button-medium > span').click()
  cy.get('.cart_navigation > .button > span').click()

  cy.ps16_random_user()
  cy.get('#submitAddress > span').click()
  cy.get('.cart_navigation > .button > span').click()
  cy.get('label').click()
  cy.get('.cart_navigation > .button > span').click()
  cy.contains('Credit').click()

  //Credit card inputing
  cy.get('.fancybox-inner > .mollie-iframe-container').should('be.visible').as('popup container')
  cy.get('.fancybox-item').should('be.visible').as('popup X')
  cy.frameLoaded('[data-testid=mollie-container--cardHolder] > iframe')
  cy.enter('[data-testid=mollie-container--cardHolder] > iframe').then(getBody => {
  getBody().find('#cardHolder').type('TEST TEEESSSTT')
})
  cy.enter('[data-testid=mollie-container--cardNumber] > iframe').then(getBody => {
  getBody().find('#cardNumber').type('5555555555554444')
})
  cy.enter('[data-testid=mollie-container--expiryDate] > iframe').then(getBody => {
  getBody().find('#expiryDate').type('1222')
})
  cy.enter('[data-testid=mollie-container--verificationCode] > iframe').then(getBody => {
  getBody().find('#verificationCode').type('222')
})
  cy.get('.fancybox-inner > .mollie-iframe-container > .row > .col-lg-3 > form > .btn').click()
  cy.get(':nth-child(2) > .checkbox > .checkbox__label').click()
  cy.get('.button').click()

  //Success page UI verification
  cy.get('#mollie-ok').should('include.text','Thank you')

})
it('Checking the Back-Office Order Existance [Orders API]', () => {
  cy.on('uncaught:exception', (err, runnable) => {
expect(err.message)

// using mocha's async done callback to finish
// this test so we prove that an uncaught exception
// was thrown
done()

// return false to prevent the error from
// failing this test
return false
})
  cy.mollie_test16_admin()
  cy.login_mollie16_test()
  cy.visit('https://demo.invertus.eu/clients/mollie16-test/admin1/index.php?controller=AdminOrders&token=2e9e601079755e680c5f058da5aa16d3')
  cy.get('tbody > :nth-child(1) > :nth-child(8)').should('include.text','Credit Card')
  cy.get('tbody > :nth-child(1) > :nth-child(9)').should('include.text','Payment accepted')
  cy.get(':nth-child(1) > :nth-child(13) > .btn-group > .btn').click()
  cy.get('#mollie_order > :nth-child(1)').should('exist')
  cy.get('.sc-htpNat > .panel').should('exist')
  cy.get('.sc-jTzLTM > .panel').should('exist')
  cy.get('.btn-group > [title=""]').should('exist')
  cy.get('.btn-group > .btn-primary').should('exist')
  cy.get('tfoot > tr > td > .btn-group > :nth-child(2)').should('exist')

  cy.get('.sc-htpNat > .panel > .card-body > :nth-child(3)').should('exist')
  cy.get('.card-body > :nth-child(6)').should('exist')
  cy.get('.card-body > :nth-child(9)').should('exist')

     cy.on('uncaught:exception', (err, runnable) => {
    expect(err.message)

// using mocha's async done callback to finish
// this test so we prove that an uncaught exception
// was thrown
    done()

// return false to prevent the error from
// failing this test
    return false
  })
  cy.get('#mollie_order > :nth-child(1) > :nth-child(1)').should('exist')
  cy.get('.sc-htpNat > .panel > .card-body').should('exist')
  cy.get('.btn-group-action > .btn-group > .dropdown-toggle').click()
  cy.get('.btn-group > .dropdown-menu > :nth-child(1) > a').should('exist')
  cy.get('.dropdown-menu > :nth-child(2) > a').should('exist')
})
  it('Checking the Email Sending log in Prestashop [Orders API]', () => {
    cy.on('uncaught:exception', (err, runnable) => {
  expect(err.message)

  // using mocha's async done callback to finish
  // this test so we prove that an uncaught exception
  // was thrown
  done()

  // return false to prevent the error from
  // failing this test
  return false
})
  cy.mollie_test16_admin()
  cy.login_mollie16_test()
  cy.visit('https://demo.invertus.eu/clients/mollie16-test/admin1/index.php?controller=AdminEmails&token=023927e534d296d1d25aab2eaa409760')
  cy.get('.table > tbody > :nth-child(1) > :nth-child(4)').should('include.text','order_conf')
  cy.get('.table > tbody > :nth-child(2) > :nth-child(4)').should('include.text','payment')
})
})
