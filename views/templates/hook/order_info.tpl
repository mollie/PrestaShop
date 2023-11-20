{**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 *
 * @see        https://github.com/mollie/PrestaShop
 * @codingStandardsIgnoreStart
 *}
<div id="mollie_order" class="test"></div>
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
                notAValidAmount: '{l s='Enter a valid amount' mod='mollie' js=1}',
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
                unitPrice: '{l s='Unit price' mod='mollie' js=1}',
                vatAmount: '{l s='VAT amount' mod='mollie' js=1}',
                totalAmount: '{l s='Total amount' mod='mollie' js=1}',
                ship: '{l s='Ship' mod='mollie' js=1}',
                reviewShipment: '{l s='Review shipment' mod='mollie' js=1}',
                reviewShipmentProducts: '{l s='Review the products included in the shipment. You can remove items or change the quantity if needed.' mod='mollie' js=1}',
                reviewRefund: '{l s='Review refund' mod='mollie' js=1}',
                reviewRefundProducts: '{l s='Review the products included in the refund. You can remove items or change the quantity if needed.' mod='mollie' js=1}',
                reviewCancel: '{l s='Review cancellation' mod='mollie' js=1}',
                reviewCancelProducts: '{l s='Review the products included in the cancellation. You can remove items or change the quantity if needed.' mod='mollie' js=1}',
                OK: '{l s='OK' mod='mollie' js=1}',
                shipProducts: '{l s='Ship products' mod='mollie' js=1}',
                trackingDetails: '{l s='Tracking details' mod='mollie' js=1}',
                addTrackingInfo: '{l s='Add tracking information to record that you shipped products to your customer.' mod='mollie' js=1}',
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
                refundWarning: '{l s='This order was (partially) paid for with a voucher. You can refund a maximum of %1s.' mod='mollie' js=1}',
              },
              {$currencies|json_encode}
      );
    });
  }());
</script>
{foreach $webPackChunks as $webPackChunk}
  <script type="text/javascript" src="{$webPackChunk|escape:'html':'UTF-8'}"></script>
{/foreach}
