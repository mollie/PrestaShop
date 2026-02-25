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
<h2>{if isset($msg_title)}{$msg_title|escape:'htmlall':'UTF-8'}{else}{l s='Welcome back' mod='mollie'}{/if}</h2>
<p>{$msg_details|escape:'htmlall':'UTF-8'}</p>
<a class="btn btn-default" href="{$link->getPageLink('index', true)|escape:'htmlall':'UTF-8'}">
  <i class="icon icon-chevron-left"></i> {l s='Continue shopping' mod='mollie'}
</a>
