{**
* Mollie       https://www.mollie.nl
*
* @author      Mollie B.V. <info@mollie.nl>
* @copyright   Mollie B.V.
* @link        https://github.com/mollie/PrestaShop
* @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
*}
{if qrCodeEnabled}
  <div id="mollie-qr-code"></div>
  <script type="text/javascript">
    (function () {
      window.MollieModule = window.MollieModule || { };
      window.MollieModule.urls = window.MollieModule.urls || { };
      window.MollieModule.urls.publicPath = '{$publicPath|escape:'javascript':'UTF-8'}';
      window.MollieModule.urls.qrCodeNew = '{$link->getModuleLink('mollie', 'qrcode', ['ajax' => '1', 'action' => 'qrCodeNew', 'rand' => time()], Tools::usingSecureMode())|escape:'javascript':'UTF-8'}';
      window.MollieModule.urls.cartAmount = '{$link->getModuleLink('mollie', 'qrcode', ['ajax' => '1', 'action' => 'cartAmount', 'rand' => time()], Tools::usingSecureMode())|escape:'javascript':'UTF-8'}';
      window.MollieModule.urls.qrCodeStatus = '{$link->getModuleLink('mollie', 'qrcode', ['ajax' => '1', 'action' => 'qrCodeStatus', 'rand' => time()], Tools::usingSecureMode())|escape:'javascript':'UTF-8'}';
    }());
    (function initQrCode() {
      if (typeof window.MollieModule === 'undefined'
        || typeof window.MollieModule.app === 'undefined'
        || typeof window.MollieModule.app.default === 'undefined'
        || typeof window.MollieModule.app.default.qrCode === 'undefined'
      ) {
        return setTimeout(initQrCode, 100);
      }

      window.MollieModule.app.qrCode().then(function (fn) {
        fn.default(
          document.getElementById('mollie-qr-code'),
          '{l s='or scan the iDEAL QR code' mod='mollie' js=1}',
          false
        );
      });
    }());
  </script>
{/if}
