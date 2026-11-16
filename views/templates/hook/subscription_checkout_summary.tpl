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
<div class="mollie-subscription-summary alert alert-info" role="note">
  <p class="mollie-subscription-summary__title"><strong>{l s='This order starts a subscription' mod='mollie'}</strong></p>
  <p class="mollie-subscription-summary__text">
    {if $mollieSubscriptionInterval == '1 day'}
      {l s='After today\'s payment, %amount% will be charged every day, delivery included, starting on %date%.' sprintf=['%amount%' => $mollieSubscriptionAmount, '%date%' => $mollieSubscriptionStartDate] mod='mollie'}
    {elseif $mollieSubscriptionInterval == '1 week'}
      {l s='After today\'s payment, %amount% will be charged every week, delivery included, starting on %date%.' sprintf=['%amount%' => $mollieSubscriptionAmount, '%date%' => $mollieSubscriptionStartDate] mod='mollie'}
    {elseif $mollieSubscriptionInterval == '3 months'}
      {l s='After today\'s payment, %amount% will be charged every 3 months, delivery included, starting on %date%.' sprintf=['%amount%' => $mollieSubscriptionAmount, '%date%' => $mollieSubscriptionStartDate] mod='mollie'}
    {elseif $mollieSubscriptionInterval == '12 months'}
      {l s='After today\'s payment, %amount% will be charged every year, delivery included, starting on %date%.' sprintf=['%amount%' => $mollieSubscriptionAmount, '%date%' => $mollieSubscriptionStartDate] mod='mollie'}
    {else}
      {l s='After today\'s payment, %amount% will be charged every month, delivery included, starting on %date%.' sprintf=['%amount%' => $mollieSubscriptionAmount, '%date%' => $mollieSubscriptionStartDate] mod='mollie'}
    {/if}
  </p>
  <p class="mollie-subscription-summary__note">{l s='You can cancel the subscription at any time from your account.' mod='mollie'}</p>
</div>
