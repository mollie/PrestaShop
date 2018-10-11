<div id="mollie_order"></div>
<script type="text/javascript">
  (function () {
    window.MollieModule.back.orderInfo(
      "#mollie_order",
      {
        ajaxEndpoint: '{$ajaxEndpoint|escape:'javascript':'UTF-8'}',
        moduleDir: '{$module_dir|escape:'javascript':'UTF-8'}',
        initialStatus: 'form',
        orderId: {$smarty.get.id_order|intval},
      },
      {
        areYouSure: '{l s='Are you sure?' mod='mollie' js=1}',
        areYouSureYouWantToRefund: '{l s='Are you sure you want to refund this order?' mod='mollie' js=1}',
        refund: '{l s='Refund' mod='mollie' js=1}',
        cancel: '{l s='Cancel' mod='mollie' js=1}',
        fullRefund:'{l s='Full refund' mod='mollie' js=1}',
        remaining: '{l s='Remaining' mod='mollie' js=1}',
        partialRefund: '{l s='Partial refund' mod='mollie' js=1}',
      }
    );
  }());
</script>
