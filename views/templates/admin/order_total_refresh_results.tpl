{**
* Mollie       https://www.mollie.nl
*
* @author      Mollie B.V. <info@mollie.nl>
* @copyright   Mollie B.V.
* @link        https://github.com/mollie/PrestaShop
* @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
*}
<div class="form-group js-order-total-refresh-results" data-tab-id="general_settings">
    <div class="col-log-9">
        {if $refreshOrderTotalInfoStatus}
            <div>
                <i class="icon-check text-success"></i>
                <span class="text-success">
                    <b>
                       {l s='Successfully updated order total restriction values' mod='mollie'}
                    </b>
                </span>
            </div>
        {else}
            <div>
                <i class="icon-remove"></i>
                <span class="text-danger">
                    <b>
                        {$errorMessage|escape:'html':'UTF-8'}
                    </b>
                </span>
            </div>
        {/if}
    </div>
</div>
