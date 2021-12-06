/// <reference types="Cypress" />
context('PS177 All Payments Checking [Orders API]', () => {
  beforeEach(() => {
    cy.viewport(1920,1080)
  })
it('Enabling All Payments in BO [Orders API]', () => {
    var login = (MollieBOLoggingIn) => {
    cy.session(MollieBOLoggingIn,() => {
    cy.mollie_test17_admin()
    cy.login_mollie17_test()
    })
 	}
login('MollieBOLoggingIn')
      cy.visit('https://mollie1770test.invertusdemo.com/admin1/index.php?controller=AdminMollieModule')
      cy.get('.btn-continue').click()
      cy.get('#subtab-AdminMollieModule > .link').click()
      //switching the multistore
      cy.get('#header_shop > .dropdown').click()
      cy.get('#header_shop > .dropdown > .dropdown-menu').click(100,100)
      //
      cy.get('#MOLLIE_API_KEY_TEST').clear({force: true}).type((Cypress.env('mollie_test_api_key')),{delay: 0, log: false})
      cy.get('#MOLLIE_PROFILE_ID').clear({force: true}).type((Cypress.env('mollie_test_profile_id')),{delay: 0, log: false})
      cy.get('[for="MOLLIE_IFRAME_on"]').click()
      //giropay
      cy.get('[name="MOLLIE_METHOD_ENABLED_giropay"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_giropay"]').select('Orders API', {force: true})
      cy.get('[name="MOLLIE_METHOD_DESCRIPTION_giropay"]').clear({force: true}).type('Lorem Ipsum is simply dummy text of the printing and typesetting industry. ', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_TYPE_giropay"]').select('Fixed Fee and Percentage', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_giropay"]').clear({force: true}).type('111', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_PERCENTAGE_giropay"]').clear({force: true}).type('222', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_LIMIT_giropay"]').clear({force: true}).type('333', {force: true})
      //eps
      cy.get('[name="MOLLIE_METHOD_ENABLED_eps"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_eps"]').select('Orders API', {force: true})
      cy.get('[name="MOLLIE_METHOD_DESCRIPTION_eps"]').clear({force: true}).type('Lorem Ipsum is simply dummy text of the printing and typesetting industry. ', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_TYPE_eps"]').select('Fixed Fee and Percentage', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_eps"]').clear({force: true}).type('111', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_PERCENTAGE_eps"]').clear({force: true}).type('222', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_LIMIT_eps"]').clear({force: true}).type('333', {force: true})
      //przelewy24
      cy.get('[name="MOLLIE_METHOD_ENABLED_przelewy24"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_przelewy24"]').select('Orders API', {force: true})
      cy.get('[name="MOLLIE_METHOD_DESCRIPTION_przelewy24"]').clear({force: true}).type('Lorem Ipsum is simply dummy text of the printing and typesetting industry. ', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_TYPE_przelewy24"]').select('Fixed Fee and Percentage', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_przelewy24"]').clear({force: true}).type('111', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_PERCENTAGE_przelewy24"]').clear({force: true}).type('222', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_LIMIT_przelewy24"]').clear({force: true}).type('333', {force: true})
      //kbc
      cy.get('[name="MOLLIE_METHOD_ENABLED_kbc"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_kbc"]').select('Orders API', {force: true})
      cy.get('[name="MOLLIE_METHOD_DESCRIPTION_kbc"]').clear({force: true}).type('Lorem Ipsum is simply dummy text of the printing and typesetting industry. ', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_TYPE_kbc"]').select('Fixed Fee and Percentage', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_kbc"]').clear({force: true}).type('111', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_PERCENTAGE_kbc"]').clear({force: true}).type('222', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_LIMIT_kbc"]').clear({force: true}).type('333', {force: true})
      //voucher
      cy.get('[name="MOLLIE_METHOD_ENABLED_voucher"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_voucher"]').select('Orders API', {force: true})
      cy.get('[name="MOLLIE_METHOD_DESCRIPTION_voucher"]').clear({force: true}).type('Lorem Ipsum is simply dummy text of the printing and typesetting industry. ', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_TYPE_voucher"]').select('Fixed Fee and Percentage', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_voucher"]').clear({force: true}).type('111', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_PERCENTAGE_voucher"]').clear({force: true}).type('222', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_LIMIT_voucher"]').clear({force: true}).type('333', {force: true})
      //belfius
      cy.get('[name="MOLLIE_METHOD_ENABLED_belfius"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_belfius"]').select('Orders API', {force: true})
      cy.get('[name="MOLLIE_METHOD_DESCRIPTION_przelewy24"]').clear({force: true}).type('Lorem Ipsum is simply dummy text of the printing and typesetting industry. ', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_TYPE_belfius"]').select('Fixed Fee and Percentage', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_belfius"]').clear({force: true}).type('111', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_PERCENTAGE_belfius"]').clear({force: true}).type('222', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_LIMIT_belfius"]').clear({force: true}).type('333', {force: true})
      //bancontact
      cy.get('[name="MOLLIE_METHOD_ENABLED_bancontact"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_bancontact"]').select('Orders API', {force: true})
      cy.get('[name="MOLLIE_METHOD_DESCRIPTION_bancontact"]').clear({force: true}).type('Lorem Ipsum is simply dummy text of the printing and typesetting industry. ', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_TYPE_bancontact"]').select('Fixed Fee and Percentage', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_bancontact"]').clear({force: true}).type('111', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_PERCENTAGE_bancontact"]').clear({force: true}).type('222', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_LIMIT_bancontact"]').clear({force: true}).type('333', {force: true})
      //sofort
      cy.get('[name="MOLLIE_METHOD_ENABLED_sofort"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_sofort"]').select('Orders API', {force: true})
      cy.get('[name="MOLLIE_METHOD_DESCRIPTION_sofort"]').clear({force: true}).type('Lorem Ipsum is simply dummy text of the printing and typesetting industry. ', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_TYPE_sofort"]').select('Fixed Fee and Percentage', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_sofort"]').clear({force: true}).type('111', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_PERCENTAGE_sofort"]').clear({force: true}).type('222', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_LIMIT_sofort"]').clear({force: true}).type('333', {force: true})
      //creditcard
      cy.get('[name="MOLLIE_METHOD_ENABLED_creditcard"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_creditcard"]').select('Orders API', {force: true})
      cy.get('[name="MOLLIE_METHOD_DESCRIPTION_creditcard"]').clear({force: true}).type('Lorem Ipsum is simply dummy text of the printing and typesetting industry. ', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_TYPE_creditcard"]').select('Fixed Fee and Percentage', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_creditcard"]').clear({force: true}).type('111', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_PERCENTAGE_creditcard"]').clear({force: true}).type('222', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_LIMIT_creditcard"]').clear({force: true}).type('333', {force: true})
      //ideal
      cy.get('[name="MOLLIE_METHOD_ENABLED_ideal"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_ideal"]').select('Orders API', {force: true})
      cy.get('[name="MOLLIE_METHOD_DESCRIPTION_ideal"]').clear({force: true}).type('Lorem Ipsum is simply dummy text of the printing and typesetting industry. ', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_TYPE_ideal"]').select('Fixed Fee and Percentage', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_ideal"]').clear({force: true}).type('111', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_PERCENTAGE_ideal"]').clear({force: true}).type('222', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_LIMIT_ideal"]').clear({force: true}).type('333', {force: true})
      //klarnapaylater
      cy.get('[name="MOLLIE_METHOD_ENABLED_klarnapaylater"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_DESCRIPTION_klarnapaylater"]').clear({force: true}).type('Lorem Ipsum is simply dummy text of the printing and typesetting industry. ', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_TYPE_klarnapaylater"]').select('Fixed Fee and Percentage', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_klarnapaylater"]').clear({force: true}).type('111', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_PERCENTAGE_klarnapaylater"]').clear({force: true}).type('222', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_LIMIT_klarnapaylater"]').clear({force: true}).type('333', {force: true})
      //klarnasliceit
      cy.get('[name="MOLLIE_METHOD_ENABLED_klarnasliceit"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_DESCRIPTION_klarnasliceit"]').clear({force: true}).type('Lorem Ipsum is simply dummy text of the printing and typesetting industry. ', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_TYPE_klarnasliceit"]').select('Fixed Fee and Percentage', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_klarnasliceit"]').clear({force: true}).type('111', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_PERCENTAGE_klarnasliceit"]').clear({force: true}).type('222', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_LIMIT_klarnasliceit"]').clear({force: true}).type('333', {force: true})
      //klarnapaynow
      cy.get('[name="MOLLIE_METHOD_ENABLED_klarnapaynow"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_DESCRIPTION_klarnapaynow"]').clear({force: true}).type('Lorem Ipsum is simply dummy text of the printing and typesetting industry. ', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_TYPE_klarnapaynow"]').select('Fixed Fee and Percentage', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_klarnapaynow"]').clear({force: true}).type('111', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_PERCENTAGE_klarnapaynow"]').clear({force: true}).type('222', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_LIMIT_klarnapaynow"]').clear({force: true}).type('333', {force: true})
      //banktransfer
      cy.get('[name="MOLLIE_METHOD_ENABLED_banktransfer"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_banktransfer"]').select('Orders API', {force: true})
      cy.get('[name="MOLLIE_METHOD_DESCRIPTION_banktransfer"]').clear({force: true}).type('Lorem Ipsum is simply dummy text of the printing and typesetting industry. ', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_TYPE_banktransfer"]').select('Fixed Fee and Percentage', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_banktransfer"]').clear({force: true}).type('111', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_PERCENTAGE_banktransfer"]').clear({force: true}).type('222', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_LIMIT_banktransfer"]').clear({force: true}).type('333', {force: true})
      //paypal
      cy.get('[name="MOLLIE_METHOD_ENABLED_paypal"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_paypal"]').select('Orders API', {force: true})
      cy.get('[name="MOLLIE_METHOD_DESCRIPTION_paypal"]').clear({force: true}).type('Lorem Ipsum is simply dummy text of the printing and typesetting industry. ', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_TYPE_paypal"]').select('Fixed Fee and Percentage', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_paypal"]').clear({force: true}).type('111', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_PERCENTAGE_paypal"]').clear({force: true}).type('222', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_LIMIT_paypal"]').clear({force: true}).type('333', {force: true})
      //applepay
      cy.get('[name="MOLLIE_METHOD_ENABLED_applepay"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_DESCRIPTION_applepay"]').clear({force: true}).type('Lorem Ipsum is simply dummy text of the printing and typesetting industry. ', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_TYPE_applepay"]').select('Fixed Fee and Percentage', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_applepay"]').clear({force: true}).type('111', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_PERCENTAGE_applepay"]').clear({force: true}).type('222', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_LIMIT_applepay"]').clear({force: true}).type('333', {force: true})
      cy.get('[type="submit"]').first().click()
      cy.get('[class="alert alert-success"]').should('be.visible')
})
   // Starting purchasing process
it('Bancontact checkout FO [Orders API]', () => {
      Cypress.on('uncaught:exception', (err, runnable) => {
      // returning false here prevents Cypress from
      // failing the test
      return false
      })
      var fasterLoginDE = (LoginFoDE) => {
      cy.session (LoginFoDE, () => {
      cy.mollie_1770_test_faster_login_DE_Orders_Api()
      })
      }
      fasterLoginDE('LoginFoDE')
      cy.visit('https://mollie1770test.invertusdemo.com/en/women/2-brown-bear-printed-sweater.html')
      cy.get('.add > .btn').click()
      cy.get('.cart-content-btn > .btn-primary').click()
      cy.get('.text-sm-center > .btn').click()
      cy.contains('Germany').click()
      cy.get('.clearfix > .btn').click()
      cy.get('#js-delivery > .continue').click()
      cy.contains('Bancontact').click({force:true})
      cy.get('.js-terms').click()
      cy.get('.ps-shown-by-js > .btn').click()
      cy.setCookie(
        'SESSIONID',
        "cypress-dummy-value",
        {
            domain: '.www.mollie.com',
            sameSite: 'None',
            secure: true,
            httpOnly: true
        }
      );    // reload current page to activate cookie
      cy.reload();
      cy.get(':nth-child(2) > .checkbox > .checkbox__label').click()
      cy.get('.button').click()
      cy.url().should('include','https://mollie1770test.invertusdemo.com/')
      //Success page UI verification
      // cy.get('.h1').should('include.text','Your order is confirmed')
      // cy.get('#order-details > ul > :nth-child(2)').should('include.text','Bancontact')
})
it('Bancontact Order BO Shiping, Refunding [Orders API]', () => {
  Cypress.on('uncaught:exception', (err, runnable) => {
    // returning false here prevents Cypress from
    // failing the test
    return false
  })
  var login = (MollieBOLoggingIn) => {
    cy.session(MollieBOLoggingIn,() => {
    cy.mollie_test17_admin()
    cy.login_mollie17_test()
    })
    }
  login('MollieBOLoggingIn')
      cy.visit('https://mollie1770test.invertusdemo.com/admin1/index.php?controller=AdminOrders')
      cy.get(':nth-child(1) > .column-payment').click()
      //Refunding dropdown in React
      cy.get('.btn-group-action > .btn-group > .dropdown-toggle').eq(0).click()
      cy.get('[role="button"]').eq(2).click()
      cy.get('[class="swal-button swal-button--confirm"]').click()
      cy.get('[class="alert alert-success"]').should('be.visible')
      //Shipping button in React
      cy.get('.btn-group > [title=""]').eq(0).click()
      cy.get('[class="swal-button swal-button--confirm"]').click()
      cy.get('.swal-modal').should('exist')
      cy.get('#input-carrier').clear({force: true}).type('FedEx',{delay:0})
      cy.get('#input-code').clear({force: true}).type('123456',{delay:0})
      cy.get('#input-url').clear({force: true}).type('https://www.invertus.eu',{delay:0})
      cy.get(':nth-child(2) > .swal-button').click()
      cy.get('#mollie_order > :nth-child(1) > .alert').contains('Shipment was made successfully!')
      cy.get('[class="alert alert-success"]').should('be.visible')
})
it('iDEAL checkout FO [Orders API]', () => {
      Cypress.on('uncaught:exception', (err, runnable) => {
      // returning false here prevents Cypress from
      // failing the test
      return false
      })
      var fasterLoginDE = (LoginFoDE) => {
      cy.session (LoginFoDE, () => {
      cy.mollie_1770_test_faster_login_DE_Orders_Api()
      })
      }
      fasterLoginDE('LoginFoDE')
      cy.visit('https://mollie1770test.invertusdemo.com/en/women/2-brown-bear-printed-sweater.html')
      cy.get('.add > .btn').click()
      cy.get('.cart-content-btn > .btn-primary').click()
      cy.get('.text-sm-center > .btn').click()
      cy.contains('Germany').click()
      cy.get('.clearfix > .btn').click()
      cy.get('#js-delivery > .continue').click()
      cy.contains('iDEAL').click({force:true})
      cy.get('.js-terms').click()
      cy.get('.ps-shown-by-js > .btn').click()
      cy.setCookie(
        'SESSIONID',
        "cypress-dummy-value",
        {
            domain: '.www.mollie.com',
            sameSite: 'None',
            secure: true,
            httpOnly: true
        }
      );    // reload current page to activate cookie
      cy.reload();
      cy.get('.grid-button-ideal-ABNANL2A').click()
      cy.get(':nth-child(2) > .checkbox > .checkbox__label').click()
      cy.get('.button').click()
      cy.url().should('include','https://mollie1770test.invertusdemo.com/')
      //Success page UI verification
      // cy.get('.h1').should('include.text','Your order is confirmed')
      // cy.get('#order-details > ul > :nth-child(2)').should('include.text','iDEAL')
})
it('iDEAL Order BO Shiping, Refunding [Orders API]', () => {
  Cypress.on('uncaught:exception', (err, runnable) => {
    // returning false here prevents Cypress from
    // failing the test
    return false
  })
  var login = (MollieBOLoggingIn) => {
    cy.session(MollieBOLoggingIn,() => {
    cy.mollie_test17_admin()
    cy.login_mollie17_test()
    })
    }
  login('MollieBOLoggingIn')
      cy.visit('https://mollie1770test.invertusdemo.com/admin1/index.php?controller=AdminOrders')
      cy.get(':nth-child(1) > .column-payment').click()
      //Refunding dropdown in React
      cy.get('.btn-group-action > .btn-group > .dropdown-toggle').eq(0).click()
      cy.get('[role="button"]').eq(2).click()
      cy.get('[class="swal-button swal-button--confirm"]').click()
      cy.get('[class="alert alert-success"]').should('be.visible')
      //Shipping button in React
      cy.get('.btn-group > [title=""]').eq(0).click()
      cy.get('[class="swal-button swal-button--confirm"]').click()
      cy.get('.swal-modal').should('exist')
      cy.get('#input-carrier').clear({force: true}).type('FedEx',{delay:0})
      cy.get('#input-code').clear({force: true}).type('123456',{delay:0})
      cy.get('#input-url').clear({force: true}).type('https://www.invertus.eu',{delay:0})
      cy.get(':nth-child(2) > .swal-button').click()
      cy.get('#mollie_order > :nth-child(1) > .alert').contains('Shipment was made successfully!')
      cy.get('[class="alert alert-success"]').should('be.visible')
})
it('Klarna Slice It checkout FO [Orders API]', () => {
      Cypress.on('uncaught:exception', (err, runnable) => {
      // returning false here prevents Cypress from
      // failing the test
      return false
      })
      var fasterLoginDE = (LoginFoDE) => {
      cy.session (LoginFoDE, () => {
      cy.mollie_1770_test_faster_login_DE_Orders_Api()
      })
      }
      fasterLoginDE('LoginFoDE')
      cy.visit('https://mollie1770test.invertusdemo.com/en/women/2-brown-bear-printed-sweater.html')
      cy.get('.add > .btn').click()
      cy.get('.cart-content-btn > .btn-primary').click()
      cy.get('.text-sm-center > .btn').click()
      cy.contains('Germany').click()
      cy.get('.clearfix > .btn').click()
      cy.get('#js-delivery > .continue').click()
      cy.contains('Slice it.').click({force:true})
      cy.get('.js-terms').click()
      cy.get('.ps-shown-by-js > .btn').click()
      cy.setCookie(
        'SESSIONID',
        "cypress-dummy-value",
        {
            domain: '.www.mollie.com',
            sameSite: 'None',
            secure: true,
            httpOnly: true
        }
      );    // reload current page to activate cookie
      cy.reload();
      cy.get(':nth-child(1) > .checkbox > .checkbox__label').click()
      cy.get('.button').click()
      cy.url().should('include','https://mollie1770test.invertusdemo.com/')
      //Success page UI verification
      // cy.get('.h1').should('include.text','Your order is confirmed')
      // cy.get('#order-details > ul > :nth-child(2)').should('include.text','Slice it.')
})
it('Klarna Slice It Order BO Shiping, Refunding [Orders API]', () => {
  Cypress.on('uncaught:exception', (err, runnable) => {
    // returning false here prevents Cypress from
    // failing the test
    return false
  })
  var login = (MollieBOLoggingIn) => {
    cy.session(MollieBOLoggingIn,() => {
    cy.mollie_test17_admin()
    cy.login_mollie17_test()
    })
    }
  login('MollieBOLoggingIn')
      cy.visit('https://mollie1770test.invertusdemo.com/admin1/index.php?controller=AdminOrders')
      cy.get(':nth-child(1) > .column-payment').click()
      //Shipping button in React
      cy.get('.btn-group > [title=""]').eq(0).click()
      cy.get('[class="swal-button swal-button--confirm"]').click()
      cy.get('.swal-modal').should('exist')
      cy.get('#input-carrier').clear({force: true}).type('FedEx',{delay:0})
      cy.get('#input-code').clear({force: true}).type('123456',{delay:0})
      cy.get('#input-url').clear({force: true}).type('https://www.invertus.eu',{delay:0})
      cy.get(':nth-child(2) > .swal-button').click()
      cy.get('#mollie_order > :nth-child(1) > .alert').contains('Shipment was made successfully!')
      cy.get('[class="alert alert-success"]').should('be.visible')
      //Refunding dropdown in React
      cy.get('.btn-group-action > .btn-group > .dropdown-toggle').eq(0).click()
      cy.get('[role="button"]').eq(2).click()
      cy.get('[class="swal-button swal-button--confirm"]').click()
      cy.get('[class="alert alert-success"]').should('be.visible')
})
it('Klarna Pay Later checkout FO [Orders API]', () => {
      Cypress.on('uncaught:exception', (err, runnable) => {
      // returning false here prevents Cypress from
      // failing the test
      return false
      })
      var fasterLoginDE = (LoginFoDE) => {
      cy.session (LoginFoDE, () => {
      cy.mollie_1770_test_faster_login_DE_Orders_Api()
      })
      }
      fasterLoginDE('LoginFoDE')
      cy.visit('https://mollie1770test.invertusdemo.com/en/women/2-brown-bear-printed-sweater.html')
      cy.get('.add > .btn').click()
      cy.get('.cart-content-btn > .btn-primary').click()
      cy.get('.text-sm-center > .btn').click()
      cy.contains('Germany').click()
      cy.get('.clearfix > .btn').click()
      cy.get('#js-delivery > .continue').click()
      cy.contains('Pay later').click({force:true})
      cy.get('.js-terms').click()
      cy.get('.ps-shown-by-js > .btn').click()
      cy.setCookie(
        'SESSIONID',
        "cypress-dummy-value",
        {
            domain: '.www.mollie.com',
            sameSite: 'None',
            secure: true,
            httpOnly: true
        }
      );    // reload current page to activate cookie
      cy.reload();
      cy.get(':nth-child(1) > .checkbox > .checkbox__label').click()
      cy.get('.button').click()
      cy.url().should('include','https://mollie1770test.invertusdemo.com/')
      //Success page UI verification
      // cy.get('.h1').should('include.text','Your order is confirmed')
      // cy.get('#order-details > ul > :nth-child(2)').should('include.text','Pay later')
})
it('Klarna Pay Later Order BO Shiping, Refunding [Orders API]', () => {
  Cypress.on('uncaught:exception', (err, runnable) => {
    // returning false here prevents Cypress from
    // failing the test
    return false
  })
  var login = (MollieBOLoggingIn) => {
    cy.session(MollieBOLoggingIn,() => {
    cy.mollie_test17_admin()
    cy.login_mollie17_test()
    })
    }
  login('MollieBOLoggingIn')
      cy.visit('https://mollie1770test.invertusdemo.com/admin1/index.php?controller=AdminOrders')
      cy.get(':nth-child(1) > .column-payment').click()
      //Shipping button in React
      cy.get('.btn-group > [title=""]').eq(0).click()
      cy.get('[class="swal-button swal-button--confirm"]').click()
      cy.get('.swal-modal').should('exist')
      cy.get('#input-carrier').clear({force: true}).type('FedEx',{delay:0})
      cy.get('#input-code').clear({force: true}).type('123456',{delay:0})
      cy.get('#input-url').clear({force: true}).type('https://www.invertus.eu',{delay:0})
      cy.get(':nth-child(2) > .swal-button').click()
      cy.get('#mollie_order > :nth-child(1) > .alert').contains('Shipment was made successfully!')
      cy.get('[class="alert alert-success"]').should('be.visible')
      //Refunding dropdown in React
      cy.get('.btn-group-action > .btn-group > .dropdown-toggle').eq(0).click()
      cy.get('[role="button"]').eq(2).click()
      cy.get('[class="swal-button swal-button--confirm"]').click()
      cy.get('[class="alert alert-success"]').should('be.visible')
})
it('Credit card checkout FO [Orders API]', () => {
      Cypress.on('uncaught:exception', (err, runnable) => {
      // returning false here prevents Cypress from
      // failing the test
      return false
      })
      var fasterLoginDE = (LoginFoDE) => {
      cy.session (LoginFoDE, () => {
      cy.mollie_1770_test_faster_login_DE_Orders_Api()
      })
      }
      fasterLoginDE('LoginFoDE')
      cy.visit('https://mollie1770test.invertusdemo.com/en/women/2-brown-bear-printed-sweater.html')
      cy.get('.add > .btn').click()
      cy.get('.cart-content-btn > .btn-primary').click()
      cy.get('.text-sm-center > .btn').click()
      cy.contains('Germany').click()
      cy.get('.clearfix > .btn').click()
      cy.get('#js-delivery > .continue').click()
      cy.contains('Credit card').click({force:true})
      //Credit card inputing
      cy.frameLoaded('[data-testid=mollie-container--cardHolder] > iframe')
      cy.enter('[data-testid=mollie-container--cardHolder] > iframe').then(getBody => {
      getBody().find('#cardHolder').clear({force: true}).type('TEST TEEESSSTT')
      })
      cy.enter('[data-testid=mollie-container--cardNumber] > iframe').then(getBody => {
      getBody().find('#cardNumber').clear({force: true}).type('5555555555554444')
      })
      cy.enter('[data-testid=mollie-container--expiryDate] > iframe').then(getBody => {
      getBody().find('#expiryDate').clear({force: true}).type('1222')
      })
      cy.enter('[data-testid=mollie-container--verificationCode] > iframe').then(getBody => {
      getBody().find('#verificationCode').clear({force: true}).type('222')
      })
      cy.get('.js-terms').click()
      cy.get('.ps-shown-by-js > .btn').click()
      cy.setCookie(
        'SESSIONID',
        "cypress-dummy-value",
        {
            domain: '.www.mollie.com',
            sameSite: 'None',
            secure: true,
            httpOnly: true
        }
      );    // reload current page to activate cookie
      cy.reload();
      cy.get(':nth-child(2) > .checkbox > .checkbox__label').click()
      cy.get('.button').click()
      cy.url().should('include','https://mollie1770test.invertusdemo.com/')
      //Success page UI verification
      // cy.get('.h1').should('include.text','Your order is confirmed')
      // cy.get('#order-details > ul > :nth-child(2)').should('include.text','Pay later')
})
it('Credit card Order BO Shiping, Refunding [Orders API]', () => {
  Cypress.on('uncaught:exception', (err, runnable) => {
    // returning false here prevents Cypress from
    // failing the test
    return false
  })
  var login = (MollieBOLoggingIn) => {
    cy.session(MollieBOLoggingIn,() => {
    cy.mollie_test17_admin()
    cy.login_mollie17_test()
    })
    }
  login('MollieBOLoggingIn')
      cy.visit('https://mollie1770test.invertusdemo.com/admin1/index.php?controller=AdminOrders')
      cy.get(':nth-child(1) > .column-payment').click()
      //Shipping button in React
      cy.get('.btn-group > [title=""]').eq(0).click()
      cy.get('[class="swal-button swal-button--confirm"]').click()
      cy.get('.swal-modal').should('exist')
      cy.get('#input-carrier').clear({force: true}).type('FedEx',{delay:0})
      cy.get('#input-code').clear({force: true}).type('123456',{delay:0})
      cy.get('#input-url').clear({force: true}).type('https://www.invertus.eu',{delay:0})
      cy.get(':nth-child(2) > .swal-button').click()
      cy.get('#mollie_order > :nth-child(1) > .alert').contains('Shipment was made successfully!')
      cy.get('[class="alert alert-success"]').should('be.visible')
      //Refunding dropdown in React
      cy.get('.btn-group-action > .btn-group > .dropdown-toggle').eq(0).click()
      cy.get('[role="button"]').eq(2).click()
      cy.get('[class="swal-button swal-button--confirm"]').click()
      cy.get('[class="alert alert-success"]').should('be.visible')
})
})