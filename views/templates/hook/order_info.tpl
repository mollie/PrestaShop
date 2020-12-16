{*
 *
 * Copyright (c) 2012-2020, Mollie B.V.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * - Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR AND CONTRIBUTORS ``AS IS'' AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE AUTHOR OR CONTRIBUTORS BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
 * OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH
 * DAMAGE.
 *
 * @author     Mollie B.V. <info@mollie.nl>
 * @copyright  Mollie B.V.
 * @license    Berkeley Software Distribution License (BSD-License 2) http://www.opensource.org/licenses/bsd-license.php
 * @category   Mollie
 * @package    Mollie
 * @link       https://www.mollie.nl
 *
 *}
<div id="mollie_order"></div>
<script type="text/javascript">
  (function () {
    window.MollieModule = window.MollieModule || {ldelim}{rdelim};
    window.MollieModule.urls = window.MollieModule.urls || {ldelim}{rdelim};
    window.MollieModule.urls.publicPath = '{$publicPath|escape:'javascript':'UTF-8'}';
    window.MollieModule.debug = {if $errorDisplay}true{else}false{/if};
  }());
  (function initTransactionInfo() {
    if (typeof window.MollieModule === 'undefined'
            || typeof window.MollieModule.app === 'undefined'
            || typeof window.MollieModule.app.default === 'undefined'
            || typeof window.MollieModule.app.default.transactionInfo === 'undefined'
    ) {
      return setTimeout(initTransactionInfo, 100);
    }

    window.MollieModule.app.default.transactionInfo().then(function (fn) {
      fn.default(
              "#mollie_order",
              {
                ajaxEndpoint: '{$ajaxEndpoint|escape:'javascript':'UTF-8'}',
                moduleDir: '{$module_dir|escape:'javascript':'UTF-8'}',
                initialStatus: 'form',
                transactionId: '{$transactionId|escape:'javascript':'UTF-8'}',
                legacy: {if version_compare($smarty.const._PS_VERSION_, '1.6.0.0', '<')}true{else}false{/if},
                tracking: {$tracking|json_encode}
              },
              {
                areYouSure: '{l s='Are you sure?' mod='mollie' js=1}',
                areYouSureYouWantToRefund: '{l s='Are you sure you want to refund this order?' mod='mollie' js=1}',
                refund: '{l s='Refund' mod='mollie' js=1}',
                cancel: '{l s='Cancel' mod='mollie' js=1}',
                refundOrder:'{l s='Refund order' mod='mollie' js=1}',
                refundable: '{l s='Refundable' mod='mollie' js=1}',
                partialRefund: '{l s='Partial refund' mod='mollie' js=1}',
                invalidAmount: '{l s='Invalid amount' mod='mollie' js=1}',
                notAValidAmount: '{l s='You have entered an invalid amount' mod='mollie' js=1}',
                refundFailed: '{l s='Refund failed' mod='mollie' js=1}',
                paymentInfo: '{l s='Payment info' mod='mollie' js=1}',
                transactionId: '{l s='Transaction ID' mod='mollie' js=1}',
                refundHistory: '{l s='Refund history' mod='mollie' js=1}',
                thereAreNoRefunds: '{l s='There are no refunds' mod='mollie' js=1}',
                ID: '{l s='ID' mod='mollie' js=1}',
                date: '{l s='Date' mod='mollie' js=1}',
                amount: '{l s='Amount' mod='mollie' js=1}',
                refunds: '{l s='Refunds' mod='mollie' js=1}',
                payments: '{l s='Payments' mod='mollie' js=1}',
                currentAmount: '{l s='Current amount' mod='mollie' js=1}',
                products: '{l s='Products' mod='mollie' js=1}',
                status: '{l s='Status' mod='mollie' js=1}',
                shipped: '{l s='Shipped' mod='mollie' js=1}',
                canceled: '{l s='Canceled' mod='mollie' js=1}',
                refunded: '{l s='Refunded' mod='mollie' js=1}',
                unitPrice: '{l s='Unit Price' mod='mollie' js=1}',
                vatAmount: '{l s='VAT Amount' mod='mollie' js=1}',
                totalAmount: '{l s='Total amount' mod='mollie' js=1}',
                ship: '{l s='Ship' mod='mollie' js=1}',
                reviewShipment: '{l s='Review shipment' mod='mollie' js=1}',
                reviewShipmentProducts: '{l s='Please review the products included in your shipment. You can remove items or change quantity if needed.' mod='mollie' js=1}',
                OK: '{l s='OK' mod='mollie' js=1}',
                shipProducts: '{l s='Ship products' mod='mollie' js=1}',
                trackingDetails: '{l s='Tracking details' mod='mollie' js=1}',
                addTrackingInfo: '{l s='Adding tracking information to your shipment is recommended, as it will prove that you actually shipped your products to your customer.' mod='mollie' js=1}',
                skipTrackingDetails: '{l s='Skip tracking details' mod='mollie' js=1}',
                optional: '{l s='optional' mod='mollie' js=1}',
                egFedex: '{l s='E.g. FedEx' mod='mollie' js=1}',
                thisInfoIsRequired: '{l s='This information is required' mod='mollie' js=1}',
                trackingCode: '{l s='Tracking code' mod='mollie' js=1}',
                url: '{l s='URL' mod='mollie' js=1}',
                carrier: '{l s='Carrier' mod='mollie' js=1}',
                shipAll: '{l s='Ship all' mod='mollie' js=1}',
                cancelAll: '{l s='Cancel all' mod='mollie' js=1}',
                refundAll: '{l s='Refund all' mod='mollie' js=1}',
                transactionInfo: '{l s='Transaction info' mod='mollie' js=1}',
                voucherInfo: '{l s='Voucher info' mod='mollie' js=1}',
                thereAreNoProducts: '{l s='There are no products' mod='mollie' js=1}',
                anErrorOccurred: '{l s='An error occurred' mod='mollie' js=1}',
                unableToShip: '{l s='Unable to ship' mod='mollie' js=1}',
                unableToRefund: '{l s='Unable to refund' mod='mollie' js=1}',
                unableToCancel: '{l s='Unable to cancel' mod='mollie' js=1}',
                refundsAreCurrentlyUnavailable: '{l s='Refunds are currently unavailable' mod='mollie' js=1}',
                refundSuccessMessage: '{l s='Refund was made successfully!' mod='mollie' js=1}',
                shipmentWarning: '{l s='Shipment was made successfully!' mod='mollie' js=1}',
                cancelWarning: '{l s='Order was canceled successfully!' mod='mollie' js=1}',
                method: '{l s='Method' mod='mollie' js=1}',
                remainderMethod: '{l s='Remainder method' mod='mollie' js=1}',
                issuer: '{l s='Issuer' mod='mollie' js=1}',
                refundWarning: '{l s='Warning! This order is (partially) paid using a voucher. You can refund a maximum of %1s.' mod='mollie' js=1}',
              },
              {$currencies|json_encode}
      );
    });
  }());
</script>
{foreach $webPackChunks as $webPackChunk}
  <script type="text/javascript" src="{$webPackChunk|escape:'html':'UTF-8'}"></script>
{/foreach}
