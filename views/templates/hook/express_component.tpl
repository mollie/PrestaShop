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
{* Mount point for the Mollie Express Component. A cart that can have a session shows it at once
   with a placeholder the buttons replace; otherwise it starts hidden and expressComponent.js
   reveals it once Mollie has rendered at least one wallet button. Placeholder styles are inline
   because a combined theme stylesheet can be served from a stale cache. On the cart page and
   above the personal information form Mollie collects the customer details, so those strips
   have no terms hint and no divider. *}
{assign var='mollieExpressIsPayment' value=($mollieExpressPlacement == 'payment')}
<link rel="preconnect" href="https://js.mollie.com">
<link rel="preconnect" href="https://sessions.mollie.com" crossorigin>
<div {if $mollieExpressIsPayment}id="mollie-express-component" {/if}class="mollie-express-component mollie-express-component--{$mollieExpressPlacement|escape:'html':'UTF-8'}"
     data-placement="{$mollieExpressPlacement|escape:'html':'UTF-8'}"
     data-amount="{$mollieExpressAmount|escape:'html':'UTF-8'}"
     data-currency="{$mollieExpressCurrency|escape:'html':'UTF-8'}"
     {if !$mollieExpressExpected}hidden{/if}>
  <p class="mollie-express-component__title">{l s='Express checkout' mod='mollie'}</p>
  {if $mollieExpressIsPayment}
    <p class="mollie-express-component__terms"{if !($mollieExpressExpected && $mollieExpressTermsRequired)} hidden{/if}>{l s='Accept the terms of service below to use express checkout.' mod='mollie'}</p>
  {/if}
  {if $mollieExpressExpected}
    <div class="mollie-express-component__placeholder" aria-hidden="true" style="height: 56px; border-radius: 6px; background: #eeeeee;"></div>
  {/if}
  <div class="mollie-express-component__mount"{if $mollieExpressExpected} style="height: 0; min-height: 0; overflow: hidden;"{/if}></div>
  <p class="mollie-express-component__error" hidden></p>
  {if $mollieExpressIsPayment}
    <p class="mollie-express-component__divider"><span>{l s='or pay with' mod='mollie'}</span></p>
  {/if}
</div>
