/// <reference types="Cypress" />
context('PS17 All Payments Checking [Orders API]', () => {
  beforeEach(() => {
    cy.viewport(1920,1080)
  })
it('Enabling All Payments in BO [Orders API]', () => {
    var login = (MollieBOLoggingIn) => {
    cy.session(MollieBOLoggingIn,() => {
    cy.mollie_1752_test_demo_module_dashboard()
    cy.mollie_1752_test_login()
    })
 	}
login('MollieBOLoggingIn')
      cy.visit('https://demo.invertus.eu/clients/mollie17-test/admin1/index.php?controller=AdminMollieModule')
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
      cy.mollie_1752_test_faster_login_DE_Orders_Api()
      })
      }
      fasterLoginDE('LoginFoDE')
      cy.visit('https://demo.invertus.eu/clients/mollie17-test/en/home/21-testproduct1.html')
      cy.get('.add > .btn').click()
      cy.get('.cart-content-btn > .btn-primary').click()
      cy.get('.text-sm-center > .btn').click()
      cy.contains('Germany').click()
      cy.get('.clearfix > .btn').click()
      cy.get('#js-delivery > .continue').click()
      cy.contains('Bancontact').click({force:true})
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

      //Success page UI verification
      cy.get('.h1').should('include.text','Your order is confirmed')
      cy.get('#order-details > ul > :nth-child(2)').should('include.text','Bancontact')
})
it('Bancontact Order BO Shiping, Refunding [Orders API]', () => {
  Cypress.on('uncaught:exception', (err, runnable) => {
    // returning false here prevents Cypress from
    // failing the test
    return false
  })
  var login = (MollieBOLoggingIn) => {
    cy.session(MollieBOLoggingIn,() => {
    cy.mollie_1752_test_demo_module_dashboard()
    cy.mollie_1752_test_login()
    })
    }
  login('MollieBOLoggingIn')
      cy.visit('https://demo.invertus.eu/clients/mollie17-test/admin1/index.php?controller=AdminOrders')
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
      cy.mollie_1752_test_faster_login_DE_Orders_Api()
      })
      }
      fasterLoginDE('LoginFoDE')
      cy.visit('https://demo.invertus.eu/clients/mollie17-test/en/home/21-testproduct1.html')
      cy.get('.add > .btn').click()
      cy.get('.cart-content-btn > .btn-primary').click()
      cy.get('.text-sm-center > .btn').click()
      cy.contains('Germany').click()
      cy.get('.clearfix > .btn').click()
      cy.get('#js-delivery > .continue').click()
      cy.contains('iDEAL').click({force:true})
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

      //Success page UI verification
      cy.get('.h1').should('include.text','Your order is confirmed')
      cy.get('#order-details > ul > :nth-child(2)').should('include.text','iDEAL')
})
it('iDEAL Order BO Shiping, Refunding [Orders API]', () => {
  Cypress.on('uncaught:exception', (err, runnable) => {
    // returning false here prevents Cypress from
    // failing the test
    return false
  })
  var login = (MollieBOLoggingIn) => {
    cy.session(MollieBOLoggingIn,() => {
    cy.mollie_1752_test_demo_module_dashboard()
    cy.mollie_1752_test_login()
    })
    }
  login('MollieBOLoggingIn')
      cy.visit('https://demo.invertus.eu/clients/mollie17-test/admin1/index.php?controller=AdminOrders')
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
      cy.mollie_1752_test_faster_login_DE_Orders_Api()
      })
      }
      fasterLoginDE('LoginFoDE')
      cy.visit('https://demo.invertus.eu/clients/mollie17-test/en/home/21-testproduct1.html')
      cy.get('.add > .btn').click()
      cy.get('.cart-content-btn > .btn-primary').click()
      cy.get('.text-sm-center > .btn').click()
      cy.contains('Germany').click()
      cy.get('.clearfix > .btn').click()
      cy.get('#js-delivery > .continue').click()
      cy.contains('Slice it.').click({force:true})
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

      //Success page UI verification
      cy.get('.h1').should('include.text','Your order is confirmed')
      cy.get('#order-details > ul > :nth-child(2)').should('include.text','Slice it.')
})
it('Klarna Slice It Order BO Shiping, Refunding [Orders API]', () => {
  Cypress.on('uncaught:exception', (err, runnable) => {
    // returning false here prevents Cypress from
    // failing the test
    return false
  })
  var login = (MollieBOLoggingIn) => {
    cy.session(MollieBOLoggingIn,() => {
    cy.mollie_1752_test_demo_module_dashboard()
    cy.mollie_1752_test_login()
    })
    }
  login('MollieBOLoggingIn')
      cy.visit('https://demo.invertus.eu/clients/mollie17-test/admin1/index.php?controller=AdminOrders')
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
      cy.mollie_1752_test_faster_login_DE_Orders_Api()
      })
      }
      fasterLoginDE('LoginFoDE')
      cy.visit('https://demo.invertus.eu/clients/mollie17-test/en/home/21-testproduct1.html')
      cy.get('.add > .btn').click()
      cy.get('.cart-content-btn > .btn-primary').click()
      cy.get('.text-sm-center > .btn').click()
      cy.contains('Germany').click()
      cy.get('.clearfix > .btn').click()
      cy.get('#js-delivery > .continue').click()
      cy.contains('Pay later').click({force:true})
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

      //Success page UI verification
      cy.get('.h1').should('include.text','Your order is confirmed')
      cy.get('#order-details > ul > :nth-child(2)').should('include.text','Pay later')
})
it('Klarna Pay Later Order BO Shiping, Refunding [Orders API]', () => {
  Cypress.on('uncaught:exception', (err, runnable) => {
    // returning false here prevents Cypress from
    // failing the test
    return false
  })
  var login = (MollieBOLoggingIn) => {
    cy.session(MollieBOLoggingIn,() => {
    cy.mollie_1752_test_demo_module_dashboard()
    cy.mollie_1752_test_login()
    })
    }
  login('MollieBOLoggingIn')
      cy.visit('https://demo.invertus.eu/clients/mollie17-test/admin1/index.php?controller=AdminOrders')
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
it('Credit card checkout FO [Orders API]', () => {
      Cypress.on('uncaught:exception', (err, runnable) => {
      // returning false here prevents Cypress from
      // failing the test
      return false
      })
      var fasterLoginDE = (LoginFoDE) => {
      cy.session (LoginFoDE, () => {
      cy.mollie_1752_test_faster_login_DE_Orders_Api()
      })
      }
      fasterLoginDE('LoginFoDE')
      cy.visit('https://demo.invertus.eu/clients/mollie17-test/en/home/21-testproduct1.html')
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
      //Success page UI verification
      cy.get('.h1').should('include.text','Your order is confirmed')
      cy.get('#order-details > ul > :nth-child(2)').should('include.text','Pay later')
})
it('Credit card Order BO Shiping, Refunding [Orders API]', () => {
  Cypress.on('uncaught:exception', (err, runnable) => {
    // returning false here prevents Cypress from
    // failing the test
    return false
  })
  var login = (MollieBOLoggingIn) => {
    cy.session(MollieBOLoggingIn,() => {
    cy.mollie_1752_test_demo_module_dashboard()
    cy.mollie_1752_test_login()
    })
    }
  login('MollieBOLoggingIn')
      cy.visit('https://demo.invertus.eu/clients/mollie17-test/admin1/index.php?controller=AdminOrders')
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