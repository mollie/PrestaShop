{**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 *
 * @see        https://github.com/mollie/PrestaShop
 * @codingStandardsIgnoreStart
 *
 *}


{if $payment_order_id}
  <a href="{$payment_order_url|escape:'html':'UTF-8'}">{$payment_order_reference|escape:'htmlall':'UTF-8'}</a>
{else}
  <span class="text-muted">{l s='None' mod='mollie'}</span>
{/if}
