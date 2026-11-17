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
{* On the cart page the express strip may bring its own Apple Pay button. Direct then waits hidden
   and expressComponent.js reveals it when the strip cannot show. *}
<div id="mollie-applepay-direct-button"{if !empty($mollieApplePayDirectStandby)} data-mollie-express-fallback hidden{/if}></div>
