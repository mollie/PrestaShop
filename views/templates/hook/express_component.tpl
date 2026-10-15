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
{* Mount point for the Mollie Express Component. Starts hidden and is revealed by
   expressComponent.js once Mollie has rendered at least one wallet button. *}
<div id="mollie-express-component"
     class="mollie-express-component"
     data-amount="{$mollieExpressAmount|escape:'html':'UTF-8'}"
     data-currency="{$mollieExpressCurrency|escape:'html':'UTF-8'}"
     hidden>
  <p class="mollie-express-component__title">{l s='Express checkout' mod='mollie'}</p>
  <p class="mollie-express-component__terms" hidden>{l s='Accept the terms of service below to use express checkout.' mod='mollie'}</p>
  <div class="mollie-express-component__mount"></div>
  <p class="mollie-express-component__error" hidden></p>
  <p class="mollie-express-component__divider"><span>{l s='or pay with' mod='mollie'}</span></p>
</div>
