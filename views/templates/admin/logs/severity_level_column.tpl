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


{if $log_severity_level == $log_severity_level_informative}
  <span class="badge badge-pill badge-success" style="margin-bottom: 5px">{l s='Informative only' mod='mollie'} ({$log_severity_level|intval})</span>
{elseif $log_severity_level == $log_severity_level_warning}
  <span class="badge badge-pill badge-warning" style="margin-bottom: 5px">{l s='Warning' mod='mollie'} ({$log_severity_level|intval})</span>
{elseif $log_severity_level == $log_severity_level_error}
  <span class="badge badge-pill badge-danger" style="margin-bottom: 5px">{l s='Error' mod='mollie'} ({$log_severity_level|intval})</span>
{elseif $log_severity_level == $log_severity_level_major}
  <span class="badge badge-pill badge-critical" style="margin-bottom: 5px">{l s='Major issue (crash)!' mod='mollie'} ({$log_severity_level|intval})</span>
{else}
  <span class="badge badge-pill">{$log_severity_level|escape:'htmlall':'UTF-8'}</span>
{/if}
