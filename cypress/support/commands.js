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
//import 'cypress-file-upload';
import 'cypress-iframe';
// or
//require('cypress-iframe');

//const compareSnapshotCommand = require('cypress-visual-regression/dist/command');
//compareSnapshotCommand({
//  capture: 'fullPage'
//});
// ***********************************************
// This example commands.js shows you how to
// create various custom commands and overwrite
// existing commands.
//
// For more comprehensive examples of custom
// commands please read more here:
// https://on.cypress.io/custom-commands
// ***********************************************
//
//
// -- This is a parent command --
// Cypress.Commands.add("login", (email, password) => { ... })
Cypress.Commands.add("ConfOrdersAPI", () => {
      cy.get('[for="MOLLIE_IFRAME_on"]').click()
      cy.get('#MOLLIE_PROFILE_ID').clear({force: true}).type((Cypress.env('MOLLIE_TEST_PROFILE_ID')),{delay: 0, log: false})
      //giropay
      cy.get('[name="MOLLIE_METHOD_ENABLED_giropay"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_giropay"]').select('Orders API', {force: true})
      cy.get('[name="MOLLIE_METHOD_DESCRIPTION_giropay"]').clear({force: true}).type('Lorem Ipsum dummy text 123 !@#$%^&*', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_TYPE_giropay"]').select('3', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_giropay"]').clear({force: true}).type('11', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_PERCENTAGE_giropay"]').clear({force: true}).type('22', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_LIMIT_giropay"]').clear({force: true}).type('33', {force: true})
      //eps
      cy.get('[name="MOLLIE_METHOD_ENABLED_eps"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_eps"]').select('Orders API', {force: true})
      cy.get('[name="MOLLIE_METHOD_DESCRIPTION_eps"]').clear({force: true}).type('Lorem Ipsum dummy text 123 !@#$%^&*', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_TYPE_eps"]').select('3', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_eps"]').clear({force: true}).type('11', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_PERCENTAGE_eps"]').clear({force: true}).type('22', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_LIMIT_eps"]').clear({force: true}).type('33', {force: true})
      //przelewy24
      cy.get('[name="MOLLIE_METHOD_ENABLED_przelewy24"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_przelewy24"]').select('Orders API', {force: true})
      cy.get('[name="MOLLIE_METHOD_DESCRIPTION_przelewy24"]').clear({force: true}).type('Lorem Ipsum dummy text 123 !@#$%^&*', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_TYPE_przelewy24"]').select('3', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_przelewy24"]').clear({force: true}).type('11', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_PERCENTAGE_przelewy24"]').clear({force: true}).type('22', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_LIMIT_przelewy24"]').clear({force: true}).type('33', {force: true})
      //kbc
      cy.get('[name="MOLLIE_METHOD_ENABLED_kbc"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_kbc"]').select('Orders API', {force: true})
      cy.get('[name="MOLLIE_METHOD_DESCRIPTION_kbc"]').clear({force: true}).type('Lorem Ipsum dummy text 123 !@#$%^&*', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_TYPE_kbc"]').select('3', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_kbc"]').clear({force: true}).type('11', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_PERCENTAGE_kbc"]').clear({force: true}).type('22', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_LIMIT_kbc"]').clear({force: true}).type('33', {force: true})
      //voucher
      cy.get('[name="MOLLIE_METHOD_ENABLED_voucher"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_voucher"]').select('Orders API', {force: true})
      cy.get('[name="MOLLIE_METHOD_DESCRIPTION_voucher"]').clear({force: true}).type('Lorem Ipsum dummy text 123 !@#$%^&*', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_TYPE_voucher"]').select('3', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_voucher"]').clear({force: true}).type('11', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_PERCENTAGE_voucher"]').clear({force: true}).type('22', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_LIMIT_voucher"]').clear({force: true}).type('33', {force: true})
      //belfius
      cy.get('[name="MOLLIE_METHOD_ENABLED_belfius"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_belfius"]').select('Orders API', {force: true})
      cy.get('[name="MOLLIE_METHOD_DESCRIPTION_przelewy24"]').clear({force: true}).type('Lorem Ipsum dummy text 123 !@#$%^&*', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_TYPE_belfius"]').select('3', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_belfius"]').clear({force: true}).type('11', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_PERCENTAGE_belfius"]').clear({force: true}).type('22', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_LIMIT_belfius"]').clear({force: true}).type('33', {force: true})
      //bancontact
      cy.get('[name="MOLLIE_METHOD_ENABLED_bancontact"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_bancontact"]').select('Orders API', {force: true})
      cy.get('[name="MOLLIE_METHOD_DESCRIPTION_bancontact"]').clear({force: true}).type('Lorem Ipsum dummy text 123 !@#$%^&*', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_TYPE_bancontact"]').select('3', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_bancontact"]').clear({force: true}).type('11', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_PERCENTAGE_bancontact"]').clear({force: true}).type('22', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_LIMIT_bancontact"]').clear({force: true}).type('33', {force: true})
      //sofort
      cy.get('[name="MOLLIE_METHOD_ENABLED_sofort"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_sofort"]').select('Orders API', {force: true})
      cy.get('[name="MOLLIE_METHOD_DESCRIPTION_sofort"]').clear({force: true}).type('Lorem Ipsum dummy text 123 !@#$%^&*', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_TYPE_sofort"]').select('3', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_sofort"]').clear({force: true}).type('11', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_PERCENTAGE_sofort"]').clear({force: true}).type('22', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_LIMIT_sofort"]').clear({force: true}).type('33', {force: true})
      //creditcard
      cy.get('[name="MOLLIE_METHOD_ENABLED_creditcard"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_creditcard"]').select('Orders API', {force: true})
      cy.get('[name="MOLLIE_METHOD_DESCRIPTION_creditcard"]').clear({force: true}).type('Lorem Ipsum dummy text 123 !@#$%^&*', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_TYPE_creditcard"]').select('3', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_creditcard"]').clear({force: true}).type('11', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_PERCENTAGE_creditcard"]').clear({force: true}).type('22', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_LIMIT_creditcard"]').clear({force: true}).type('33', {force: true})
      //ideal
      cy.get('[name="MOLLIE_METHOD_ENABLED_ideal"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_ideal"]').select('Orders API', {force: true})
      cy.get('[name="MOLLIE_METHOD_DESCRIPTION_ideal"]').clear({force: true}).type('Lorem Ipsum dummy text 123 !@#$%^&*', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_TYPE_ideal"]').select('3', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_ideal"]').clear({force: true}).type('11', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_PERCENTAGE_ideal"]').clear({force: true}).type('22', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_LIMIT_ideal"]').clear({force: true}).type('33', {force: true})
      //klarnapaylater
      cy.get('[name="MOLLIE_METHOD_ENABLED_klarnapaylater"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_DESCRIPTION_klarnapaylater"]').clear({force: true}).type('Lorem Ipsum dummy text 123 !@#$%^&*', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_TYPE_klarnapaylater"]').select('3', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_klarnapaylater"]').clear({force: true}).type('11', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_PERCENTAGE_klarnapaylater"]').clear({force: true}).type('22', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_LIMIT_klarnapaylater"]').clear({force: true}).type('33', {force: true})
      //klarnasliceit
      cy.get('[name="MOLLIE_METHOD_ENABLED_klarnasliceit"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_DESCRIPTION_klarnasliceit"]').clear({force: true}).type('Lorem Ipsum dummy text 123 !@#$%^&*', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_TYPE_klarnasliceit"]').select('3', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_klarnasliceit"]').clear({force: true}).type('11', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_PERCENTAGE_klarnasliceit"]').clear({force: true}).type('22', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_LIMIT_klarnasliceit"]').clear({force: true}).type('33', {force: true})
      //klarnapaynow
      cy.get('[name="MOLLIE_METHOD_ENABLED_klarnapaynow"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_DESCRIPTION_klarnapaynow"]').clear({force: true}).type('Lorem Ipsum dummy text 123 !@#$%^&*', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_TYPE_klarnapaynow"]').select('3', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_klarnapaynow"]').clear({force: true}).type('11', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_PERCENTAGE_klarnapaynow"]').clear({force: true}).type('22', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_LIMIT_klarnapaynow"]').clear({force: true}).type('33', {force: true})
      //banktransfer
      cy.get('[name="MOLLIE_METHOD_ENABLED_banktransfer"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_banktransfer"]').select('Orders API', {force: true})
      cy.get('[name="MOLLIE_METHOD_DESCRIPTION_banktransfer"]').clear({force: true}).type('Lorem Ipsum dummy text 123 !@#$%^&*', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_TYPE_banktransfer"]').select('3', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_banktransfer"]').clear({force: true}).type('11', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_PERCENTAGE_banktransfer"]').clear({force: true}).type('22', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_LIMIT_banktransfer"]').clear({force: true}).type('33', {force: true})
      //paypal
      cy.get('[name="MOLLIE_METHOD_ENABLED_paypal"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_paypal"]').select('Orders API', {force: true})
      cy.get('[name="MOLLIE_METHOD_DESCRIPTION_paypal"]').clear({force: true}).type('Lorem Ipsum dummy text 123 !@#$%^&*', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_TYPE_paypal"]').select('3', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_paypal"]').clear({force: true}).type('11', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_PERCENTAGE_paypal"]').clear({force: true}).type('22', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_LIMIT_paypal"]').clear({force: true}).type('33', {force: true})
      //applepay
      cy.get('[name="MOLLIE_METHOD_ENABLED_applepay"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_DESCRIPTION_applepay"]').clear({force: true}).type('Lorem Ipsum dummy text 123 !@#$%^&*', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_TYPE_applepay"]').select('3', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_applepay"]').clear({force: true}).type('11', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_PERCENTAGE_applepay"]').clear({force: true}).type('22', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_LIMIT_applepay"]').clear({force: true}).type('33', {force: true})
      //in3
      cy.get('[name="MOLLIE_METHOD_ENABLED_in3"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_in3"]').select('Orders API', {force: true})
      cy.get('[name="MOLLIE_METHOD_DESCRIPTION_in3"]').clear({force: true}).type('Lorem Ipsum dummy text 123 !@#$%^&*', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_TYPE_in3"]').select('3', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_in3"]').clear({force: true}).type('11', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_PERCENTAGE_in3"]').clear({force: true}).type('22', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_LIMIT_in3"]').clear({force: true}).type('33', {force: true})
 })
