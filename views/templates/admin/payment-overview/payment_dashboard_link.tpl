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


{if $payment_dashboard_url}
  <a href="{$payment_dashboard_url|escape:'html':'UTF-8'}" target="_blank" rel="noopener noreferrer">
    {$payment_transaction_id|escape:'htmlall':'UTF-8'} <i class="icon-external-link"></i>
  </a>
{else}
  <span class="text-muted">{$payment_transaction_id|escape:'htmlall':'UTF-8'}</span>
{/if}
