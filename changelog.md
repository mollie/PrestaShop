![Mollie](https://www.mollie.nl/files/Mollie-Logo-Style-Small.png)

# Changelog #

## Changes in release 2.0.0 ##
+ Compatibility with Prestashop 1.7+
+ Fix for "Frontcontroller::init - cart cannot be loaded".
+ Fix for recieving payment multiple times.
+ Added option to send customer order to Mollie metadata 
+ Mollie now works in maintenance mode.
+ Improved configuration page.
+ Improved User Experience.

## Changes in release 1.3.0 ##
+ Automatically pass along the customer's email address for banktransfer payment instructions, when available.

## Changes in release 1.2.6 ##
+ Fixed incorrect install/de-install hooks.
+ Update submodule [mollie-api-php](https://github.com/mollie/mollie-api-php) to `v1.5.1`

## Changes in release 1.2.5 ##
+ Added some new translations.
+ EU Compliance module has been added.
+ Update submodule mollie-api-php to version 1.4.0.

## Changes in release 1.2.4 ##
+ Added payment description filters ({cart.id} {customer.firstname} {customer.lastname} {customer.company})
+ Update submodule mollie-api-php to version 1.3.3

## Changes in release 1.2.3 ##
+ Set default language for admin when no language set
+ Update submodule mollie-api-php to version 1.3.1

## Changes in release 1.2.2 ##
+ Fixed issue where banktransfer status didn't get updated correctly
+ Update submodule mollie-api-php to version 1.2.8

## Changes in release 1.2.1 ##
+ Fixed an issue where the country context is not set when Mollie calls the PrestaShop webhook.
+ Fixed an issue where the iDEAL issuers are not displayed correctly when using module OnePageCheckout.
+ Update submodule mollie-api-php to version 1.2.7

## Changes in release 1.2.0 ##
+ Add option to send webshop locale to Mollie to use in payment screens. This will use the
current language and country locale (In admin: Localization -> Advanced)

## Changes in release 1.1.0 ##
+ Add English and German translations
+ Save Mollie payment id (tr_xxxx) in order payment
+ Update Mollie API client to version 1.2.5, contains system compatibility checker

## Changes in release 1.0.8 ##
+ Create the order before the payment is finished when payment method is banktransfer.

## Changes in release 1.0.7 ##
+ Stop Google Analytics from listing payment provider as referrer.

## Changes in release 1.0.6 ##
+ Fixed redirect issue for cancelled payments.

## Changes in release 1.0.5 ##
+ Fixed currency issue on return page.

## Changes in release 1.0.4 ##
+ Added cart ID to payment descriptions.
+ Fixed missing keys warning.

## Changes in release 1.0.3 ##
+ Fixed error in payment method selection.
+ Prevent customers from returning to an empty cart after cancelling a payment.
+ Fixed issues with Google Analytics by using Prestashop's default return page.

## Changes in release 1.0.2 ##
+ Added 'open' order status.
+ Fixed bug where order updated twice.
+ Fixed iDEAL bank list not showing.

## Changes in release 1.0.1 ##
+ Fixed issue with custom statuses for open payments.

## Changes in release 1.0.0 ##
+ Initial release.
