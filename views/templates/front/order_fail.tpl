{**
* Mollie       https://www.mollie.nl
*
* @author      Mollie B.V. <info@mollie.nl>
* @copyright   Mollie B.V.
* @link        https://github.com/mollie/PrestaShop
* @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
*}
{extends file='checkout/order-confirmation.tpl'}
{block name='page_content_container'}
    <section id="content-hook_order_confirmation" class="card">
        <div class="card-block">
            <div class="row">
                <div class="col-md-12">
                    <h3 class="h1 card-title">
                        <i class="material-icons rtl-no-flip text-danger">cancel</i>{l s='Your order is canceled' mod='mollie'}
                    </h3>
                </div>
            </div>
        </div>
    </section>
{/block}
{block name='hook_order_confirmation'}
{/block}
{block name='hook_payment_return'}
{/block}
{block name='customer_registration_form'}
{/block}