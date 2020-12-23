{**
* Mollie       https://www.mollie.nl
*
* @author      Mollie B.V. <info@mollie.nl>
* @copyright   Mollie B.V.
* @link        https://github.com/mollie/PrestaShop
* @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
* @codingStandardsIgnoreStart
*}
<table width="100%" id="body" border="0" cellpadding="0" cellspacing="0" style="margin:0;">
    <tr>
        <td colspan="6" class="left">
        </td>

        <td colspan="6" rowspan="6" class="right">
            <table id="payment-tab" width="100%" class="right">
                <tr class="bold">
                    <td class="grey" width="50%">
                        {l s='Payment Fee' mod='mollie'}
                    </td>
                    <td class="white" width="50%">
                        {$orderFeeAmountDisplay|escape:'html':'UTF-8'}
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
