/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 *
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 *
 * @see        https://github.com/mollie/PrestaShop
 */
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 *
 * @see        https://github.com/mollie/PrestaShop
 * @codingStandardsIgnoreStart
 */
// ***********************************************************
// This example support/index.js is processed and
// loaded automatically before your test files.
//
// This is a great place to put global configuration and
// behavior that modifies Cypress.
//
// You can change the location of this file or turn off
// automatically serving support files with the
// 'supportFile' configuration option.
//
// You can read more here:
// https://on.cypress.io/configuration
// ***********************************************************
import 'cypress-iframe';
// or
require('cypress-iframe');
// Import commands.js using ES2015 syntax:
import './commands'

// Alternatively you can use CommonJS syntax:
// require('./commands')

Cypress.on('uncaught:exception', (err, runnable) => {
  // returning false here prevents Cypress from
  // failing the test
  return false
})
Cypress.Keyboard.defaults({
  keystrokeDelay: 0,
})
// Cypress.on('window:before:load', (win) => {
//         Cypress.log({
//             name: 'console.log',
//             message: 'wrap on console.log',
//         });

//         // pass through cypress log so we can see log inside command execution order
//         win.console.log = (...args) => {
//             Cypress.log({
//                 name: 'console.log',
//                 message: args,
//             });
//         };
//     });

//     Cypress.on('log:added', (options) => {
//         if (options.instrument === 'command') {
//             // eslint-disable-next-line no-console
//             console.log(
//                 `${(options.displayName || options.name || '').toUpperCase()} ${
//                     options.message
//                 }`,
//             );
//         }
//     });
// Cypress.on('window:before:load', (win) => {
//   cy.spy(win.console, 'error');
//   cy.spy(win.console, 'warn');
// });

// afterEach(() => {
//   cy.window().then((win) => {
//     expect(win.console.error).to.have.callCount(0);
//     expect(win.console.warn).to.have.callCount(0);
//   });
// });