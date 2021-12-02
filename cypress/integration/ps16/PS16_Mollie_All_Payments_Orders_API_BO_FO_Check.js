/// <reference types="Cypress" />
context('PS16 All Payments Checking', () => {
  beforeEach(() => {
    cy.viewport(1920,1080)
  })

it('Enabling All Payments in BO [Orders API]', () => {
    var login = (MollieBOLoggingIn) => {
    cy.session(MollieBOLoggingIn,() => {
      cy.mollie_test16_admin()
      cy.login_mollie16_test()
    })
    }
      login('MollieBOLoggingIn')
      cy.visit('https://demo.invertus.eu/clients/mollie16-test/admin1/index.php?controller=AdminMollieModule')
      cy.get('#MOLLIE_API_KEY_TEST').clear().type((Cypress.env('mollie_test_api_key')),{delay: 0, log: false})
      cy.get('#MOLLIE_PROFILE_ID').clear().type((Cypress.env('mollie_test_profile_id')),{delay: 0, log: false})
      cy.get('[for="MOLLIE_IFRAME_on"]').click()
      cy.get('[name="MOLLIE_METHOD_ENABLED_giropay"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_giropay"]').select('Orders API', {force: true})
      cy.get('[name="MOLLIE_METHOD_ENABLED_eps"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_eps"]').select('Orders API', {force: true})
      cy.get('[name="MOLLIE_METHOD_ENABLED_przelewy24"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_przelewy24"]').select('Orders API', {force: true})
      cy.get('[name="MOLLIE_METHOD_ENABLED_kbc"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_kbc"]').select('Orders API', {force: true})
      cy.get('[name="MOLLIE_METHOD_ENABLED_voucher"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_voucher"]').select('Orders API', {force: true})
      cy.get('[name="MOLLIE_METHOD_ENABLED_belfius"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_belfius"]').select('Orders API', {force: true})
      cy.get('[name="MOLLIE_METHOD_ENABLED_bancontact"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_bancontact"]').select('Orders API', {force: true})
      cy.get('[name="MOLLIE_METHOD_ENABLED_sofort"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_sofort"]').select('Orders API', {force: true})
      cy.get('[name="MOLLIE_METHOD_ENABLED_creditcard"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_creditcard"]').select('Orders API', {force: true})
      cy.get('[name="MOLLIE_METHOD_ENABLED_ideal"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_ideal"]').select('Orders API', {force: true})
      cy.get('[name="MOLLIE_METHOD_ENABLED_klarnapaylater"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_ENABLED_klarnasliceit"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_ENABLED_klarnapaynow"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_ENABLED_banktransfer"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_banktransfer"]').select('Orders API', {force: true})
      cy.get('[name="MOLLIE_METHOD_ENABLED_paypal"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_paypal"]').select('Orders API', {force: true})
      cy.get('[name="MOLLIE_METHOD_ENABLED_applepay"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_paypal"]').select('Orders API', {force: true})
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
      cy.mollie_16124_test_faster_login_DE_Orders_Api()
      cy.get('.cart_navigation > .button > span').click()
      cy.get('.cart_navigation > .button > span').click()
      cy.get('.cart_navigation > .button > span').click()
      cy.get('label').click({force: true})
      cy.get('.cart_navigation > .button > span').click({force: true}).wait(1000)
      })
      }
      fasterLoginDE('LoginFoDE')
      cy.visit('https://demo.invertus.eu/clients/mollie16-test/en/home/10-test1.html')
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
      cy.get('[type="submit"]').eq(1).click().wait(1000)
      cy.get('[class="btn btn-default button button-medium"]').click()
      cy.get('[class="button btn btn-default standard-checkout button-medium"]').click()
      cy.get('[name="processAddress"]').click()
      cy.get('label').click({force: true})
      cy.get('.cart_navigation > .button > span').click()
      cy.get('#mollie_link_bancontact').click()
      cy.get(':nth-child(2) > .checkbox > .checkbox__label').click()
      cy.get('.button').click()
      cy.get('[id="mollie-ok"]').should('contain.text','Thank you')
})
it('Bancontact Order BO Shiping, Refunding [Orders API]', () => {
  Cypress.on('uncaught:exception', (err, runnable) => {
    // returning false here prevents Cypress from
    // failing the test
    return false
  })
  var login = (MollieBOLoggingIn) => {
    cy.session(MollieBOLoggingIn,() => {
      cy.mollie_test16_admin()
      cy.login_mollie16_test()
    })
    }
  login('MollieBOLoggingIn')
      cy.visit('https://demo.invertus.eu/clients/mollie16-test/admin1/index.php?controller=AdminOrders')
      cy.get('[class=" odd"]').eq(0).click().wait(3000)
      //Refunding dropdown in React
      cy.get('.btn-group-action > .btn-group > .dropdown-toggle').click()
      cy.get('[role="button"]').eq(0).click()
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
it('iDEAL checkout FO [Orders API]', () => {
      Cypress.on('uncaught:exception', (err, runnable) => {
      // returning false here prevents Cypress from
      // failing the test
      return false
      })
      var fasterLoginDE = (LoginFoDE) => {
      cy.session (LoginFoDE, () => {
      cy.mollie_16124_test_faster_login_DE_Orders_Api()
      cy.get('.cart_navigation > .button > span').click()
      cy.get('.cart_navigation > .button > span').click()
      cy.get('.cart_navigation > .button > span').click()
      cy.get('label').click({force: true})
      cy.get('.cart_navigation > .button > span').click({force: true}).wait(1000)
      })
      }
      fasterLoginDE('LoginFoDE')
      cy.visit('https://demo.invertus.eu/clients/mollie16-test/en/home/10-test1.html')
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
      cy.get('[type="submit"]').eq(1).click().wait(1000)
      cy.get('[class="btn btn-default button button-medium"]').click()
      cy.get('[class="button btn btn-default standard-checkout button-medium"]').click()
      cy.get('[name="processAddress"]').click()
      cy.get('label').click({force: true})
      cy.get('.cart_navigation > .button > span').click()
      cy.get('#mollie_link_ideal').click()
      cy.get('[class="payment-method-list--bordered"]').eq(0).click()
      cy.get(':nth-child(2) > .checkbox > .checkbox__label').click()
      cy.get('.button').click()
      cy.get('[id="mollie-ok"]').should('contain.text','Thank you')
})
it('iDEAL Order BO Shiping, Refunding [Orders API]', () => {
  Cypress.on('uncaught:exception', (err, runnable) => {
    // returning false here prevents Cypress from
    // failing the test
    return false
  })
  var login = (MollieBOLoggingIn) => {
    cy.session(MollieBOLoggingIn,() => {
      cy.mollie_test16_admin()
      cy.login_mollie16_test()
    })
    }
  login('MollieBOLoggingIn')
      cy.visit('https://demo.invertus.eu/clients/mollie16-test/admin1/index.php?controller=AdminOrders')
      cy.get('[class=" odd"]').eq(0).click().wait(3000)
      //Refunding dropdown in React
      cy.get('.btn-group-action > .btn-group > .dropdown-toggle').click()
      cy.get('[role="button"]').eq(0).click()
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
it('Klarna Slice It checkout FO [Orders API]', () => {
      Cypress.on('uncaught:exception', (err, runnable) => {
      // returning false here prevents Cypress from
      // failing the test
      return false
      })
      var fasterLoginDE = (LoginFoDE) => {
      cy.session (LoginFoDE, () => {
      cy.mollie_16124_test_faster_login_DE_Orders_Api()
      cy.get('.cart_navigation > .button > span').click()
      cy.get('.cart_navigation > .button > span').click()
      cy.get('.cart_navigation > .button > span').click()
      cy.get('label').click({force: true})
      cy.get('.cart_navigation > .button > span').click({force: true}).wait(1000)
      })
      }
      fasterLoginDE('LoginFoDE')
      cy.visit('https://demo.invertus.eu/clients/mollie16-test/en/home/10-test1.html')
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
      cy.get('[type="submit"]').eq(1).click().wait(1000)
      cy.get('[class="btn btn-default button button-medium"]').click()
      cy.get('[class="button btn btn-default standard-checkout button-medium"]').click()
      cy.get('[name="processAddress"]').click()
      cy.get('label').click({force: true})
      cy.get('.cart_navigation > .button > span').click()
      cy.get('#mollie_link_klarnasliceit').click()
      cy.get(':nth-child(1) > .checkbox > .checkbox__label').click()
      cy.get('.button').click()
      cy.get('[id="mollie-ok"]').should('contain.text','Thank you')
})
it('Klarna Slice It Order BO Shiping, Refunding [Orders API]', () => {
  Cypress.on('uncaught:exception', (err, runnable) => {
    // returning false here prevents Cypress from
    // failing the test
    return false
  })
  var login = (MollieBOLoggingIn) => {
    cy.session(MollieBOLoggingIn,() => {
      cy.mollie_test16_admin()
      cy.login_mollie16_test()
    })
    }
  login('MollieBOLoggingIn')
      cy.visit('https://demo.invertus.eu/clients/mollie16-test/admin1/index.php?controller=AdminOrders')
      cy.get('[class=" odd"]').eq(0).click().wait(3000)
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
      cy.get('[role="button"]').eq(0).click()
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
      cy.mollie_16124_test_faster_login_DE_Orders_Api()
      cy.get('.cart_navigation > .button > span').click()
      cy.get('.cart_navigation > .button > span').click()
      cy.get('.cart_navigation > .button > span').click()
      cy.get('label').click({force: true})
      cy.get('.cart_navigation > .button > span').click({force: true}).wait(1000)
      })
      }
      fasterLoginDE('LoginFoDE')
      cy.visit('https://demo.invertus.eu/clients/mollie16-test/en/home/10-test1.html')
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
      cy.get('[type="submit"]').eq(1).click().wait(1000)
      cy.get('[class="btn btn-default button button-medium"]').click()
      cy.get('[class="button btn btn-default standard-checkout button-medium"]').click()
      cy.get('[name="processAddress"]').click()
      cy.get('label').click({force: true})
      cy.get('.cart_navigation > .button > span').click()
      cy.get('#mollie_link_klarnapaylater').click()
      cy.get(':nth-child(1) > .checkbox > .checkbox__label').click()
      cy.get('.button').click()
      cy.get('[id="mollie-ok"]').should('contain.text','Thank you')
})
it('Klarna Pay Later Order BO Shiping, Refunding [Orders API]', () => {
  Cypress.on('uncaught:exception', (err, runnable) => {
    // returning false here prevents Cypress from
    // failing the test
    return false
  })
  var login = (MollieBOLoggingIn) => {
    cy.session(MollieBOLoggingIn,() => {
      cy.mollie_test16_admin()
      cy.login_mollie16_test()
    })
    }
  login('MollieBOLoggingIn')
      cy.visit('https://demo.invertus.eu/clients/mollie16-test/admin1/index.php?controller=AdminOrders')
      cy.get('[class=" odd"]').eq(0).click().wait(3000)
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
      cy.get('[role="button"]').eq(0).click()
      cy.get('[class="swal-button swal-button--confirm"]').click()
      cy.get('[class="alert alert-success"]').should('be.visible')
})
it('Klarna Pay Now checkout FO [Orders API]', () => {
      Cypress.on('uncaught:exception', (err, runnable) => {
      // returning false here prevents Cypress from
      // failing the test
      return false
      })
      var fasterLoginDE = (LoginFoDE) => {
      cy.session (LoginFoDE, () => {
      cy.mollie_16124_test_faster_login_DE_Orders_Api()
      cy.get('.cart_navigation > .button > span').click()
      cy.get('.cart_navigation > .button > span').click()
      cy.get('.cart_navigation > .button > span').click()
      cy.get('label').click({force: true})
      cy.get('.cart_navigation > .button > span').click({force: true}).wait(1000)
      })
      }
      fasterLoginDE('LoginFoDE')
      cy.visit('https://demo.invertus.eu/clients/mollie16-test/en/home/10-test1.html')
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
      cy.get('[type="submit"]').eq(1).click().wait(1000)
      cy.get('[class="btn btn-default button button-medium"]').click()
      cy.get('[class="button btn btn-default standard-checkout button-medium"]').click()
      cy.get('[name="processAddress"]').click()
      cy.get('label').click({force: true})
      cy.get('.cart_navigation > .button > span').click()
      cy.get('#mollie_link_klarnapaynow').click()
      cy.get(':nth-child(1) > .checkbox > .checkbox__label').click()
      cy.get('.button').click()
      cy.get('[id="mollie-ok"]').should('contain.text','Thank you')
})
it('Klarna Pay Now Order BO Shiping, Refunding [Orders API]', () => {
  Cypress.on('uncaught:exception', (err, runnable) => {
    // returning false here prevents Cypress from
    // failing the test
    return false
  })
  var login = (MollieBOLoggingIn) => {
    cy.session(MollieBOLoggingIn,() => {
      cy.mollie_test16_admin()
      cy.login_mollie16_test()
    })
    }
  login('MollieBOLoggingIn')
      cy.visit('https://demo.invertus.eu/clients/mollie16-test/admin1/index.php?controller=AdminOrders')
      cy.get('[class=" odd"]').eq(0).click().wait(3000)
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
      cy.get('[role="button"]').eq(0).click()
      cy.get('[class="swal-button swal-button--confirm"]').click()
      cy.get('[class="alert alert-success"]').should('be.visible')
})
it('Credit Cart checkout FO [Orders API]', () => {
      Cypress.on('uncaught:exception', (err, runnable) => {
      // returning false here prevents Cypress from
      // failing the test
      return false
      })
      var fasterLoginDE = (LoginFoDE) => {
      cy.session (LoginFoDE, () => {
      cy.mollie_16124_test_faster_login_DE_Orders_Api()
      cy.get('.cart_navigation > .button > span').click()
      cy.get('.cart_navigation > .button > span').click()
      cy.get('.cart_navigation > .button > span').click()
      cy.get('label').click({force: true})
      cy.get('.cart_navigation > .button > span').click({force: true}).wait(1000)
      })
      }
      fasterLoginDE('LoginFoDE')
      cy.visit('https://demo.invertus.eu/clients/mollie16-test/en/home/10-test1.html')
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
      cy.get('[type="submit"]').eq(1).click().wait(1000)
      cy.get('[class="btn btn-default button button-medium"]').click()
      cy.get('[class="button btn btn-default standard-checkout button-medium"]').click()
      cy.get('[name="processAddress"]').click()
      cy.get('label').click({force: true})
      cy.get('.cart_navigation > .button > span').click()
      cy.get('#mollie_link_creditcard').click()
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

      //Success page UI verification
      cy.get('#mollie-ok').should('include.text','Thank you')
})
it('Credit Card Order BO Shiping, Refunding [Orders API]', () => {
  Cypress.on('uncaught:exception', (err, runnable) => {
    // returning false here prevents Cypress from
    // failing the test
    return false
  })
  var login = (MollieBOLoggingIn) => {
    cy.session(MollieBOLoggingIn,() => {
      cy.mollie_test16_admin()
      cy.login_mollie16_test()
    })
    }
  login('MollieBOLoggingIn')
      cy.visit('https://demo.invertus.eu/clients/mollie16-test/admin1/index.php?controller=AdminOrders')
      cy.get('[class=" odd"]').eq(0).click().wait(3000)
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
      cy.get('[role="button"]').eq(0).click()
      cy.get('[class="swal-button swal-button--confirm"]').click()
      cy.get('[class="alert alert-success"]').should('be.visible')
})
})