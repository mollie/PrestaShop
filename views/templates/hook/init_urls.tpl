{**
* Mollie       https://www.mollie.nl
*
* @author      Mollie B.V. <info@mollie.nl>
* @copyright   Mollie B.V.
* @link        https://github.com/mollie/PrestaShop
* @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
*}
<script type="text/javascript">
  (function () {
    window.MollieModule = window.MollieModule || {ldelim}{rdelim};
    window.MollieModule.urls = window.MollieModule.urls || {ldelim}{rdelim};
    window.MollieModule.urls.publicPath = '{$publicPath|escape:'javascript':'UTF-8'}';
    window.MollieModule.urls.qrCodeNew = '{$link->getModuleLink('mollie', 'qrcode', ['ajax' => '1', 'action' => 'qrCodeNew', 'rand' => time()], Tools::usingSecureMode())|escape:'javascript':'UTF-8'}';
    window.MollieModule.urls.cartAmount = '{$link->getModuleLink('mollie', 'qrcode', ['ajax' => '1', 'action' => 'cartAmount', 'rand' => time()], Tools::usingSecureMode())|escape:'javascript':'UTF-8'}';
    window.MollieModule.urls.qrCodeStatus = '{$link->getModuleLink('mollie', 'qrcode', ['ajax' => '1', 'action' => 'qrCodeStatus', 'rand' => time()], Tools::usingSecureMode())|escape:'javascript':'UTF-8'}';
    window.MollieModule.debug = {if display_errors}true{else}false{/if};
  }());
</script>
