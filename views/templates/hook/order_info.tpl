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
    {* <span class="label label-success pull-right" style="margin-left: 10px;">{l s='Authorized' mod='mollie'}</span> *}
  </div>
  <div class="card-body">
    <div class="form-group">
      <label>{l s='Applicable to orders paid exclusively through Mollie' mod='mollie'}</label>
      <div class="radio">
        <label>
          <input type="radio" name="refund_type" value="partial" id="mollie-partial-refund" {if $isRefunded || $refundable_amount <= 0}disabled{/if} />
          {l s='Partial refund' mod='mollie'}
        </label>
      </div>
      <div class="radio">
        <label>
          <input type="radio" name="refund_type" value="full" id="mollie-full-refund" checked {if $isRefunded || $refundable_amount <= 0}disabled{/if} />
          {l s='Full refund' mod='mollie'}
        </label>
      </div>
    </div>
    <div class="form-group">
      <label for="mollie-refund-amount">{l s='Refund amount (Max: %s)' sprintf=[$refundable_amount] mod='mollie'}</label>
      <input type="number" step="0.01" max="{$refundable_amount}" class="form-control" id="mollie-refund-amount" value="{$refundable_amount}" {if $isRefunded || $refundable_amount <= 0}disabled{/if} />
    </div>
    <button type="button" class="btn btn-primary btn-block" id="mollie-initiate-refund" {if $isRefunded || $refundable_amount <= 0}disabled{/if}>
      <i class="icon-undo"></i> {l s='Initiate Refund' mod='mollie'}
    </button>
    {if $mollie_api_type == 'payments'}
    <div class="form-group capture-div">
      <label for="mollie-capture-amount">{l s='Capture amount (Capturable: %s)' sprintf=[$capturable_amount] mod='mollie'}</label>
      <input type="number" step="0.01" max="{$capturable_amount}" class="form-control" id="mollie-capture-amount" value="{$capturable_amount}" {if $isCaptured || $capturable_amount <= 0}disabled{/if} />
    </div>
    <button type="button" class="btn btn-primary btn-block" id="mollie-initiate-capture" {if $isCaptured || $capturable_amount <= 0}disabled{/if}>
      <i class="icon-money"></i> {l s='Initiate Capture' mod='mollie'}
    </button>
    {/if}
    <hr />
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
          {if isset($product.name)}
            <tr>
              <td><strong>{$product.quantity}x</strong> {$product.name|escape:'html':'UTF-8'}</td>
              <td>{$product.price_formatted|escape:'html':'UTF-8'}</td>
              <td>
              {if $mollie_api_type == 'orders' && $product.name != 'Discount' && $product.name != 'Shipping'}
                <button type="button" class="btn btn-default btn-xs mollie-ship-btn" data-price="{$product.price}" data-product="{$product.id}" {if $product.isShipped || $product.price > $refundable_amount}disabled{/if}>
                  <i class="icon-truck"></i> {l s='Ship' mod='mollie'}
                </button>
              {/if}
              {if $product.name != 'Discount' && $product.name != 'Shipping'}
                <button type="button" class="btn btn-default btn-xs mollie-refund-btn" data-price="{$product.price}" data-product="{$product.id}" {if $product.isRefunded || $product.price > $refundable_amount}disabled{/if}>
                  <i class="icon-ban"></i> {l s='Refund' mod='mollie'}
                </button>
              {/if}
              </td>
            </tr>
          {/if}
        {/foreach}
      </tbody>
    </table>
    {if $mollie_api_type == 'orders'}
      <button type="button" class="btn btn-default btn-block" id="mollie-ship-all" {if $isShipped}disabled{/if}>
        <i class="icon-truck"></i> {l s='Ship All' mod='mollie'}
      </button>
    {else}
      <button type="button" class="btn btn-default btn-block" id="mollie-capture-all" {if $isCaptured || $isRefunded || $capturable_amount <= 0}disabled{/if}>
        <i class="icon-money"></i> {l s='Capture All' mod='mollie'}
      </button>
    {/if}
  </div>
</div>

{include file="module:mollie/views/templates/hook/partials/modal_refund.tpl"}
{include file="module:mollie/views/templates/hook/partials/modal_ship.tpl"}
{include file="module:mollie/views/templates/hook/partials/modal_capture.tpl"}
