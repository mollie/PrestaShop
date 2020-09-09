![Mollie](https://www.mollie.nl/files/Mollie-Logo-Style-Small.png)

# Changelog #
## Changes in release 4.0.8 ##
+ Fixed issue where backorder paid status didn't send order_conf mail.
+ Fixed issue where order status that ended with id 0 would not get saved in mollie settings.
+ Fixed custom shipment information .
+ Refactored payment data creation to use objects instead of arrays.
+ Added single click payment.
+ Added descriptions of API methods.
+ Added API key test button.
+ Improved UX of Payments from visual side.
+ Improved UX of API from visual side.
+ Added module upgrade notice.
+ Added Mollie tab in main tabs.
+ Added custom logo for credit card payment.
+ Improved calculation logic to use Number class.
+ Added selector to control when email alert module can send new_order mail.
+ Fixed issues with refunded orders where sometimes refunded order would have partially refunded status.
+ Fixed issue with gifts and wrapping.

## Changes in release 4.0.7 ##
+ Added new order status “Completed” which is set when Mollie order is finished.
+ Added option for PS1.7 to choose when to send order_conf email to the customer.
+ Added new Mollie_payment email template that has Mollie payment link.
+ Added “Second chance email button” in admin order list. Orders with Mollie payment will have option which allows merchant to send Mollie_payment email to the customer where he can finish his order payment.
+ Added option to create order with Mollie payment from back office and send Mollie_payment mail to customer where he can finish his order.
+ Added logic where cart is saved for customer if he returns back to checkout from Mollie payment page (due to possible security vulnerabilities we removed voucher restoration if such have been added to cart).
+ Added missing translations.
+ Added functionality where merchant can exclude countries if all countries are selected for payment option.
+ Changed locale settings and fixed issue where webshop locale was not sent to mollie if option is selected.
+ Improved settings descriptions.
+ Fixed issues with credit card and order API.
+ Improved UX of Payments Enabling/Disabling function from visual side.
+ Shipping order status fixed.

## Changes in release 4.0.6 ##
+ Webhook call fail issue error 500.
+ Status duplication when payment is paid or canceled.
+ Payment method in pdf file when order is paid.
+ Return from payment screen stuck.
+ Vendor issue where in some cases it would throw error because another vendor file already has random_bytes function.
+ Tax excluded orders where the calculation were off if prices are displayed without taxes.
+ Below features added: Switch for PS1.7 that allows to choose if you want to send order_conf mail which is sent before payment is completed. Hidden order_conf mail switch for ps1.6 because it can only be disabled in core.

## Changes in release 4.0.5 ##
+ Improved payment method description in settings.
+ Fixed order transaction id when payment is accepted.
+ Removed duplicated order validation on webhook call.
+ Fixed some namespace issues.

## Changes in release 4.0.4 ##
+ Fixed issue "Automatically ship when one of these statuses is reached" wasn't working correctly.
+ Disabled order_conf email for PS1.7. To disable it for PS1.6 you need to change PS core. classes/PaymentModule.php. you should comment out line 271:
Mail::Send(intval($order->id_lang), 'order_conf', 'Order confirmation', $data, $customer->email, $customer->firstname.' '.$customer->lastname, NULL, NULL, $fileAttachment);
+ Added fix for PS1.6 hookDisplayPaymentEU where it was using wrong method ID.
+ Added fix where saving API Key would reset default order statuses.

## Changes in release 4.0.3 ##
+ Fixed upgrade cache issue where upgrading module would only work after second time.
+ Fixed guzzle conflict with other modules that have guzzle in a vendor.

## Changes in release 4.0.2 ##
+ Added index.php files.
+ Added missing license and updated the old ones.
+ Added switch to send an email when payment is accepted.

## Changes in release 4.0.0 ##
+ Fixed double order status bug - Now Order is created before payment
+ Fixed Payment Accepted email bug
+ Fixed PHP bug where sometimes the total price is incorrect
+ New UI - Created new payment methods form in General settings
+ New Order payment Fee
+ Added payment Fee to PDF Invoice if param is added
+ Now fully compatible only from PrestaShop 1.6.1.x PHP 5.6
+ Refactored shipment settings from React to jQuery and PHP
+ Removed override validation
+ Fixed order status bug when order status is not changed to a specific status that is selected in Back-office
+ Fixed email receiving bugs
+ Prestashop 1.6 error fixes and small changes
+ Fixed bugs in One Page checkout and other Prestashop 1.6 bugs

## Changes in release 3.5.5 ##
+ Updated licenses
+ Fixed bug with front office set media hook
+ Order status bug fix

## Changes in release 3.5.4 ##
+ Fixed issue where in some themes JS and CSS wouldn't load in front office.
+ Fixed issue where profile always used test mode API.
+ Fixed email issue where email wouldn't add invoice on back-order.

## Changes in release 3.5.3 ##
+ Fixed shipment information save and auto fill when shipping from admin order page.
+ Fixed auto shipment on selected statuses.
+ Removed possibility to display payments own page.
+ Added success messages in admin order page for cancel, refund and shipment options.
+ Improved error messages in front office.
+ Improved translations.
+ Improved credit card inputs to no longer click on place holder when trying to use input on mobile.
+ Improved Mollie settings to be more user friendly.

## Changes in release 3.5.2 ##
+ Added Mollie Credit cards Components for PrestaShop1.6.1.*
+ Fixed wrong API key error handle.
+ Improved cards Components display for phones.
+ Fixed bug where shipment information URL wouldn't get saved.
+ Removed wrong configurations on 3.5.2 upgrade.
+ Added warning messages if required inputs are empty or invalid.
+ Fixed invoice with price without tax.

## Changes in release 3.5.0 ##
+ Added Mollie Components for Credit cards for PrestaShop1.7.*
+ Fixed order statuses duplication.

## Changes in release 3.4.7 ##
+ Fixed tax issue when products price is displayed without tax.
+ Removed CarteSi payment method.
+ Fixed order refund, cancel and shipment in admin order page.
+ Fixed some minor issues.

## Changes in release 3.4.6 ##
+ Fixed out of stock order status issue.
+ Fixed price rounding issues.
+ Fixed Mollie email sender bug with translations.
+ Custom css only applied on front office.
+ Apple pay is only available if SSL is enabled.

## Changes in release 3.4.5 ##
+ Added description validation in BO.
+ Fixed install and uninstall issues.
+ Fixed error when opening module for the fist time after installing Mollie.
+ Added configuration deletion when uninstalling module.

## Changes in release 3.4.4 ##
+ Fixed custom CSS in advanced settings tab.
+ Fixed issue where MOLLIE_METHODS_CONFIG sometimes would get too big for database.
+ Fixed upgrade issues.
+ Fixed countries restriction issue on PS1.6 version.

## Changes in release 3.4.3 ##
+ Added country selector for each method in back-office for PS1.6-PS1.7 versions.
+ Fixed publicPath error.
+ Added new Mollie logo in back-office.

## Changes in release 3.4.2 ##
+ Fixed orders status not set in admin order page.
+ Fixed bug where setting API key would reset Mollie configurations.
+ Added MyBank payment method.

## Changes in release 3.4.1 ##
+ Fixed wrong namespace when calling vendor classes.
+ Fixed issue with admin order page JS global values.
+ Fixed bug where PayPal with payment API would trow error in admin order page.

## Changes in release 3.4.0 ##
+ Settings splitted to General settings and advanced settings tabs.
+ Added apple pay method.
+ Payment API no longer needs billing and shipping addresses.
+ Updated FR translations.
+ Fixed statuses in back-office order page.
+ Added backorder statuses for orders with out of stock products.
+ Fixed issue when ordering without taxes.
+ Moved Html code out of PHP classes.
+ Changed namespace prefix when calling vendor functions.

## Changes in release 3.3.5 ##
+ [Orders API] Support both discounts + rounding method conversions
+ [Orders API] Support rounding differences caused by discounts


## Changes in release 3.3.4 ##
+ Override default bankwire template mail variables
+ Disable displayPaymentEU hook on 1.7 (redundant for ps_legalcompliance module)
+ Prevent double Mollie order states on (re)install
+ [Payments API] Defer QR code initialization until visible
+ [Orders API] Add support for multiple payments per order
+ [Orders API] Allow more carrier configurations for auto shipping

## Changes in release 3.3.3 ##
+ Debounce payment status check on return page
+ Restore mod_php support
+ [Orders API] Add support for cumulative specific price rules


## Changes in release 3.3.2 ##
+ Improved cache handling
+ Bypass webhooks for localhost testing
+ Postcode no longer required
+ Wait on the return page for the order status to become either paid/canceled/etc. (anything but created/pending or open when method != banktransfer)
+ [Orders API] Avoid listing free items on the order product list
+ [Orders API] Fix order reference
+ [Orders API] Fix sending mails
+ [Orders API] Improved order rounding handling


## Changes in release 3.3.1 ##
+ Make Klarna Pay later. and Klarna Slice it. translatable in checkout
+ (Payments API) Add {order.reference} example to description field


## Changes in release 3.3.0 ##
+ New payment method: Klarna (switch the module to the Orders API to unlock this payment method)
+ Add support for the Orders API
+ SVG payment method icons
+ Add a fallback (use attached cacert.pem) in case the root certificates are missing from the system, so a connection with the Mollie API can still be made
+ Completely detach from the Mollie API on the configuration page and checkout; this should improve the checkout speed as well as prevent it from taking down the entire checkout during an outage.
+ (Orders API) Ship, cancel and or refund order/order lines straight from the back office
+ (Orders API) Add automatic shipment tracking
+ (Payments API) You can now add {order.reference} to the payment description.
+ (Payments API) Add support for full and partial refunds from the back office


## Changes in release 3.2.0 ##
+ Code and style changes for Addons validation

## Changes in release 3.1.0 ##
+ Compatible with PrestaShop 1.7.4
+ Separate listings for Cartes Bancaires + CartaSi
+ More locales supported
+ Fix eps/iDEAL return pages
+ Fix default PrestaShop order statuses for Mollie paid and open statuses
+ Fix shipping address passed to Mollie
+ Fix QR Codes window height check

## Changes in release 3.0.2 ##
+ Fix for permanent error reporting
+ Fix built-in module updater on PS 1.7

## Changes in release 3.0.1 ##
+ Fix for incorrect rounding of amounts > € 1.000,00 in PrestaShop 1.7

## Changes in release 3.0.0 ##
+ Added iDEAL QR Codes
+ Added auto upgrader
+ Added sortable payment methods
+ Added Mollie multi-currency
+ Fix for Issue #53 : Change "ps_orders.payment" after customer changes payment method
+ Fix for Issue #52 : Make the 'order canceled' function optional

## Changes in release 2.0.6 ##
+ Fix for Issue #54 : Error executing API call when "Send customer credentials to Mollie" is enabled

Note: To prevent confusion for those who are used to Mollie showing TBM Bank when doing a iDEAL test payment. All bank options are showing instead of TBM Bank from now on. Select a bank to make a test payment and you will be redirected to the Mollie test payment page.

## Changes in release 2.0.5 ##
+ Fix for Issue #50: 'Send webshop locale' setting results in redirect to homepage

## Changes in release 2.0.4 ##
+ fix for Issue #45 - adjusted hookDisplayAdminOrder() method

## Changes in release 2.0.3 ##
+ fix for Issue #44 - added return.tpl for Prestashop 1.7.x

## Changes in release 2.0.2 ##
+ fix for ING Home Pay

## Changes in release 2.0.1 ##
+ Compatibility with Prestashop 1.7.3.0 final

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
