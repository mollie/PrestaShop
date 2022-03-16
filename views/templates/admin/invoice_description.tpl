{**
* Mollie       https://www.mollie.nl
*
* @author      Mollie B.V. <info@mollie.nl>
* @copyright   Mollie B.V.
* @link        https://github.com/mollie/PrestaShop
* @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
*}
<p>
    {l s='When do you want the invoice for Klarna Payments?' mod='mollie'}
</p>
<p class="help-block">
    {l s='Default: Invoice creation is based on order settings > statuses. Custom status is not created.' mod='mollie'}
</p>
<p class="help-block">
    {l s='On Authorize: Create a full invoice when the order is authorized. Custom status is created.' mod='mollie'}
</p>
<p class="help-block">
    {l s='On Shipment: Create a full invoice when the order is shipped. Custom status is created.' mod='mollie'}
</p>
