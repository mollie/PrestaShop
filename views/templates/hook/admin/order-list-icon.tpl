{**
* Mollie       https://www.mollie.nl
*
* @author      Mollie B.V. <info@mollie.nl>
* @copyright   Mollie B.V.
* @link        https://github.com/mollie/PrestaShop
* @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
*}

<div class="mollie-icon-container" data-id-order="{$idOrder|intval}">
    <img class="resend-payment-mail-mollie" src="{$orderListIcon}" width="26" height="26" data-id-order="{$idOrder|intval}"/>
    <div class="mollie-message-container">{$message|escape:'html':'UTF-8'}</div>
</div>