Cypress.Commands.add("ConfPaymentsAPI", () => {
      //giropay
      cy.get('[name="MOLLIE_METHOD_ENABLED_giropay"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_giropay"]').select('Payments API', {force: true})
      //eps
      cy.get('[name="MOLLIE_METHOD_ENABLED_eps"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_eps"]').select('Payments API', {force: true})
      //przelewy24
      cy.get('[name="MOLLIE_METHOD_ENABLED_przelewy24"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_przelewy24"]').select('Payments API', {force: true})
      //kbc
      cy.get('[name="MOLLIE_METHOD_ENABLED_kbc"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_kbc"]').select('Payments API', {force: true})
      //voucher
      cy.get('[name="MOLLIE_METHOD_ENABLED_voucher"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_DESCRIPTION_voucher"]').clear({force: true}).type('Lorem Ipsum 123 !@#$%^&*', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_TYPE_voucher"]').select('3', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_voucher"]').clear({force: true}).type('11', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_PERCENTAGE_voucher"]').clear({force: true}).type('22', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_LIMIT_voucher"]').clear({force: true}).type('33', {force: true})
      //belfius
      cy.get('[name="MOLLIE_METHOD_ENABLED_belfius"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_belfius"]').select('Payments API', {force: true})
      //bancontact
      cy.get('[name="MOLLIE_METHOD_ENABLED_bancontact"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_bancontact"]').select('Payments API', {force: true})
      //sofort
      cy.get('[name="MOLLIE_METHOD_ENABLED_sofort"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_sofort"]').select('Payments API', {force: true})
      //creditcard
      cy.get('[name="MOLLIE_METHOD_ENABLED_creditcard"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_creditcard"]').select('Payments API', {force: true})
      //ideal
      cy.get('[name="MOLLIE_METHOD_ENABLED_ideal"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_ideal"]').select('Payments API', {force: true})
      //klarnapaylater
      cy.get('[name="MOLLIE_METHOD_ENABLED_klarnapaylater"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_DESCRIPTION_klarnapaylater"]').clear({force: true}).type('Lorem Ipsum 123 !@#$%^&*', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_TYPE_klarnapaylater"]').select('3', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_klarnapaylater"]').clear({force: true}).type('11', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_PERCENTAGE_klarnapaylater"]').clear({force: true}).type('22', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_LIMIT_klarnapaylater"]').clear({force: true}).type('33', {force: true})
      //klarnasliceit
      cy.get('[name="MOLLIE_METHOD_ENABLED_klarnasliceit"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_DESCRIPTION_klarnasliceit"]').clear({force: true}).type('Lorem Ipsum 123 !@#$%^&*', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_TYPE_klarnasliceit"]').select('3', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_klarnasliceit"]').clear({force: true}).type('11', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_PERCENTAGE_klarnasliceit"]').clear({force: true}).type('22', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_LIMIT_klarnasliceit"]').clear({force: true}).type('33', {force: true})
      //klarnapaynow
      cy.get('[name="MOLLIE_METHOD_ENABLED_klarnapaynow"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_DESCRIPTION_klarnapaynow"]').clear({force: true}).type('Lorem Ipsum 123 !@#$%^&*', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_TYPE_klarnapaynow"]').select('3', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_klarnapaynow"]').clear({force: true}).type('11', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_PERCENTAGE_klarnapaynow"]').clear({force: true}).type('22', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_LIMIT_klarnapaynow"]').clear({force: true}).type('33', {force: true})
      //banktransfer
      cy.get('[name="MOLLIE_METHOD_ENABLED_banktransfer"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_banktransfer"]').select('Payments API', {force: true})
      //paypal
      cy.get('[name="MOLLIE_METHOD_ENABLED_paypal"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_paypal"]').select('Payments API', {force: true})
      //applepay
      cy.get('[name="MOLLIE_METHOD_ENABLED_applepay"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_DESCRIPTION_applepay"]').clear({force: true}).type('Lorem Ipsum 123 !@#$%^&*', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_TYPE_applepay"]').select('3', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_applepay"]').clear({force: true}).type('11', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_PERCENTAGE_applepay"]').clear({force: true}).type('22', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_LIMIT_applepay"]').clear({force: true}).type('33', {force: true})
})
Cypress.Commands.add("ConfOrdersAPI1784", () => {
      cy.get('#MOLLIE_IFRAME_on').click({force:true})
      //giropay
      cy.get('[name="MOLLIE_METHOD_ENABLED_giropay"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_giropay"]').select('Orders API', {force: true})
      cy.get('[name="MOLLIE_METHOD_DESCRIPTION_giropay"]').clear({force: true}).type('Lorem Ipsum dummy text 123 !@#$%^&*', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_TYPE_giropay"]').select('3', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_giropay"]').clear({force: true}).type('11', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_PERCENTAGE_giropay"]').clear({force: true}).type('22', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_LIMIT_giropay"]').clear({force: true}).type('33', {force: true})
      //eps
      cy.get('[name="MOLLIE_METHOD_ENABLED_eps"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_eps"]').select('Orders API', {force: true})
      cy.get('[name="MOLLIE_METHOD_DESCRIPTION_eps"]').clear({force: true}).type('Lorem Ipsum dummy text 123 !@#$%^&*', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_TYPE_eps"]').select('3', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_eps"]').clear({force: true}).type('11', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_PERCENTAGE_eps"]').clear({force: true}).type('22', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_LIMIT_eps"]').clear({force: true}).type('33', {force: true})
      //przelewy24
      cy.get('[name="MOLLIE_METHOD_ENABLED_przelewy24"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_przelewy24"]').select('Orders API', {force: true})
      cy.get('[name="MOLLIE_METHOD_DESCRIPTION_przelewy24"]').clear({force: true}).type('Lorem Ipsum dummy text 123 !@#$%^&*', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_TYPE_przelewy24"]').select('3', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_przelewy24"]').clear({force: true}).type('11', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_PERCENTAGE_przelewy24"]').clear({force: true}).type('22', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_LIMIT_przelewy24"]').clear({force: true}).type('33', {force: true})
      //kbc
      cy.get('[name="MOLLIE_METHOD_ENABLED_kbc"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_kbc"]').select('Orders API', {force: true})
      cy.get('[name="MOLLIE_METHOD_DESCRIPTION_kbc"]').clear({force: true}).type('Lorem Ipsum dummy text 123 !@#$%^&*', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_TYPE_kbc"]').select('3', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_kbc"]').clear({force: true}).type('11', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_PERCENTAGE_kbc"]').clear({force: true}).type('22', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_LIMIT_kbc"]').clear({force: true}).type('33', {force: true})
      //voucher
      cy.get('[name="MOLLIE_METHOD_ENABLED_voucher"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_voucher"]').select('Orders API', {force: true})
      cy.get('[name="MOLLIE_METHOD_DESCRIPTION_voucher"]').clear({force: true}).type('Lorem Ipsum dummy text 123 !@#$%^&*', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_TYPE_voucher"]').select('3', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_voucher"]').clear({force: true}).type('11', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_PERCENTAGE_voucher"]').clear({force: true}).type('22', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_LIMIT_voucher"]').clear({force: true}).type('33', {force: true})
      //belfius
      cy.get('[name="MOLLIE_METHOD_ENABLED_belfius"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_belfius"]').select('Orders API', {force: true})
      cy.get('[name="MOLLIE_METHOD_DESCRIPTION_przelewy24"]').clear({force: true}).type('Lorem Ipsum dummy text 123 !@#$%^&*', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_TYPE_belfius"]').select('3', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_belfius"]').clear({force: true}).type('11', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_PERCENTAGE_belfius"]').clear({force: true}).type('22', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_LIMIT_belfius"]').clear({force: true}).type('33', {force: true})
      //bancontact
      cy.get('[name="MOLLIE_METHOD_ENABLED_bancontact"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_bancontact"]').select('Orders API', {force: true})
      cy.get('[name="MOLLIE_METHOD_DESCRIPTION_bancontact"]').clear({force: true}).type('Lorem Ipsum dummy text 123 !@#$%^&*', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_TYPE_bancontact"]').select('3', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_bancontact"]').clear({force: true}).type('11', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_PERCENTAGE_bancontact"]').clear({force: true}).type('22', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_LIMIT_bancontact"]').clear({force: true}).type('33', {force: true})
      //sofort
      cy.get('[name="MOLLIE_METHOD_ENABLED_sofort"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_sofort"]').select('Orders API', {force: true})
      cy.get('[name="MOLLIE_METHOD_DESCRIPTION_sofort"]').clear({force: true}).type('Lorem Ipsum dummy text 123 !@#$%^&*', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_TYPE_sofort"]').select('3', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_sofort"]').clear({force: true}).type('11', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_PERCENTAGE_sofort"]').clear({force: true}).type('22', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_LIMIT_sofort"]').clear({force: true}).type('33', {force: true})
      //creditcard
      cy.get('[name="MOLLIE_METHOD_ENABLED_creditcard"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_creditcard"]').select('Orders API', {force: true})
      cy.get('[name="MOLLIE_METHOD_DESCRIPTION_creditcard"]').clear({force: true}).type('Lorem Ipsum dummy text 123 !@#$%^&*', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_TYPE_creditcard"]').select('3', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_creditcard"]').clear({force: true}).type('11', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_PERCENTAGE_creditcard"]').clear({force: true}).type('22', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_LIMIT_creditcard"]').clear({force: true}).type('33', {force: true})
      //ideal
      cy.get('[name="MOLLIE_METHOD_ENABLED_ideal"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_ideal"]').select('Orders API', {force: true})
      cy.get('[name="MOLLIE_METHOD_DESCRIPTION_ideal"]').clear({force: true}).type('Lorem Ipsum dummy text 123 !@#$%^&*', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_TYPE_ideal"]').select('3', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_ideal"]').clear({force: true}).type('11', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_PERCENTAGE_ideal"]').clear({force: true}).type('22', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_LIMIT_ideal"]').clear({force: true}).type('33', {force: true})
      //klarnapaylater
      cy.get('[name="MOLLIE_METHOD_ENABLED_klarnapaylater"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_DESCRIPTION_klarnapaylater"]').clear({force: true}).type('Lorem Ipsum dummy text 123 !@#$%^&*', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_TYPE_klarnapaylater"]').select('3', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_klarnapaylater"]').clear({force: true}).type('11', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_PERCENTAGE_klarnapaylater"]').clear({force: true}).type('22', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_LIMIT_klarnapaylater"]').clear({force: true}).type('33', {force: true})
      //klarnasliceit
      cy.get('[name="MOLLIE_METHOD_ENABLED_klarnasliceit"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_DESCRIPTION_klarnasliceit"]').clear({force: true}).type('Lorem Ipsum dummy text 123 !@#$%^&*', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_TYPE_klarnasliceit"]').select('3', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_klarnasliceit"]').clear({force: true}).type('11', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_PERCENTAGE_klarnasliceit"]').clear({force: true}).type('22', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_LIMIT_klarnasliceit"]').clear({force: true}).type('33', {force: true})
      //klarnapaynow
      cy.get('[name="MOLLIE_METHOD_ENABLED_klarnapaynow"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_DESCRIPTION_klarnapaynow"]').clear({force: true}).type('Lorem Ipsum dummy text 123 !@#$%^&*', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_TYPE_klarnapaynow"]').select('3', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_klarnapaynow"]').clear({force: true}).type('11', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_PERCENTAGE_klarnapaynow"]').clear({force: true}).type('22', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_LIMIT_klarnapaynow"]').clear({force: true}).type('33', {force: true})
      //banktransfer
      cy.get('[name="MOLLIE_METHOD_ENABLED_banktransfer"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_banktransfer"]').select('Orders API', {force: true})
      cy.get('[name="MOLLIE_METHOD_DESCRIPTION_banktransfer"]').clear({force: true}).type('Lorem Ipsum dummy text 123 !@#$%^&*', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_TYPE_banktransfer"]').select('3', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_banktransfer"]').clear({force: true}).type('11', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_PERCENTAGE_banktransfer"]').clear({force: true}).type('22', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_LIMIT_banktransfer"]').clear({force: true}).type('33', {force: true})
      //paypal
      cy.get('[name="MOLLIE_METHOD_ENABLED_paypal"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_paypal"]').select('Orders API', {force: true})
      cy.get('[name="MOLLIE_METHOD_DESCRIPTION_paypal"]').clear({force: true}).type('Lorem Ipsum dummy text 123 !@#$%^&*', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_TYPE_paypal"]').select('3', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_paypal"]').clear({force: true}).type('11', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_PERCENTAGE_paypal"]').clear({force: true}).type('22', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_LIMIT_paypal"]').clear({force: true}).type('33', {force: true})
      //applepay
      cy.get('[name="MOLLIE_METHOD_ENABLED_applepay"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_DESCRIPTION_applepay"]').clear({force: true}).type('Lorem Ipsum dummy text 123 !@#$%^&*', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_TYPE_applepay"]').select('3', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_applepay"]').clear({force: true}).type('11', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_PERCENTAGE_applepay"]').clear({force: true}).type('22', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_LIMIT_applepay"]').clear({force: true}).type('33', {force: true})
      //in3
      cy.get('[name="MOLLIE_METHOD_ENABLED_in3"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_in3"]').select('Orders API', {force: true})
      cy.get('[name="MOLLIE_METHOD_DESCRIPTION_in3"]').clear({force: true}).type('Lorem Ipsum dummy text 123 !@#$%^&*', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_TYPE_in3"]').select('3', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_in3"]').clear({force: true}).type('11', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_PERCENTAGE_in3"]').clear({force: true}).type('22', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_LIMIT_in3"]').clear({force: true}).type('33', {force: true})
      //Gift card
      // cy.get('[name="MOLLIE_METHOD_ENABLED_giftcard"]').select('Yes', {force: true})
      // cy.get('[name="MOLLIE_METHOD_API_giftcard"]').select('Orders API', {force: true})
      // cy.get('[name="MOLLIE_METHOD_DESCRIPTION_giftcard"]').clear({force: true}).type('Lorem Ipsum dummy text 123 !@#$%^&*', {force: true})
      // cy.get('[name="MOLLIE_METHOD_SURCHARGE_TYPE_giftcard"]').select('3', {force: true})
      // cy.get('[name="MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_giftcard"]').clear({force: true}).type('11', {force: true})
      // cy.get('[name="MOLLIE_METHOD_SURCHARGE_PERCENTAGE_giftcard"]').clear({force: true}).type('22', {force: true})
      // cy.get('[name="MOLLIE_METHOD_SURCHARGE_LIMIT_giftcard"]').clear({force: true}).type('33', {force: true})
  })
