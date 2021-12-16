/// <reference types="Cypress" />
context('PS16 All Payments Checking [Orders API]', () => {
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
      cy.ConfOrdersAPI()
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
      cy.get('.btn-group-action > .btn-group > .dropdown-toggle').eq(0).click()
      cy.get('[role="button"]').eq(0).click()
      cy.get('[class="swal-button swal-button--confirm"]').click()
      cy.get('[class="alert alert-success"]').should('be.visible')
      //Shipping button in React
      cy.get('.btn-group > [title=""]').eq(0).click()
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
      cy.get('.btn-group-action > .btn-group > .dropdown-toggle').eq(0).click()
      cy.get('[role="button"]').eq(0).click()
      cy.get('[class="swal-button swal-button--confirm"]').click()
      cy.get('[class="alert alert-success"]').should('be.visible')
      //Shipping button in React
      cy.get('.btn-group > [title=""]').eq(0).click()
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
      cy.get('.btn-group > [title=""]').eq(0).click()
      cy.get('[class="swal-button swal-button--confirm"]').click()
      cy.get('.swal-modal').should('exist')
      cy.get('#input-carrier').type('FedEx',{delay:0})
      cy.get('#input-code').type('123456',{delay:0})
      cy.get('#input-url').type('https://www.invertus.eu',{delay:0})
      cy.get(':nth-child(2) > .swal-button').click()
      cy.get('#mollie_order > :nth-child(1) > .alert').contains('Shipment was made successfully!')
      cy.get('[class="alert alert-success"]').should('be.visible')
      //Refunding dropdown in React
      cy.get('.btn-group-action > .btn-group > .dropdown-toggle').eq(0).click()
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
      cy.get('.btn-group > [title=""]').eq(0).click()
      cy.get('[class="swal-button swal-button--confirm"]').click()
      cy.get('.swal-modal').should('exist')
      cy.get('#input-carrier').type('FedEx',{delay:0})
      cy.get('#input-code').type('123456',{delay:0})
      cy.get('#input-url').type('https://www.invertus.eu',{delay:0})
      cy.get(':nth-child(2) > .swal-button').click()
      cy.get('#mollie_order > :nth-child(1) > .alert').contains('Shipment was made successfully!')
      cy.get('[class="alert alert-success"]').should('be.visible')
      //Refunding dropdown in React
      cy.get('.btn-group-action > .btn-group > .dropdown-toggle').eq(0).click()
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
      cy.get('.btn-group > [title=""]').eq(0).click()
      cy.get('[class="swal-button swal-button--confirm"]').click()
      cy.get('.swal-modal').should('exist')
      cy.get('#input-carrier').type('FedEx',{delay:0})
      cy.get('#input-code').type('123456',{delay:0})
      cy.get('#input-url').type('https://www.invertus.eu',{delay:0})
      cy.get(':nth-child(2) > .swal-button').click()
      cy.get('#mollie_order > :nth-child(1) > .alert').contains('Shipment was made successfully!')
      cy.get('[class="alert alert-success"]').should('be.visible')
      //Refunding dropdown in React
      cy.get('.btn-group-action > .btn-group > .dropdown-toggle').eq(0).click()
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
      getBody().find('#expiryDate').type('12222')
      })
      cy.enter('[data-testid=mollie-container--verificationCode] > iframe').then(getBody => {
      getBody().find('#verificationCode').type('2222')
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
      cy.get('.btn-group > [title=""]').eq(0).click()
      cy.get('[class="swal-button swal-button--confirm"]').click()
      cy.get('.swal-modal').should('exist')
      cy.get('#input-carrier').type('FedEx',{delay:0})
      cy.get('#input-code').type('123456',{delay:0})
      cy.get('#input-url').type('https://www.invertus.eu',{delay:0})
      cy.get(':nth-child(2) > .swal-button').click()
      cy.get('#mollie_order > :nth-child(1) > .alert').contains('Shipment was made successfully!')
      cy.get('[class="alert alert-success"]').should('be.visible')
      //Refunding dropdown in React
      cy.get('.btn-group-action > .btn-group > .dropdown-toggle').eq(0).click()
      cy.get('[role="button"]').eq(0).click()
      cy.get('[class="swal-button swal-button--confirm"]').click()
      cy.get('[class="alert alert-success"]').should('be.visible')
})
})