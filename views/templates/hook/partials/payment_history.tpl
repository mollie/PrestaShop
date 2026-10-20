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
{assign var=history_icons value=['created' => 'add', 'authorized' => 'lock', 'paid' => 'check', 'captured' => 'credit_card', 'refunded' => 'replay', 'chargeback' => 'report', 'chargeback_reversed' => 'undo', 'canceled' => 'close', 'expired' => 'schedule', 'failed' => 'close']}
<hr />
<details class="mollie-payment-history" open>
  <summary class="mollie-payment-history__summary">
    <i class="material-icons mollie-payment-history__icon">history</i>
    <span class="mollie-payment-history__title">{l s='Payment history' mod='mollie'}</span>
    <span class="mollie-payment-history__count">{if $payment_history|count == 1}{l s='1 event' mod='mollie'}{else}{l s='%s events' sprintf=[$payment_history|count] mod='mollie'}{/if}</span>
    {if $history_only && $payment_status}
    <span class="mollie-status mollie-status--{$payment_status|escape:'html':'UTF-8'}">{if isset($status_labels[$payment_status])}{$status_labels[$payment_status]|escape:'html':'UTF-8'}{else}{$payment_status|escape:'html':'UTF-8'}{/if}</span>
    {/if}
    <i class="material-icons mollie-payment-history__chevron">expand_less</i>
  </summary>
  {if empty($payment_history)}
  <p class="mollie-timeline__empty">{l s='No events yet' mod='mollie'}</p>
  {else}
  <ol class="mollie-timeline">
    {foreach from=$payment_history item=event}
    <li class="mollie-timeline__item mollie-timeline__item--{$event.type|escape:'html':'UTF-8'}">
      <span class="mollie-timeline__disc"><i class="material-icons mollie-timeline__icon">{if isset($history_icons[$event.type])}{$history_icons[$event.type]|escape:'html':'UTF-8'}{else}info{/if}</i></span>
      <details class="mollie-timeline__event">
        <summary class="mollie-timeline__row">
          <span class="mollie-timeline__label">{if isset($history_labels[$event.type])}{$history_labels[$event.type]|escape:'html':'UTF-8'}{else}{$event.type|escape:'html':'UTF-8'}{/if}</span>
          {if $event.status}
          <span class="mollie-status mollie-status--{$event.status|escape:'html':'UTF-8'}">{if isset($status_labels[$event.status])}{$status_labels[$event.status]|escape:'html':'UTF-8'}{else}{$event.status|escape:'html':'UTF-8'}{/if}</span>
          {/if}
          {if $event.type == 'authorized'}
          {foreach from=$event.details item=detail}
          {if $detail.label == 'capture_before'}
          <span class="mollie-status mollie-status--authorized">{$history_detail_labels.capture_before|escape:'html':'UTF-8'} {$detail.value|escape:'html':'UTF-8'}</span>
          {/if}
          {/foreach}
          {/if}
          <span class="mollie-timeline__date">{$event.date|escape:'html':'UTF-8'}</span>
          <span class="mollie-timeline__meta">
            {if $event.amount_formatted}
            <span class="mollie-timeline__amount{if $event.negative} mollie-timeline__amount--negative{/if}">{$event.amount_formatted|escape:'html':'UTF-8'}</span>
            {/if}
            <i class="material-icons mollie-timeline__chevron">expand_more</i>
          </span>
        </summary>
        {if $event.details || $event.reference}
        <dl class="mollie-timeline__details">
          {if $event.reference}
          <dt>{if $event.type == 'refunded'}{$history_labels.refunded|escape:'html':'UTF-8'}{elseif $event.type == 'captured'}{$history_labels.captured|escape:'html':'UTF-8'}{elseif $event.type == 'chargeback' || $event.type == 'chargeback_reversed'}{$history_labels.chargeback|escape:'html':'UTF-8'}{else}{l s='Mollie reference' mod='mollie'}{/if}</dt>
          <dd><code>{$event.reference|escape:'html':'UTF-8'}</code></dd>
          {/if}
          {foreach from=$event.details item=detail}
          {if $event.type != 'authorized' || $detail.label != 'capture_before'}
          <dt>{if isset($history_detail_labels[$detail.label])}{$history_detail_labels[$detail.label]|escape:'html':'UTF-8'}{else}{$detail.label|escape:'html':'UTF-8'}{/if}</dt>
          <dd>{$detail.value|escape:'html':'UTF-8'}</dd>
          {/if}
          {/foreach}
        </dl>
        {/if}
      </details>
    </li>
    {/foreach}
  </ol>
  {/if}
</details>