Cypress.Commands.add("ConfPaymentsAPI1784", () => {
      //Gift card
      // cy.get('[name="MOLLIE_METHOD_ENABLED_giftcard"]').select('Yes', {force: true})
      // cy.get('[name="MOLLIE_METHOD_API_giftcard"]').select('Payments API', {force: true})
      //giropay
      cy.get('[name="MOLLIE_METHOD_ENABLED_giropay"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_giropay"]').select('Payments API', {force: true})
      //eps
      cy.get('[name="MOLLIE_METHOD_ENABLED_eps"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_eps"]').select('Payments API', {force: true})
      //przelewy24
      cy.get('[name="MOLLIE_METHOD_ENABLED_przelewy24"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_przelewy24"]').select('Payments API', {force: true})
      //kbc
      cy.get('[name="MOLLIE_METHOD_ENABLED_kbc"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_kbc"]').select('Payments API', {force: true})
      //voucher
      cy.get('[name="MOLLIE_METHOD_ENABLED_voucher"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_DESCRIPTION_voucher"]').clear({force: true}).type('Lorem Ipsum 123 !@#$%^&*', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_TYPE_voucher"]').select('3', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_voucher"]').clear({force: true}).type('11', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_PERCENTAGE_voucher"]').clear({force: true}).type('22', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_LIMIT_voucher"]').clear({force: true}).type('33', {force: true})
      //belfius
      cy.get('[name="MOLLIE_METHOD_ENABLED_belfius"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_belfius"]').select('Payments API', {force: true})
      //bancontact
      cy.get('[name="MOLLIE_METHOD_ENABLED_bancontact"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_bancontact"]').select('Payments API', {force: true})
      //sofort
      cy.get('[name="MOLLIE_METHOD_ENABLED_sofort"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_sofort"]').select('Payments API', {force: true})
      //creditcard
      cy.get('[name="MOLLIE_METHOD_ENABLED_creditcard"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_creditcard"]').select('Payments API', {force: true})
      //ideal
      cy.get('[name="MOLLIE_METHOD_ENABLED_ideal"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_ideal"]').select('Payments API', {force: true})
      //klarnapaylater
      cy.get('[name="MOLLIE_METHOD_ENABLED_klarnapaylater"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_DESCRIPTION_klarnapaylater"]').clear({force: true}).type('Lorem Ipsum 123 !@#$%^&*', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_TYPE_klarnapaylater"]').select('3', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_klarnapaylater"]').clear({force: true}).type('11', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_PERCENTAGE_klarnapaylater"]').clear({force: true}).type('22', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_LIMIT_klarnapaylater"]').clear({force: true}).type('33', {force: true})
      //klarnasliceit
      cy.get('[name="MOLLIE_METHOD_ENABLED_klarnasliceit"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_DESCRIPTION_klarnasliceit"]').clear({force: true}).type('Lorem Ipsum 123 !@#$%^&*', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_TYPE_klarnasliceit"]').select('3', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_klarnasliceit"]').clear({force: true}).type('11', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_PERCENTAGE_klarnasliceit"]').clear({force: true}).type('22', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_LIMIT_klarnasliceit"]').clear({force: true}).type('33', {force: true})
      //klarnapaynow
      cy.get('[name="MOLLIE_METHOD_ENABLED_klarnapaynow"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_DESCRIPTION_klarnapaynow"]').clear({force: true}).type('Lorem Ipsum 123 !@#$%^&*', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_TYPE_klarnapaynow"]').select('3', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_klarnapaynow"]').clear({force: true}).type('11', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_PERCENTAGE_klarnapaynow"]').clear({force: true}).type('22', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_LIMIT_klarnapaynow"]').clear({force: true}).type('33', {force: true})
      //banktransfer
      cy.get('[name="MOLLIE_METHOD_ENABLED_banktransfer"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_banktransfer"]').select('Payments API', {force: true})
      //paypal
      cy.get('[name="MOLLIE_METHOD_ENABLED_paypal"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_API_paypal"]').select('Payments API', {force: true})
      //applepay
      cy.get('[name="MOLLIE_METHOD_ENABLED_applepay"]').select('Yes', {force: true})
      cy.get('[name="MOLLIE_METHOD_DESCRIPTION_applepay"]').clear({force: true}).type('Lorem Ipsum 123 !@#$%^&*', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_TYPE_applepay"]').select('3', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_applepay"]').clear({force: true}).type('11', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_PERCENTAGE_applepay"]').clear({force: true}).type('22', {force: true})
      cy.get('[name="MOLLIE_METHOD_SURCHARGE_LIMIT_applepay"]').clear({force: true}).type('33', {force: true})
})
Cypress.Commands.add("login_mollie17_test", () => {
   Cypress.env()
   cy.get('#email').type((Cypress.env('demousername')),{delay: 0, log: false})
   cy.get('#passwd').type((Cypress.env('demopassword')),{delay: 0, log: false})
   cy.get('#submit_login').click()
   cy.get('#header_shop > .dropdown').click()
   cy.get('.list-dropdown-menu > :nth-child(3)').click()
 })
