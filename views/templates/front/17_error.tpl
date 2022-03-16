{**
* Mollie       https://www.mollie.nl
*
* @author      Mollie B.V. <info@mollie.nl>
* @copyright   Mollie B.V.
* @link        https://github.com/mollie/PrestaShop
* @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
*}
{extends "page.tpl"}
{block name="page_content"}
  <a class="btn btn-primary button button-small" href="{$link->getPageLink('order.php', true, null, ['step' => 3])|escape:'htmlall':'UTF-8'}" title="{l s='Back to your shopping cart' mod='mollie'}">
    <span><i class="material-icons">arrow_back</i> {l s='Back to your shopping cart' mod='mollie'}</span>
  </a>
{/block}
