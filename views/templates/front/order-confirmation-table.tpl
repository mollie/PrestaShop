{**
* Copyright (c) 2012-2020, Mollie B.V.
* All rights reserved.
*
* Redistribution and use in source and binary forms, with or without
* modification, are permitted provided that the following conditions are met:
*
* - Redistributions of source code must retain the above copyright notice,
*    this list of conditions and the following disclaimer.
* - Redistributions in binary form must reproduce the above copyright
*    notice, this list of conditions and the following disclaimer in the
*    documentation and/or other materials provided with the distribution.
*
* THIS SOFTWARE IS PROVIDED BY THE AUTHOR AND CONTRIBUTORS ``AS IS'' AND ANY
* EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
* WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
* DISCLAIMED. IN NO EVENT SHALL THE AUTHOR OR CONTRIBUTORS BE LIABLE FOR ANY
* DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
* (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
* SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
* CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
* LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
* OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH
* DAMAGE.
*
* @author     Mollie B.V. <info@mollie.nl>
* @copyright  Mollie B.V.
* @license    Berkeley Software Distribution License (BSD-License 2) http://www.opensource.org/licenses/bsd-license.php
* @category   Mollie
* @package    Mollie
* @link       https://www.mollie.nl
*}
{extends file='checkout/_partials/order-confirmation-table.tpl'}
{block name='order_confirmation_table'}
    {foreach from=$products item=product}
        <div class="order-line row">
            <div class="col-sm-2 col-xs-3">
            <span class="image">
              <img src="{$product.cover.medium.url}"/>
            </span>
            </div>
            <div class="col-sm-4 col-xs-9 details">
                {if $add_product_link}<a href="{$product.url}" target="_blank">{/if}
                    <span>{$product.name}</span>
                    {if $add_product_link}</a>{/if}
                {if is_array($product.customizations) && $product.customizations|count}
                    {foreach from=$product.customizations item="customization"}
                        <div class="customizations">
                            <a href="#" data-toggle="modal"
                               data-target="#product-customizations-modal-{$customization.id_customization}">{l s='Product customization' d='Shop.Theme.Catalog'}</a>
                        </div>
                        <div class="modal fade customization-modal"
                             id="product-customizations-modal-{$customization.id_customization}" tabindex="-1"
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
                                                    {$field.label}
                                                </div>
                                                <div class="col-sm-9 col-xs-8 value">
                                                    {if $field.type == 'text'}
                                                        {if $field.id_module}
                                                            {$field.text nofilter}
                                                        {else}
                                                            {$field.text}
                                                        {/if}
                                                    {elseif $field.type == 'image'}
                                                        <img src="{$field.image.small.url}">
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
                    <div class="col-xs-4 text-sm-center text-xs-left">{$product.price}</div>
                    <div class="col-xs-4 text-sm-center">{$product.quantity}</div>
                    <div class="col-xs-4 text-sm-center text-xs-right bold">{$product.total}</div>
                </div>
            </div>
        </div>
    {/foreach}
    <hr>
    <table>
        <tr>
            <td>{l s='Payment Fee' mod='mollie'}</td>
            <td>{$payment_fee}</td>
        </tr>
        {foreach $subtotals as $subtotal}
            {if $subtotal.type !== 'tax' && $subtotal.label !== null}
                <tr>
                    <td>{$subtotal.label}</td>
                    <td>{if 'discount' == $subtotal.type}-&nbsp;{/if}{$subtotal.value}</td>
                </tr>
            {/if}
        {/foreach}

        {if !$configuration.display_prices_tax_incl && $configuration.taxes_enabled}
            <tr>
                <td><span class="text-uppercase">{$totals.total.label}&nbsp;{$labels.tax_short}</span></td>
                <td>{$totals.total.value}</td>
            </tr>
            <tr class="total-value font-weight-bold">
                <td><span class="text-uppercase">{$totals.total_including_tax.label}</span></td>
                <td>{$totals.total_including_tax.value}</td>
            </tr>
        {else}
            <tr class="total-value font-weight-bold">
                <td>
                    <span class="text-uppercase">{$totals.total.label}&nbsp;{if $configuration.taxes_enabled}{$labels.tax_short}{/if}</span>
                </td>
                <td>{$totals.total.value}</td>
            </tr>
        {/if}
        {if $subtotals.tax.label !== null}
            <tr class="sub taxes">
                <td>
                    <span class="label">{l s='%label%:' sprintf=['%label%' => $subtotals.tax.label] d='Shop.Theme.Global'}</span>&nbsp;<span
                            class="value">{$subtotals.tax.value}</span></td>
            </tr>
        {/if}
    </table>
{/block}