Cypress.Commands.add("login_mollie16_test", () => {
   Cypress.env()
   cy.get('#email').type((Cypress.env('demousername')),{delay: 0, log: false})
   cy.get('#passwd').type((Cypress.env('demopassword')),{delay: 0, log: false})
   cy.get('.row-padding-top > .btn').click().wait(500)
   cy.get('#header_shop > .dropdown > .dropdown-toggle').click()
   cy.get('#header_shop > .dropdown > .dropdown-menu > :nth-child(3) > a').click()
   cy.visit('https://demo.invertus.eu/clients/mollie16-test/admin1/index.php?controller=AdminMollieModule')
 })
Cypress.Commands.add("prestashop_admin_localhost_1771", (url) => {
   cy.visit('/admin1771/index.php')
 })
Cypress.Commands.add("mollie_test17_admin", (url) => {
   cy.visit('https://mollie1770test.invertusdemo.com/admin1/index.php')
 })
Cypress.Commands.add("mollie_test16_admin", (url) => {
   cy.visit('https://demo.invertus.eu/clients/mollie16-test/admin1/index.php?controller=AdminMollieModule')
 })
Cypress.Commands.add("ps16_random_user", (randomuser) => {
   // Creating random user all the time
   const uuid = () => Cypress._.random(0, 1e6)
   const id = uuid()
   const testname = `testemail${id}@testing.com`
   cy.get('#email_create').type(testname, {delay: 0})
   cy.get('#SubmitCreate > span').click()
   cy.get('#id_gender1').check()
   cy.get('#customer_firstname').type('AUT',{delay:0}).as('firstname')
   cy.get('#customer_lastname').type('AUT',{delay:0}).as('lastname')
   cy.get('#passwd').type('123456',{delay:0}).as('pasw')
   cy.get('#submitAccount > span').click()
   cy.get('#company').type('123456',{delay:0}).as('company')
   cy.get('#vat-number').type('123456',{delay:0}).as('vat number')
   cy.get('#address1').type('ADDR',{delay:0}).as('address')
   cy.get('#address2').type('ADDR',{delay:0}).as('address2')
   cy.get('#postcode').type('54469',{delay:0}).as('zip')
   cy.get('#city').type('CIT',{delay:0}).as('city')
   cy.get('#id_country').select('Lithuania').as('country')
   cy.get('#phone').type('+085',{delay:0}).as('telephone')
   cy.get('#phone_mobile').type('+000',{delay:0}).as('telephone2')
 })
