{**
* Copyright (c) 2012-2020, Mollie B.V.
* All rights reserved.
*
* Redistribution and use in source and binary forms, with or without
* modification, are permitted provided that the following conditions are met:
*
* - Redistributions of source code must retain the above copyright notice,
*    this list of conditions and the following disclaimer.
* - Redistributions in binary form must reproduce the above copyright
*    notice, this list of conditions and the following disclaimer in the
*    documentation and/or other materials provided with the distribution.
*
* THIS SOFTWARE IS PROVIDED BY THE AUTHOR AND CONTRIBUTORS ``AS IS'' AND ANY
* EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
* WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
* DISCLAIMED. IN NO EVENT SHALL THE AUTHOR OR CONTRIBUTORS BE LIABLE FOR ANY
* DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
* (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
* SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
* CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
* LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
* OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH
* DAMAGE.
*
* @author     Mollie B.V. <info@mollie.nl>
* @copyright  Mollie B.V.
* @license    Berkeley Software Distribution License (BSD-License 2) http://www.opensource.org/licenses/bsd-license.php
* @category   Mollie
* @package    Mollie
* @link       https://www.mollie.nl
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
