<section class="recurring-method-form box">
  <div class="row">
    <div class="col-12 col-lg-6">
      <div class="row product-info">
        <div class="col-12 col-lg-6 product-img">
          <img class="img"
               src="{$order.img}"
               itemprop="image" width="200">
        </div>
        <div class="col-12 col-lg-6">
          <a href="{$order.link}"><p><b>{l s='Product:' mod='mollie'}</b> {$order.name}</p></a>
          <p><b>{l s='Quantity:' mod='mollie'}</b> {$order.quantity}</p>
          <p><b>{l s='Unit price:' mod='mollie'}</b> {$order.unit_price}</p>
        </div>
      </div>
    </div>
    <div class="col-12 col-lg-6">
      <p><b>{l s='Total:' mod='mollie'}</b> {$order.total}</p>
      <p><b>{l s='Subscription status:' mod='mollie'}</b> {$order.status}</p>
      <p><b>{l s='Subscription start date:' mod='mollie'}</b> {$order.start_date}</p>

      {if isset($order.next_payment_date)}
        <p><b>{l s='Next payment date:' mod='mollie'}</b> {$order.next_payment_date}</p>
      {/if}

      {if isset($order.cancelled_date)}
        <p><b>{l s='Cancelled date:' mod='mollie'}</b> {$order.cancelled_date}</p>
      {/if}
    </div>
  </div>
</section>