Cypress.Commands.add("mollie_1752_test_demo_module_dashboard", (url) => {
  cy.visit('https://demo.invertus.eu/clients/mollie17-test/admin1/index.php?controller=AdminModules&configure=mollie')
})

Cypress.Commands.add("mollie_1752_test_login", () => {
  Cypress.env()
  cy.get('#email').type((Cypress.env('demousername')),{delay: 0, log: false})
  cy.get('#passwd').type((Cypress.env('demopassword')),{delay: 0, log: false})
  cy.get('#submit_login').click().wait(3000)
  cy.get('.selected-item > .arrow-down').click()
  cy.get('#shop-list > .dropdown-menu > .items-list > :nth-child(3)').click(5,5)
  cy.get('#subtab-AdminMollieModule > .link').click()
})
Cypress.Commands.add("mollie_16124_test_faster_login_DE_Orders_Api", () => {
  cy.visit('https://demo.invertus.eu/clients/mollie16-test/en/login?back=my-account')
  cy.get('#email').type((Cypress.env('FO_username')),{delay: 0, log: false})
  cy.get('#passwd').type((Cypress.env('FO_password')),{delay: 0, log: false})
  cy.get('#SubmitLogin > span').click()
  cy.visit('https://demo.invertus.eu/clients/mollie16-test/en/home/10-test1.html')
  cy.get('.exclusive > span').click()
  cy.get('.button-medium > span').click()
})
Cypress.Commands.add("mollie_16124_test_faster_login_DE_Payments_Api", () => {
  cy.visit('https://demo.invertus.eu/clients/mollie16-test/en/login?back=my-account')
  cy.get('#email').type((Cypress.env('FO_username')),{delay: 0, log: false})
  cy.get('#passwd').type((Cypress.env('FO_password')),{delay: 0, log: false})
  cy.get('#SubmitLogin > span').click()
  cy.visit('https://demo.invertus.eu/clients/mollie16-test/en/home/9-test1.html')
  cy.get('.exclusive > span').click()
  cy.get('.button-medium > span').click()
})
Cypress.Commands.add("mollie_1770_test_faster_login_DE_Orders_Api", () => {
  cy.visit('https://mollie1770test.invertusdemo.com/en/login?back=my-account')
  cy.get('.col-md-6 > .form-control').type((Cypress.env('FO_username')),{delay: 0, log: false})
  cy.get('.input-group > .form-control').type((Cypress.env('FO_password')),{delay: 0, log: false})
  cy.get('#submit-login').click()
  cy.visit('https://mollie1770test.invertusdemo.com/en/women/2-brown-bear-printed-sweater.html')
  cy.get('.add > .btn').click()
  cy.get('.cart-content-btn > .btn-primary').click()
  cy.get('.text-sm-center > .btn').click()
})
Cypress.Commands.add("mollie_1770_test_faster_login_DE_Payments_Api", () => {
  cy.visit('https://mollie1770test.invertusdemo.com/en/login?back=my-account')
  cy.get('.col-md-6 > .form-control').type((Cypress.env('FO_username')),{delay: 0, log: false})
  cy.get('.input-group > .form-control').type((Cypress.env('FO_password')),{delay: 0, log: false})
  cy.get('#submit-login').click()
  cy.visit('https://mollie1770test.invertusdemo.com/en/men/1-hummingbird-printed-t-shirt.html')
  cy.get('.add > .btn').click()
  cy.get('.cart-content-btn > .btn-primary').click()
  cy.get('.text-sm-center > .btn').click()
})
Cypress.Commands.add("mollie_1752_test_faster_login_DE_Payments_Api", () => {
   cy.visit('https://demo.invertus.eu/clients/mollie17-test/en/login?back=my-account')
   cy.get('.col-md-6 > .form-control').type((Cypress.env('FO_username')),{delay: 0, log: false})
   cy.get('.input-group > .form-control').type((Cypress.env('FO_password')),{delay: 0, log: false})
   cy.get('#submit-login').click()
   cy.visit('https://demo.invertus.eu/clients/mollie17-test/en/home/20-testproduct1.html')
   cy.get('.add > .btn').click()
   cy.get('.cart-content-btn > .btn-primary').click()
   cy.get('.text-sm-center > .btn').click()
 })
