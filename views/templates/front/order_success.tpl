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
{extends file='checkout/order-confirmation.tpl'}
{block name='order_confirmation_table'}
    {include
    file=$link
    products=$order.products
    subtotals=$order.subtotals
    totals=$order.totals
    labels=$order.labels
    add_product_link=false
    }
{/block}
