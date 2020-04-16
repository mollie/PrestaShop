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
<script type="text/javascript">
  (function () {
    window.MollieModule = window.MollieModule || {ldelim}{rdelim};
    window.MollieModule.urls = window.MollieModule.urls || {ldelim}{rdelim};
    window.MollieModule.urls.publicPath = '{$publicPath|escape:'javascript':'UTF-8'}';
    window.MollieModule.debug = {if Configuration::get(Mollie\Config\Config::MOLLIE_DISPLAY_ERRORS)}true{else}false{/if};
  }());
  (function initMollieMethodsConfig() {
    if (typeof window.MollieModule === 'undefined'
            || typeof window.MollieModule.app === 'undefined'
            || typeof window.MollieModule.app.default === 'undefined'
            || typeof window.MollieModule.app.default.methodConfig === 'undefined'
    ) {
      return setTimeout(initMollieMethodsConfig, 100);
    }
    window.MollieModule.app.default.methodConfig().then(function (fn) {
      fn.default(
              '{$input.name|escape:'javascript':'UTF-8'}',
              {
                legacy: {if version_compare($smarty.const._PS_VERSION_, '1.6.0.0', '<')}true{else}false{/if},
                ajaxEndpoint: '{$link->getAdminLink('AdminModules', true)|escape:'javascript':'UTF-8'}&configure=mollie&ajax=1&action=MollieMethodConfig',
                moduleDir: '{$module_dir|escape:'javascript':'UTF-8'}'
              },
              {
                yes: '{l s='Yes' mod='mollie' js=1}',
                no: '{l s='No' mod='mollie' js=1}',
                notAvailable: '{l s='Not available' mod='mollie' js=1}',
                thisPaymentMethodIsNotAvailableOnPaymentsApi:
                        '{l s='This payment method is not available on the Payments API. Switch to the Orders API below in order to activate this method.' mod='mollie' js=1}',
                thisPaymentMethodNeedsSSLEnabled:
                        '{l s='Please enable SSL to use this payment method' mod='mollie' js=1}',
                unableToLoadMethods: '{l s='Unable to load payment methods' mod='mollie' js=1}',
                retry: '{l s='Retry' mod='mollie' js=1}',
                error: '{l s='Error' mod='mollie' js=1}'
              }
      );
    });
  }());
</script>