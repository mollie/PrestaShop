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
          <input type="radio" name="refund_type" value="partial" id="mollie-partial-refund" {if $isRefunded}disabled{/if} />
          {l s='Partial refund' mod='mollie'}
        </label>
        <span class="help-block">{l s='Refund a custom amount for partial order returns' mod='mollie'}</span>
      </div>
      <div class="radio">
        <label>
          <input type="radio" name="refund_type" value="full" id="mollie-full-refund" checked {if $isRefunded}disabled{/if} />
          {l s='Full refund' mod='mollie'}
        </label>
        <span class="help-block">{l s='Refund a custom amount for partial order returns' mod='mollie'}</span>
      </div>
    </div>
    <div class="form-group">
      <label for="mollie-refund-amount">{l s='Refund amount (Max: %s, left: %s)' sprintf=[$max_refund_amount, $max_refund_amount - $refunded_amount] mod='mollie'}</label>
      <input type="number" step="0.01" max="{$max_refund_amount}" class="form-control" id="mollie-refund-amount" value="{$max_refund_amount}" {if $isRefunded}disabled{/if} />
    </div>
    <button type="button" class="btn btn-primary btn-block" id="mollie-initiate-refund" {if $isRefunded}disabled{/if}>
      <i class="icon-undo"></i> {l s='Initiate Refund' mod='mollie'}
    </button>
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
              <td>{$product.price|escape:'html':'UTF-8'}</td>
              <td>
                {if $mollie_api_type == 'orders'}
                  <button type="button" class="btn btn-default btn-xs mollie-ship-btn" data-price="{$product.price}" data-product="{$product.id}" {if $product.isShipped}disabled{/if}>
                    <i class="icon-truck"></i> {l s='Ship' mod='mollie'}
                  </button>
                {else}
                  <button type="button" class="btn btn-default btn-xs mollie-capture-btn" data-price="{$product.price}" data-product="{$product.id}" {if $product.isRefunded || $product.isCaptured}disabled{/if}>
                    <i class="icon-money"></i> {l s='Capture' mod='mollie'}
                  </button>
                {/if}
                <button type="button" class="btn btn-default btn-xs mollie-refund-btn" data-price="{$product.price}" data-product="{$product.id}" {if $product.isRefunded}disabled{/if}>
                  <i class="icon-undo"></i> {l s='Refund' mod='mollie'}
                </button>
              </td>
            </tr>
          {/if}
        {/foreach}
      </tbody>
    </table>
    {if $mollie_api_type == 'orders'}
      <button type="button" class="btn btn-default btn-block" id="mollie-ship-all">
        <i class="icon-truck"></i> {l s='Ship All' mod='mollie'}
      </button>
    {else}
      <button type="button" class="btn btn-default btn-block" id="mollie-capture-all" {if $isCaptured || $isRefunded}disabled{/if}>
        <i class="icon-money"></i> {l s='Capture All' mod='mollie'}
      </button>
    {/if}
  </div>
</div>

{include file="module:mollie/views/templates/hook/partials/modal_refund.tpl"}
{include file="module:mollie/views/templates/hook/partials/modal_ship.tpl"}
{include file="module:mollie/views/templates/hook/partials/modal_capture.tpl"}
