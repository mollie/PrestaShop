{*
 * NOTICE OF LICENSE
 *
 * @author    INVERTUS, UAB www.invertus.eu <support@invertus.eu>
 * @copyright Copyright (c) permanent, INVERTUS, UAB
 * @license   Addons PrestaShop license limitation
 * @see       /LICENSE
 *
 * International Registered Trademark & Property of INVERTUS, UAB
 *
 *}

<div class="mollie-icon-container" data-id-order="{$idOrder|intval}">
    <img class="resend-payment-mail-mollie" src="{$orderListIcon}" width="26" height="26" data-id-order="{$idOrder|intval}"/>
    <div class="mollie-message-container">{$message|escape:'html':'UTF-8'}</div>
</div>
