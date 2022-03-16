{**
* Mollie       https://www.mollie.nl
*
* @author      Mollie B.V. <info@mollie.nl>
* @copyright   Mollie B.V.
* @link        https://github.com/mollie/PrestaShop
* @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
*}
{capture name=path}
  <a href="{$link->getPageLink('order.php', true, null, ['step' => 3])|escape:'htmlall':'UTF-8'}">
    {l s='Payment' mod='mollie'}
  </a>
  <span class="navigation-pipe">
        {$navigationPipe|escape:'htmlall':'UTF-8'}
    </span>
  <span class="navigation_page">
        {l s='Payment error' mod='mollie'}
    </span>
{/capture}
<div class="alert alert-danger">
  <strong>{l s='An error occurred' mod='mollie'}:</strong>
  <ul>
    {foreach from=$errors item='error'}
      <li>{$error|escape:'htmlall':'UTF-8'}</li>
    {/foreach}
  </ul>
</div>
<ul class="footer_links clearfix">
  <li>
    <a class="btn btn-default button button-small" href="{$link->getPageLink('order.php', true, null, ['step' => 3])|escape:'htmlall':'UTF-8'}" title="{l s='Back to your shopping cart' mod='mollie'}">
      <span><i class="icon-chevron-left"></i> {l s='Back to your shopping cart' mod='mollie'}</span>
    </a>
  </li>
</ul>