Cypress.Commands.add("mollie_1752_test_faster_login_DE_Orders_Api", () => {
    cy.visit('https://demo.invertus.eu/clients/mollie17-test/en/login?back=my-account')
    cy.get('.col-md-6 > .form-control').type((Cypress.env('FO_username')),{delay: 0, log: false})
    cy.get('.input-group > .form-control').type((Cypress.env('FO_password')),{delay: 0, log: false})
    cy.get('#submit-login').click()
    cy.visit('https://demo.invertus.eu/clients/mollie17-test/en/home/21-testproduct1.html')
    cy.get('.add > .btn').click()
    cy.get('.cart-content-btn > .btn-primary').click()
    cy.get('.text-sm-center > .btn').click()
})
Cypress.Commands.add("OrderRefundingShippingOrdersAPI", () => {
    cy.visit('/admin1/index.php?controller=AdminOrders')
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
Cypress.Commands.add("OrderShippingRefundingOrdersAPI", () => {
    cy.visit('/admin1/index.php?controller=AdminOrders')
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
Cypress.Commands.add("OrderRefundingPartialPaymentsAPI", () => {
    cy.visit('/admin1/index.php?controller=AdminOrders')
    cy.get(':nth-child(1) > .column-payment').click()
    cy.get('#mollie_order > :nth-child(1)').should('exist')
    cy.get('.form-inline > :nth-child(1) > .btn').should('exist')
    cy.get('.input-group-btn > .btn').should('exist')
    cy.get('.sc-htpNat > .panel > .card-body > :nth-child(3)').should('exist')
    cy.get('.card-body > :nth-child(6)').should('exist')
    cy.get('.card-body > :nth-child(9)').should('exist')
    cy.get('#mollie_order > :nth-child(1) > :nth-child(1)').should('exist')
    cy.get('.sc-htpNat > .panel > .card-body').should('exist')
    cy.get('.sc-bxivhb > .panel > .panel-heading').should('exist')
    cy.get('.sc-bxivhb > .panel > .card-body').should('exist')
    //Check partial refunding on Payments API
    cy.get('.form-inline > :nth-child(2) > .input-group > .form-control').type('1.51',{delay:0})
    cy.get(':nth-child(2) > .input-group > .input-group-btn > .btn').click()
    cy.get('.swal-modal').should('exist')
    cy.get(':nth-child(2) > .swal-button').click()
    cy.get('#mollie_order > :nth-child(1) > .alert').contains('Refund was made successfully!')
    cy.get('.form-inline > :nth-child(1) > .btn').click()
    cy.get('.swal-modal').should('exist')
    cy.get(':nth-child(2) > .swal-button').click()
    cy.get('#mollie_order > :nth-child(1) > .alert').contains('Refund was made successfully!')
})
Cypress.Commands.add("EnablingModuleMultistore", () => {
  cy.get('#subtab-AdminParentModulesSf > :nth-child(1)').click()
  cy.get('#subtab-AdminModulesSf').click()
  cy.get('.pstaggerAddTagInput').type('mollie')
  cy.get('#module-search-button').click()
  cy.get('.btn-group > .btn-primary-reverse').click()
})
Cypress.Commands.add("OpenModuleDashboard", () => {
    cy.get('#subtab-AdminParentModulesSf > :nth-child(1)').click()
    cy.get('#subtab-AdminModulesSf').click()
    cy.get('.pstaggerAddTagInput').type('mollie')
    cy.get('#module-search-button').click()
    cy.get('.btn-group > .btn-primary-reverse').click()
})
