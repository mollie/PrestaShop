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
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2020 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *}
{extends file='customer/page.tpl'}

{block name='page_title'}
    {l s='Mollie subscriptons' mod='psgdpr'}
{/block}


{block name='page_content'}
    <h6>{l s='Here are the orders you\'ve placed since your account was created.' d='Shop.Theme.Customeraccount'}</h6>

    {if $recurringOrdersData}
        <table class="table table-striped table-bordered table-labeled hidden-sm-down">
            <thead class="thead-default">
            <tr>
                <th>{l s='Subscription id' d='Shop.Theme.Checkout'}</th>
                <th>{l s='Status' d='Shop.Theme.Checkout'}</th>
                <th>{l s='Method' d='Shop.Theme.Checkout'}</th>
                <th>{l s='Product name' d='Shop.Theme.Checkout'}</th>
                <th>{l s='Price' d='Shop.Theme.Checkout'}</th>
                <th>{l s='Date add' d='Shop.Theme.Checkout'}</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            {foreach from=$recurringOrdersData item=recurringOrder}
                <tr>
                    <th scope="row">{$recurringOrder.recurring_order->id}</th>
                    <td>{$recurringOrder.recurring_order->status}</td>
                    <td>{$recurringOrder.recurring_order->payment_method}</td>
                    <td>{$recurringOrder.product->name}</td>
                    <td>{$recurringOrder.product_combination_price}</td>
                    <td>{$recurringOrder.recurring_order->date_add}</td>
                    <td class="text-sm-center order-actions">
                        <a href="{$recurringOrder.details_url}" data-link-action="view-order-details">
                            {l s='Details' d='Shop.Theme.Customeraccount'}
                        </a>
                    </td>
                </tr>
            {/foreach}
            </tbody>
        </table>

    {/if}
{/block}

