/// <reference types="Cypress" />
context('PS177 All Payments Checking [Payments API]', () => {
  beforeEach(() => {
    cy.viewport(1920,1080)
  })
it('Enabling All Payments in BO [Payments API]', () => {
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
      cy.get('#MOLLIE_API_KEY_TEST').clear().type((Cypress.env('mollie_test_api_key')),{delay: 0, log: false})
      cy.get('#MOLLIE_PROFILE_ID').clear().type((Cypress.env('mollie_test_profile_id')),{delay: 0, log: false})
      cy.get('[for="MOLLIE_IFRAME_on"]').click()
      cy.get('[name="MOLLIE_METHOD_ENABLED_giropay"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_giropay"]').select('Payments API', {force: true})
      cy.get('[name="MOLLIE_METHOD_ENABLED_eps"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_eps"]').select('Payments API', {force: true})
      cy.get('[name="MOLLIE_METHOD_ENABLED_przelewy24"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_przelewy24"]').select('Payments API', {force: true})
      cy.get('[name="MOLLIE_METHOD_ENABLED_kbc"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_kbc"]').select('Payments API', {force: true})
      cy.get('[name="MOLLIE_METHOD_ENABLED_voucher"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_ENABLED_belfius"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_belfius"]').select('Payments API', {force: true})
      cy.get('[name="MOLLIE_METHOD_ENABLED_bancontact"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_bancontact"]').select('Payments API', {force: true})
      cy.get('[name="MOLLIE_METHOD_ENABLED_sofort"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_sofort"]').select('Payments API', {force: true})
      cy.get('[name="MOLLIE_METHOD_ENABLED_creditcard"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_creditcard"]').select('Payments API', {force: true})
      cy.get('[name="MOLLIE_METHOD_ENABLED_ideal"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_ideal"]').select('Payments API', {force: true})
      cy.get('[name="MOLLIE_METHOD_ENABLED_klarnapaylater"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_ENABLED_klarnasliceit"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_ENABLED_klarnapaynow"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_ENABLED_banktransfer"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_banktransfer"]').select('Payments API', {force: true})
      cy.get('[name="MOLLIE_METHOD_ENABLED_paypal"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_paypal"]').select('Payments API', {force: true})
      cy.get('[name="MOLLIE_METHOD_ENABLED_applepay"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_paypal"]').select('Payments API', {force: true})
      cy.get('[type="submit"]').first().click()
      cy.get('[class="alert alert-success"]').should('be.visible')
})
   // Starting purchasing process
it('Bancontact checkout FO [Payments API]', () => {
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
it('Bancontact Order BO Shiping, Refunding [Payments API]', () => {
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
      cy.get('.btn-group-action > .btn-group > .dropdown-toggle').click()
      cy.get('[role="button"]').eq(2).click()
      cy.get('[class="swal-button swal-button--confirm"]').click()
      cy.get('[class="alert alert-success"]').should('be.visible')
      //Shipping button in React
      cy.get('.btn-group > [title=""]').click()
      cy.get('[class="swal-button swal-button--confirm"]').click()
      cy.get('.swal-modal').should('exist')
      cy.get('#input-carrier').type('FedEx',{delay:0})
      cy.get('#input-code').type('123456',{delay:0})
      cy.get('#input-url').type('https://www.invertus.eu',{delay:0})
      cy.get(':nth-child(2) > .swal-button').click()
      cy.get('#mollie_order > :nth-child(1) > .alert').contains('Shipment was made successfully!')
      cy.get('[class="alert alert-success"]').should('be.visible')
})
it('iDEAL checkout FO [Payments API]', () => {
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
it('iDEAL Order BO Shiping, Refunding [Payments API]', () => {
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
      cy.get('.btn-group-action > .btn-group > .dropdown-toggle').click()
      cy.get('[role="button"]').eq(2).click()
      cy.get('[class="swal-button swal-button--confirm"]').click()
      cy.get('[class="alert alert-success"]').should('be.visible')
      //Shipping button in React
      cy.get('.btn-group > [title=""]').click()
      cy.get('[class="swal-button swal-button--confirm"]').click()
      cy.get('.swal-modal').should('exist')
      cy.get('#input-carrier').type('FedEx',{delay:0})
      cy.get('#input-code').type('123456',{delay:0})
      cy.get('#input-url').type('https://www.invertus.eu',{delay:0})
      cy.get(':nth-child(2) > .swal-button').click()
      cy.get('#mollie_order > :nth-child(1) > .alert').contains('Shipment was made successfully!')
      cy.get('[class="alert alert-success"]').should('be.visible')
})
it('Klarna Slice It checkout FO [Payments API]', () => {
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
it('Klarna Slice It Order BO Shiping, Refunding [Payments API]', () => {
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
      cy.get('.btn-group > [title=""]').click()
      cy.get('[class="swal-button swal-button--confirm"]').click()
      cy.get('.swal-modal').should('exist')
      cy.get('#input-carrier').type('FedEx',{delay:0})
      cy.get('#input-code').type('123456',{delay:0})
      cy.get('#input-url').type('https://www.invertus.eu',{delay:0})
      cy.get(':nth-child(2) > .swal-button').click()
      cy.get('#mollie_order > :nth-child(1) > .alert').contains('Shipment was made successfully!')
      cy.get('[class="alert alert-success"]').should('be.visible')
      //Refunding dropdown in React
      cy.get('.btn-group-action > .btn-group > .dropdown-toggle').click()
      cy.get('[role="button"]').eq(2).click()
      cy.get('[class="swal-button swal-button--confirm"]').click()
      cy.get('[class="alert alert-success"]').should('be.visible')
})
it('Klarna Pay Later checkout FO [Payments API]', () => {
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
it('Klarna Pay Later Order BO Shiping, Refunding [Payments API]', () => {
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
      cy.get('.btn-group > [title=""]').click()
      cy.get('[class="swal-button swal-button--confirm"]').click()
      cy.get('.swal-modal').should('exist')
      cy.get('#input-carrier').type('FedEx',{delay:0})
      cy.get('#input-code').type('123456',{delay:0})
      cy.get('#input-url').type('https://www.invertus.eu',{delay:0})
      cy.get(':nth-child(2) > .swal-button').click()
      cy.get('#mollie_order > :nth-child(1) > .alert').contains('Shipment was made successfully!')
      cy.get('[class="alert alert-success"]').should('be.visible')
      //Refunding dropdown in React
      cy.get('.btn-group-action > .btn-group > .dropdown-toggle').click()
      cy.get('[role="button"]').eq(2).click()
      cy.get('[class="swal-button swal-button--confirm"]').click()
      cy.get('[class="alert alert-success"]').should('be.visible')
})
it('Credit card checkout FO [Payments API]', () => {
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
it('Credit card Order BO Shiping, Refunding [Payments API]', () => {
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
      cy.get('.btn-group > [title=""]').click()
      cy.get('[class="swal-button swal-button--confirm"]').click()
      cy.get('.swal-modal').should('exist')
      cy.get('#input-carrier').type('FedEx',{delay:0})
      cy.get('#input-code').type('123456',{delay:0})
      cy.get('#input-url').type('https://www.invertus.eu',{delay:0})
      cy.get(':nth-child(2) > .swal-button').click()
      cy.get('#mollie_order > :nth-child(1) > .alert').contains('Shipment was made successfully!')
      cy.get('[class="alert alert-success"]').should('be.visible')
      //Refunding dropdown in React
      cy.get('.btn-group-action > .btn-group > .dropdown-toggle').click()
      cy.get('[role="button"]').eq(2).click()
      cy.get('[class="swal-button swal-button--confirm"]').click()
      cy.get('[class="alert alert-success"]').should('be.visible')
})
})