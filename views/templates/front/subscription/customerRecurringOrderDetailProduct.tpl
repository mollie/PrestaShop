<section class="recurring-method-form box">
  <div class="row">
    <div class="col-12 col-lg-6">
      <div class="row product-info">
        <div class="col-12 col-lg-6 product-img">
          <img class="img"
               src="{$order.img|escape:'htmlall':'UTF-8'}"
               itemprop="image" width="200">
        </div>
        <div class="col-12 col-lg-6">
          <a href="{$order.link|escape:'htmlall':'UTF-8'}"><p><b>{l s='Product:' mod='mollie'}</b> {$order.name|escape:'htmlall':'UTF-8'}</p></a>
          <p><b>{l s='Quantity:' mod='mollie'}</b> {$order.quantity|escape:'htmlall':'UTF-8'}</p>
          <p><b>{l s='Unit price:' mod='mollie'}</b> {$order.unit_price|escape:'htmlall':'UTF-8'}</p>
        </div>
      </div>
    </div>
    <div class="col-12 col-lg-6">
      <p><b>{l s='Total:' mod='mollie'}</b> {$order.total|escape:'htmlall':'UTF-8'}</p>
      <p><b>{l s='Subscription status:' mod='mollie'}</b> {$order.status|escape:'htmlall':'UTF-8'}</p>
      <p><b>{l s='Subscription start date:' mod='mollie'}</b> {$order.start_date|escape:'htmlall':'UTF-8'}</p>

      {if isset($order.next_payment_date)}
        <p><b>{l s='Next payment date:' mod='mollie'}</b> {$order.next_payment_date|escape:'htmlall':'UTF-8'}</p>
      {/if}

      {if isset($order.cancelled_date)}
        <p><b>{l s='Cancelled date:' mod='mollie'}</b> {$order.cancelled_date|escape:'htmlall':'UTF-8'}</p>
      {/if}
    </div>
  </div>
</section>
