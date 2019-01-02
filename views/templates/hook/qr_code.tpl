{**
* Copyright (c) 2012-2019, Mollie B.V.
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
{if Configuration::get(Mollie::MOLLIE_QRENABLED) && Mollie::selectedApi() === Mollie::MOLLIE_PAYMENTS_API}
  <script type="text/javascript">
    (function () {
      window.MollieModule = window.MollieModule || { };
      window.MollieModule.urls = window.MollieModule.urls || { };
      window.MollieModule.urls.qrCodeNew = '{$link->getModuleLink('mollie', 'qrcode', ['ajax' => '1', 'action' => 'qrCodeNew', 'rand' => time()], Tools::usingSecureMode())|escape:'javascript':'UTF-8' nofilter}';
      window.MollieModule.urls.cartAmount = '{$link->getModuleLink('mollie', 'qrcode', ['ajax' => '1', 'action' => 'cartAmount', 'rand' => time()], Tools::usingSecureMode())|escape:'javascript':'UTF-8' nofilter}';
      window.MollieModule.urls.qrCodeStatus = '{$link->getModuleLink('mollie', 'qrcode', ['ajax' => '1', 'action' => 'qrCodeStatus', 'rand' => time()], Tools::usingSecureMode())|escape:'javascript':'UTF-8' nofilter}';
    }());
  </script>
  <div id="mollie-qr-code"></div>
  <script type="text/javascript">
    (function () {
      var scripts = document.getElementsByTagName('script');
      var found = false;
      for (var i = scripts.length; i--;) {
        if (scripts[i].src && scripts[i].src.indexOf('{Mollie::getMediaPathForJavaScript('views/js/dist/front.min.js')|escape:'javascript':'UTF-8'}') > -1) {
          found = true;
          break;
        }
      }
      if (!found) {
        var newScript = document.createElement('SCRIPT');
        newScript.src = '{Mollie::getMediaPathForJavaScript('views/js/dist/front.min.js')|escape:'javascript':'UTF-8'}';
        newScript.type = 'text/javascript';
        document.head.appendChild(newScript);
      }
    }());
    (function initQrCode() {
      if (typeof window.MollieModule === 'undefined'
        || typeof window.MollieModule.front === 'undefined'
      ) {
        return setTimeout(initQrCode, 100);
      }

      new window.MollieModule.front.QrCode(document.getElementById('mollie-qr-code'), '{l s='or scan the iDEAL QR code' mod='mollie' js=1}', false);
    }());
  </script>
{/if}
