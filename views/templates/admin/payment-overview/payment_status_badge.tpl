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


{if $payment_status == $payment_status_failed}
  <span class="badge badge-pill badge-danger">{$payment_status_label|escape:'htmlall':'UTF-8'}</span>
{elseif $payment_status == $payment_status_canceled}
  <span class="badge badge-pill badge-warning">{$payment_status_label|escape:'htmlall':'UTF-8'}</span>
{elseif $payment_status == $payment_status_expired}
  <span class="badge badge-pill badge-warning">{$payment_status_label|escape:'htmlall':'UTF-8'}</span>
{else}
  <span class="badge badge-pill badge-info">{$payment_status_label|escape:'htmlall':'UTF-8'}</span>
{/if}
