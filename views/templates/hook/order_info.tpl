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

<div class="panel card mollie-order-info-panel">
  <div class="panel-heading card-header">
    <img src="{$mollie_logo_path}" width="16" height="16" alt="Mollie logo" style="opacity: 0.8;" />
    &nbsp;<span>Mollie order - #{$order_reference|escape:'html':'UTF-8'}</span>
  </div>
  <div class="card-body">
    {if null == $products}
    <div class="alert alert-info" role="alert">
      {l s='No products are available for this order because it was placed before Mollie version 6.4.1. You can still use the refund, capture, or ship actions.' mod='mollie'}
    </div>
    {/if}
    {if $mollie_api_type == 'payments'}
    <div class="form-group">
      <label for="mollie-refund-amount">{l s='Refund amount (Max: %s)' sprintf=[$refundable_amount] mod='mollie'}</label>
      <input type="number" step="0.01" max="{$refundable_amount}" class="form-control" id="mollie-refund-amount" value="{$refundable_amount}" {if $isRefunded || $refundable_amount <= 0}disabled{/if} />
    </div>
    <button type="button" class="btn btn-primary btn-block" id="mollie-initiate-refund" {if $isRefunded || $refundable_amount <= 0}disabled{/if}>
      <i class="material-icons">replay</i> {l s='Initiate Refund' mod='mollie'}
    </button>
    <div class="form-group capture-div">
      <label for="mollie-capture-amount">{l s='Capture amount (Capturable: %s)' sprintf=[$capturable_amount] mod='mollie'}</label>
      <input type="number" step="0.01" max="{$capturable_amount}" class="form-control" id="mollie-capture-amount" value="{$capturable_amount}" {if $isCaptured || $capturable_amount <= 0}disabled{/if} />
    </div>
    <button type="button" class="btn btn-primary btn-block" id="mollie-initiate-capture" {if $isCaptured || $capturable_amount <= 0}disabled{/if}>
      <i class="material-icons">payments</i> {l s='Initiate Capture' mod='mollie'}
    </button>
    <hr />
    {/if}
    <table class="table table-bordered table-condensed">
      <thead>
        <tr>
          <th>{l s='Product' mod='mollie'}</th>
          <th>{l s='Price' mod='mollie'}</th>
          <th>{l s='Actions' mod='mollie'}</th>
        </tr>
      </thead>
      <tbody>
        {foreach from=$products item=product}
          {* Orders API *}
          {if isset($product->name)}
            <tr>
              <td><strong>{$product->quantity}x</strong> {$product->name|escape:'html':'UTF-8'}</td>
              <td>{$product->totalAmount->value|escape:'html':'UTF-8'}</td>
              <td>
              {if $mollie_api_type == 'orders' && $product->name != 'Discount'}
                <button type="button" class="btn btn-default btn-xs mollie-ship-btn" data-price="{$product->totalAmount->value}" data-orderline="{$product->id}" {if $product->quantityShipped == $product->quantity || $product->quantityCanceled == $product->quantity}disabled{/if}>
                  <i class="material-icons">local_shipping</i> {l s='Ship' mod='mollie'}
                </button>
                <button type="button" class="btn btn-default btn-xs mollie-cancel-btn" data-orderline="{$product->id}" {if $product->quantityCanceled == $product->quantity || $isShipped}disabled{/if}>
                  <i class="material-icons">cancel</i> {l s='Cancel' mod='mollie'}
                </button>
              {/if}
              {if $product->name != 'Discount'}
                <button type="button" class="btn btn-default btn-xs mollie-refund-btn" data-price="{$product->totalAmount->value}" data-orderline="{$product->id}" {if $product->quantityRefunded == $product->quantity || $isCanceled}disabled{/if}>
                  <i class="material-icons">replay</i> {l s='Refund' mod='mollie'}
                </button>
              {/if}
              </td>
            </tr>
          {/if}

          {* Payments API *}
          {if isset($product->description)}
            <tr>
              <td><strong>{$product->quantity}x</strong> {$product->description|escape:'html':'UTF-8'}</td>
              <td>{$product->totalAmount->value|escape:'html':'UTF-8'}</td>
              <td>
              {if $mollie_api_type == 'payments' && $product->description != 'Discount'}
                <button type="button" class="btn btn-default btn-xs mollie-capture-btn" data-price="{$product->totalAmount->value}" {if $isCaptured}disabled{/if}>
                  <i class="material-icons">payments</i> {l s='Capture' mod='mollie'}
                </button>
              {/if}
              {if $product->description != 'Discount'}
                <button type="button" class="btn btn-default btn-xs mollie-refund-btn" data-price="{$product->totalAmount->value}" {if $product->totalAmount->value > $refundable_amount}disabled{/if}>
                  <i class="material-icons">replay</i> {l s='Refund' mod='mollie'}
                </button>
              {/if}
              </td>
            </tr>
          {/if}
        {/foreach}
      </tbody>
    </table>
    {if $mollie_api_type == 'orders'}
      <button type="button" class="btn btn-default btn-block" id="mollie-refund-all-orders" {if $isRefunded || $refundable_amount <= 0 || $isCanceled}disabled{/if}>
        <i class="material-icons">replay</i> {l s='Refund all' mod='mollie'}
      </button>
      <button type="button" class="btn btn-default btn-block" id="mollie-ship-all" {if $isShipped || $isRefunded || $isCanceled}disabled{/if}>
        <i class="material-icons">local_shipping</i> {l s='Ship All' mod='mollie'}
      </button>
      <button type="button" class="btn btn-default btn-block" id="mollie-cancel-all" {if $isCanceled || $isRefunded || $isShipped}disabled{/if}>
        <i class="material-icons">cancel</i> {l s='Cancel All' mod='mollie'}
      </button>
    {/if}
  </div>
</div>

{include file="module:mollie/views/templates/hook/partials/modal_refund.tpl"}
{include file="module:mollie/views/templates/hook/partials/modal_ship.tpl"}
{include file="module:mollie/views/templates/hook/partials/modal_capture.tpl"}
{include file="module:mollie/views/templates/hook/partials/modal_cancel.tpl"}