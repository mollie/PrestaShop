<div id="mollie_order"></div>
<script type="text/javascript">
  (function () {
    window.MollieModule.order.orderInfo(
      "#mollie_order",
      {
        ajaxEndpoint: '{$ajaxEndpoint|escape:'javascript':'UTF-8'}',
        moduleDir: '{$module_dir|escape:'javascript':'UTF-8'}',
        initialStatus: 'form',
        orderId: {$smarty.get.id_order|intval},
      },
      {
        areYouSure: '{l s='Are you sure?' mod='mollie'}',
        areYouSureYouWantToRefund: '{l s='Are you sure you want to refund this order?' mod='mollie'}',
        refund: '{l s='Refund' mod='mollie'}',
        cancel: '{l s='Cancel' mod='mollie'}',
      }
    );
  }());
</script>
