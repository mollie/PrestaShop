{**
* Mollie       https://www.mollie.nl
*
* @author      Mollie B.V. <info@mollie.nl>
* @copyright   Mollie B.V.
* @link        https://github.com/mollie/PrestaShop
* @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
*}
<tr style="background-color: {$color|escape:'htmlall':'UTF-8'}">
    <td style="padding: 0.6em 0.4em;width: 15%;">{$product.reference|escape:'htmlall':'UTF-8'}</td>
    <td style="padding: 0.6em 0.4em;width: 30%;"><strong>{$product.name|escape:'htmlall':'UTF-8'}{$product.attributes|escape:'htmlall':'UTF-8'} - {$customizationText|escape:'htmlall':'UTF-8'}</strong></td>
    <td style="padding: 0.6em 0.4em; width: 20%;">{$price|escape:'htmlall':'UTF-8'}</td>
    <td style="padding: 0.6em 0.4em; width: 15%;">{$customizationQuantity|escape:'htmlall':'UTF-8'}</td>
    <td style="padding: 0.6em 0.4em; width: 20%;">{$fullPrice|escape:'htmlall':'UTF-8'}</td>
</tr>