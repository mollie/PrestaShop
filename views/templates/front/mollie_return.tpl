{**
* Mollie       https://www.mollie.nl
*
* @author      Mollie B.V. <info@mollie.nl>
* @copyright   Mollie B.V.
* @link        https://github.com/mollie/PrestaShop
* @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
*}
<h2>{l s='Welcome back' mod='mollie'}</h2>
<p>{$msg_details|escape:'htmlall':'UTF-8'}</p>
<a class="btn btn-default" href="{$link->getPageLink('index', true)|escape:'htmlall':'UTF-8'}">
  <i class="icon icon-chevron-left"></i> {l s='Continue shopping' mod='mollie'}
</a>
