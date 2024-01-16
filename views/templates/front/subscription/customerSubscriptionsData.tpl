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
{extends file='customer/page.tpl'}

{block name='page_title'}
    {l s='Subscriptons' mod='mollie'}
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
                <th>{l s='Total price' d='Shop.Theme.Checkout'}</th>
                <th>{l s='Date created' d='Shop.Theme.Checkout'}</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            {foreach from=$recurringOrdersData item=recurringOrder}
                <tr>
                    <th scope="row">{$recurringOrder.recurring_order->id}</th>
                    <td>{$recurringOrder.recurring_order->status}</td>
                    <td>{$recurringOrder.recurring_order->payment_method}</td>
                    <td>{$recurringOrder.product_name}</td>
                    <td>{$recurringOrder.total_price}</td>
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

