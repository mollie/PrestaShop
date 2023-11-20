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
    {l s='Subscription order details' mod='mollie'}
{/block}

{block name='page_content'}
    {block name='order_infos'}
        <div id="order-infos">
        </div>
    {/block}

    {block name='addresses'}
        <div class="addresses">
            {if $recurringOrderData.order.addresses.delivery}
                <div class="col-lg-6 col-md-6 col-sm-6">
                    <article id="delivery-address" class="box">
                        <h4>{l s='Delivery address %alias%' d='Shop.Theme.Checkout' sprintf=['%alias%' => $recurringOrderData.order.addresses.delivery.alias]}</h4>
                        <address>{$recurringOrderData.order.addresses.delivery.formatted nofilter}</address>
                        <div class="address-footer">
                            <a href="{url entity=address id=$recurringOrderData.order.addresses.delivery.id}" data-link-action="edit-address">
                                <i class="material-icons">edit</i>
                                <span>{l s='Update' d='Shop.Theme.Actions'}</span>
                            </a>
                        </div>
                    </article>
                </div>
            {/if}

            <div class="col-lg-6 col-md-6 col-sm-6">
                <article id="invoice-address" class="box">
                    <h4>{l s='Invoice address %alias%' d='Shop.Theme.Checkout' sprintf=['%alias%' => $recurringOrderData.order.addresses.invoice.alias]}</h4>
                    <address>{$recurringOrderData.order.addresses.invoice.formatted nofilter}</address>
                    <div class="address-footer">
                        <a href="{url entity=address id=$recurringOrderData.order.addresses.invoice.id}" data-link-action="edit-address">
                            <i class="material-icons">edit</i>
                            <span>{l s='Update' d='Shop.Theme.Actions'}</span>
                        </a>
                    </div>
                </article>
            </div>
            <div class="clearfix"></div>
        </div>
    {/block}

    {include file='module:mollie/views/templates/front/subscription/customerRecurringOrderDetailProduct.tpl' order=$recurringOrderData.order_detail}

    {if $recurringOrderData.recurring_order->status !== 'canceled'}

        {block name='recurring_method'}
            <section class="recurring-method-form box">
                <form action="" method="post">
                    <header>
                        <h3>{l s='Update subscription payment method' mod='mollie'}</h3>
                        <p>{l s='If you would like to update the payment method of a subscription, you can select it here.' mod='mollie'}</p>
                    </header>
                    <section class="form-fields">
                        <div class="form-group row">
                            <label class="col-md-3 form-control-label">{l s='Payment method' d='Shop.Forms.Labels'}</label>
                            <div class="col-md-5">
                                <select name="payment_method" class="form-control form-control-select">
                                    {foreach from=$recurringOrderData.payment_methods item=method}
                                        <option value="{$method->id}" {if $method->id === $recurringOrderData.recurring_order->payment_method} selected {/if}>{$method->description}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                    </section>
                    <footer class="form-footer text-sm-center">
                        <input type="hidden" name="recurring_order_id"
                               value="{$recurringOrderData.recurring_order->id}">
                        <input type="hidden" name="token" value="{$token}">
                        <button type="submit" name="submitUpdatePaymentMethod"
                                class="btn btn-primary form-control-submit">
                            {l s='Update' mod='mollie'}
                        </button>
                    </footer>
                </form>
            </section>
        {/block}

        {block name='recurring_order_cancelation'}
            <section class="recurring-method-form box">
                <form action="" method="post">
                    <header>
                        <h3>{l s='Cancel subscription' mod='mollie'}</h3>
                    </header>
                    <footer class="form-footer text-sm-center">
                        <input type="hidden" name="recurring_order_id"
                               value="{$recurringOrderData.recurring_order->id}">
                        <input type="hidden" name="token" value="{$token}">
                        <button type="submit" name="submitCancelSubscriptionMethod"
                                class="btn btn-primary form-control-submit">
                            {l s='Cancel' mod='mollie'}
                        </button>
                    </footer>
                </form>
            </section>
        {/block}
    {/if}

{/block}
