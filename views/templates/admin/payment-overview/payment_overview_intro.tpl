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


<div class="alert alert-info">
  {l s='These payment attempts never became an order. Nothing here needs action: the list is here so you can answer a customer asking what happened to their payment, without opening the Mollie dashboard.' mod='mollie'}
  {l s='Attempts still waiting after %d hours are shown as abandoned.' sprintf=[$payment_overview_grace_hours] mod='mollie'}
</div>
