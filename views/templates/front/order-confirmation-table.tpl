{**
 * 2007-2020 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2020 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *}
{extends file='checkout/_partials/order-confirmation-table.tpl'}
{block name='order_confirmation_table'}
    {foreach from=$products item=product}
        <div class="order-line row">
            <div class="col-sm-2 col-xs-3">
            <span class="image">
              <img src="{$product.cover.medium.url|escape:'html':'UTF-8'}"/>
            </span>
            </div>
            <div class="col-sm-4 col-xs-9 details">
                {if $add_product_link}<a href="{$product.url|escape:'html':'UTF-8'}" target="_blank">{/if}
                    <span>{$product.name|escape:'html':'UTF-8'}</span>
                    {if $add_product_link}</a>{/if}
                {if is_array($product.customizations) && $product.customizations|count}
                    {foreach from=$product.customizations item="customization"}
                        <div class="customizations">
                            <a href="#" data-toggle="modal"
                               data-target="#product-customizations-modal-{$customization.id_customization|intval}">{l s='Product customization' d='Shop.Theme.Catalog'}</a>
                        </div>
                        <div class="modal fade customization-modal"
                             id="product-customizations-modal-{$customization.id_customization|intval}" tabindex="-1"
                             role="dialog" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                        <h4 class="modal-title">{l s='Product customization' d='Shop.Theme.Catalog'}</h4>
                                    </div>
                                    <div class="modal-body">
                                        {foreach from=$customization.fields item="field"}
                                            <div class="product-customization-line row">
                                                <div class="col-sm-3 col-xs-4 label">
                                                    {$field.label|escape:'html':'UTF-8'}
                                                </div>
                                                <div class="col-sm-9 col-xs-8 value">
                                                    {if $field.type == 'text'}
                                                      {$field.text|escape:'html':'UTF-8'}
                                                    {elseif $field.type == 'image'}
                                                        <img src="{$field.image.small.url|escape:'html':'UTF-8'}">
                                                    {/if}
                                                </div>
                                            </div>
                                        {/foreach}
                                    </div>
                                </div>
                            </div>
                        </div>
                    {/foreach}
                {/if}
                {hook h='displayProductPriceBlock' product=$product type="unit_price"}
            </div>
            <div class="col-sm-6 col-xs-12 qty">
                <div class="row">
                    <div class="col-xs-4 text-sm-center text-xs-left">{$product.price|escape:'html':'UTF-8'}</div>
                    <div class="col-xs-4 text-sm-center">{$product.quantity|intval}</div>
                    <div class="col-xs-4 text-sm-center text-xs-right bold">{$product.total|escape:'html':'UTF-8'}</div>
                </div>
            </div>
        </div>
    {/foreach}
    <hr>
    <table>
        <tr>
            <td>{l s='Payment Fee' mod='mollie'}</td>
            <td>{$payment_fee|escape:'html':'UTF-8'}</td>
        </tr>
        {foreach $subtotals as $subtotal}
            {if $subtotal.type !== 'tax' && $subtotal.label !== null}
                <tr>
                    <td>{$subtotal.label|escape:'html':'UTF-8'}</td>
                    <td>{if 'discount' == $subtotal.type}-&nbsp;{/if}{$subtotal.value|escape:'html':'UTF-8'}</td>
                </tr>
            {/if}
        {/foreach}

        {if !$configuration.display_prices_tax_incl && $configuration.taxes_enabled}
            <tr>
                <td><span class="text-uppercase">{$totals.total.label|escape:'html':'UTF-8'}&nbsp;{$labels.tax_short|escape:'html':'UTF-8'}</span></td>
                <td>{$totals.total.value|escape:'html':'UTF-8'}</td>
            </tr>
            <tr class="total-value font-weight-bold">
                <td><span class="text-uppercase">{$totals.total_including_tax.label|escape:'html':'UTF-8'}</span></td>
                <td>{$totals.total_including_tax.value|escape:'html':'UTF-8'}</td>
            </tr>
        {else}
            <tr class="total-value font-weight-bold">
                <td>
                    <span class="text-uppercase">{$totals.total.label|escape:'html':'UTF-8'}&nbsp;{if $configuration.taxes_enabled}{$labels.tax_short|escape:'html':'UTF-8'}{/if}</span>
                </td>
                <td>{$totals.total.value|escape:'html':'UTF-8'}</td>
            </tr>
        {/if}
        {if $subtotals.tax.label !== null}
            <tr class="sub taxes">
                <td>
                    <span class="label">{l s='%label%:' sprintf=['%label%' => $subtotals.tax.label] d='Shop.Theme.Global'}</span>&nbsp;<span
                            class="value">{$subtotals.tax.value|escape:'html':'UTF-8'}</span></td>
            </tr>
        {/if}
    </table>
{/block}
