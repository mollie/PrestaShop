{**
* Copyright (c) 2012-2018, mollie-ui b.V.
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
{extends file="helpers/form/form.tpl"}

{block name="input"}
  {if $input.type === 'mollie-br'}
    <br>
  {elseif $input.type === 'mollie-warning'}
    {if version_compare($smarty.const._PS_VERSION_, '1.6.0.0', '<')}
      <div class="warn">{$input.message|escape:'htmlall':'UTF-8'}</div>
    {else}
      <div class="alert alert-warning">{$input.message|escape:'htmlall':'UTF-8'}</div>
    {/if}
  {elseif $input.type === 'mollie-methods'}
    <div id="{$input.name|escape:'htmlall':'UTF-8'}_container"></div>
    <input type="hidden" id="{$input.name|escape:'htmlall':'UTF-8'}" name="{$input.name|escape:'htmlall':'UTF-8'}">
    <script type="text/javascript">
      (function initMollieMethodsConfig() {
        if (typeof window.MollieModule === 'undefined'
          || typeof window.MollieModule.back === 'undefined'
          || typeof window.MollieModule.back.methodConfig === 'undefined'
        ) {
          return setTimeout(initMollieMethodsConfig, 100);
        }

        window.MollieModule.debug = {if Configuration::get(Mollie::MOLLIE_DISPLAY_ERRORS)}true{else}false{/if};
        window.MollieModule.back.methodConfig(
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
            thisPaymentMethodIsNotAvailableOnPaymentsApi: '{l s='This payment method is not available on the Payments API. Switch to the Orders API below in order to activate this method.' mod='mollie' js=1}',
            unableToLoadMethods: '{l s='Unable to load payment methods' mod='mollie' js=1}',
            retry: '{l s='Retry' mod='mollie' js=1}',
            error: '{l s='Error' mod='mollie' js=1}'
          }
        )
      }());
    </script>
  {elseif $input.type === 'mollie-h1'}
    <br>
    <h1>{$input.title|escape:'html':'UTF-8'}</h1>
  {elseif $input.type === 'mollie-h2'}
    <br>
    <h2>{$input.title|escape:'html':'UTF-8'}</h2>
  {elseif $input.type === 'mollie-h3'}
    <br>
    <h3>{$input.title|escape:'html':'UTF-8'}</h3>
  {elseif $input.type == 'mollie-carriers'}
    <div id="{$input.name|escape:'htmlall':'UTF-8'}_container"></div>
    <script type="text/javascript">
      (function () {
        function initMollieCarriers() {
          var source = document.getElementById('{$input.depends|escape:'javascript':'UTF-8'}');
          if (typeof source === 'undefined') {
            return setTimeout(initMollieCarriers, 100);
          }

          function initMollieCarrierConfig() {
            if (typeof window.MollieModule === 'undefined'
              || typeof window.MollieModule.back === 'undefined'
              || typeof window.MollieModule.back.carrierConfig === 'undefined'
            ) {
              return setTimeout(initMollieCarrierConfig, 100);
            }

            window.MollieModule.debug = {if Configuration::get(Mollie::MOLLIE_DISPLAY_ERRORS)}true{else}false{/if};
            window.MollieModule.back.carrierConfig(
              '{$input.name|escape:'javascript':'UTF-8'}',
              {
                legacy: {if version_compare($smarty.const._PS_VERSION_, '1.6.0.0', '<')}true{else}false{/if},
                ajaxEndpoint: '{$link->getAdminLink('AdminModules', true)|escape:'javascript':'UTF-8'}&configure=mollie&ajax=1&action=MollieCarrierConfig'
              },
              {
                name: '{l s='Name' mod='mollie' js=1}',
                urlSource: '{l s='URL Source' mod='mollie' js=1}',
                carrierUrl: '{l s='Carrier URL' mod='mollie' js=1}',
                customUrl: '{l s='Custom URL' mod='mollie' js=1}',
                module: '{l s='Module' mod='mollie' js=1}',
                doNotAutoShip: '{l s='Do not automatically ship' mod='mollie' js=1}',
                noTrackingInformation: '{l s='No tracking information' mod='mollie' js=1}',
                hereYouCanConfigureCarriers: '{l s='Here you can configure what information about the shipment is sent to Mollie' mod='mollie' js=1}',
                youCanUseTheFollowingVariables: '{l s='You can use the following variables for the Carrier URLs' mod='mollie' js=1}',
                shippingNumber: '{l s='Shipping number' mod='mollie' js=1}',
                invoiceCountryCode: '{l s='Billing country code' mod='mollie' js=1}',
                invoicePostcode: '{l s='Billing postcode' mod='mollie' js=1}',
                deliveryCountryCode: '{l s='Shipping country code' mod='mollie' js=1}',
                deliveryPostcode: '{l s='Shipping postcode' mod='mollie' js=1}',
                languageCode: '{l s='2-letter language code' mod='mollie' js=1}',
                unableToLoadCarriers: '{l s='Unable to load carrier list' mod='mollie' js=1}',
                retry: '{l s='Retry' mod='mollie' js=1}',
                error: '{l s='Error' mod='mollie' js=1}'
              }
            )
          }

          function checkInput (e) {
            var container = document.getElementById('{$input.name|escape:'javascript':'UTF-8'}_container');
            if (e && e.target && e.target.value && e.target.value === '{$input.depends_value|escape:'javascript':'UTF-8'}') {
              var input = document.getElementById('{$input.name|escape:'javascript':'UTF-8'}');
              if (input == null) {
                var newInput = document.createElement('DIV');
                newInput.id = '{$input.name|escape:'javascript':'UTF-8'}';
                container.appendChild(newInput);
                initMollieCarrierConfig();
              }
            } else {
              if (window.MollieModule && typeof window.MollieModule.unmountComponentAtNode === 'function') {
                window.MollieModule.unmountComponentAtNode(container);
                window.MollieModule.debug = {if Configuration::get(Mollie::MOLLIE_DISPLAY_ERRORS)}true{else}false{/if};
              }
              {if version_compare($smarty.const._PS_VERSION_, '1.6.0.0', '<')}
              container.innerHTML = '<div class="info">{l s='This option is not required for the currently selected API' mod='mollie' js=1}</div>';
              {else}
              container.innerHTML = '<div class="alert alert-info">{l s='This option is not required for the currently selected API' mod='mollie' js=1}</div>';
              {/if}
            }
          }

          source.addEventListener('change', checkInput);
          checkInput({
            target: {
              value: document.getElementById('{$input.depends|escape:'javascript':'UTF-8'}').value
            }
          });
        }

        initMollieCarriers();
      }());
    </script>
  {elseif $input.type == 'mollie-carrier-switch'}
    <div id="{$input.name|escape:'htmlall':'UTF-8'}_info" style="display: none" class="{if version_compare($smarty.const._PS_VERSION_, '1.6.0.0', '<')}info{else}alert alert-info{/if}">{l s='This option is not required for the currently selected API' mod='mollie'}</div>
    <div id="{$input.name|escape:'htmlall':'UTF-8'}_container">
      {if version_compare($smarty.const._PS_VERSION_, '1.6.0.0', '<')}
        {foreach $input.values as $value}
          <input
            type="radio"
            name="{$input.name|escape:'htmlall':'UTF-8'}"
            id="{$input.name|escape:'htmlall':'UTF-8'}_{$value.id|escape:'htmlall':'UTF-8'}"
            value="{$value.value|escape:'htmlall':'UTF-8'}"
            {if $fields_value[$input.name] == $value.value}checked="checked"{/if}
            {if isset($input.disabled) && $input.disabled}disabled="disabled"{/if}
          >
          <label class="t" for="{$input.name|escape:'htmlall':'UTF-8'}_{$value.id|escape:'htmlall':'UTF-8'}">
            {if isset($input.is_bool) && $input.is_bool == true}
              {if $value.value == 1}
                <img
                  src="../img/admin/enabled.gif"
                  alt="{$value.label|escape:'htmlall':'UTF-8'}"
                  title="{$value.label|escape:'htmlall':'UTF-8'}"
                />
              {else}
                <img
                  src="../img/admin/disabled.gif"
                  alt="{$value.label|escape:'htmlall':'UTF-8'}"
                  title="{$value.label|escape:'htmlall':'UTF-8'}"
                />
              {/if}
            {else}
              {$value.label|escape:'htmlall':'UTF-8'}
            {/if}
          </label>
          {if isset($input.br) && $input.br}<br />{/if}
          {if isset($value.p) && $value.p}<p>{$value.p|escape:'htmlall':'UTF-8'}</p>{/if}
        {/foreach}
      {else}
        <span class="switch prestashop-switch fixed-width-lg">
          {foreach $input.values as $value}
            <input
              type="radio"
              name="{$input.name|escape:'htmlall':'UTF-8'}"{if $value.value == 1}
              id="{$input.name|escape:'htmlall':'UTF-8'}_on"{else}id="{$input.name|escape:'htmlall':'UTF-8'}_off"{/if}
              value="{$value.value|escape:'htmlall':'UTF-8'}"
              {if $fields_value[$input.name] == $value.value}checked="checked"{/if}
              {if isset($input.disabled) && $input.disabled}disabled="disabled"{/if}
            />
          {strip}
            <label {if $value.value == 1} for="{$input.name|escape:'htmlall':'UTF-8'}_on"{else} for="{$input.name|escape:'htmlall':'UTF-8'}_off"{/if}>
            {if $value.value == 1}
              {l s='Yes' mod='mollie'}
            {else}
              {l s='No' mod='mollie'}
            {/if}
          </label>
          {/strip}
          {/foreach}
          <a class="slide-button btn"></a>
        </span>
      {/if}
    </div>
    <script type="text/javascript">
      (function () {
        function initMollieCarriersAuto() {
          var source = document.getElementById('{$input.depends|escape:'javascript':'UTF-8'}');
          if (typeof source === 'undefined') {
            return setTimeout(initMollieCarriersAuto, 100);
          }

          function checkInput (e) {
            var container = document.getElementById('{$input.name|escape:'javascript':'UTF-8'}_container');
            var info = document.getElementById('{$input.name|escape:'javascript':'UTF-8'}_info');
            if (e && e.target && e.target.value && e.target.value === '{$input.depends_value|escape:'javascript':'UTF-8'}') {
              container.style.display = 'block';
              info.style.display = 'none';
            } else {
              container.style.display = 'none';
              info.style.display = 'block';
            }
          }

          source.addEventListener('change', checkInput);
          checkInput({
            target: {
              value: document.getElementById('{$input.depends|escape:'javascript':'UTF-8'}').value
            }
          });
        }

        initMollieCarriersAuto();
      }());
    </script>
  {elseif $input.type == 'switch' && version_compare($smarty.const._PS_VERSION_, '1.6.0.0', '<')}
    {foreach $input.values as $value}
      <input
        type="radio"
        name="{$input.name|escape:'htmlall':'UTF-8'}"
        id="{$input.name|escape:'htmlall':'UTF-8'}_{$value.id|escape:'htmlall':'UTF-8'}"
        value="{$value.value|escape:'htmlall':'UTF-8'}"
        {if $fields_value[$input.name] == $value.value}checked="checked"{/if}
        {if isset($input.disabled) && $input.disabled}disabled="disabled"{/if}
      >
      <label class="t" for="{$input.name|escape:'htmlall':'UTF-8'}_{$value.id|escape:'htmlall':'UTF-8'}">
        {if isset($input.is_bool) && $input.is_bool == true}
          {if $value.value == 1}
            <img
              src="../img/admin/enabled.gif"
              alt="{$value.label|escape:'htmlall':'UTF-8'}"
              title="{$value.label|escape:'htmlall':'UTF-8'}" />
          {else}
            <img
              src="../img/admin/disabled.gif"
              alt="{$value.label|escape:'htmlall':'UTF-8'}"
              title="{$value.label|escape:'htmlall':'UTF-8'}"
            />
          {/if}
        {else}
          {$value.label|escape:'htmlall':'UTF-8'}
        {/if}
      </label>
      {if isset($input.br) && $input.br}<br />{/if}
      {if isset($value.p) && $value.p}<p>{$value.p|escape:'htmlall':'UTF-8'}</p>{/if}
    {/foreach}
  {elseif $input.type === 'checkbox'}
    <div id="{$input.name|escape:'htmlall':'UTF-8'}_info" style="display: none" class="{if version_compare($smarty.const._PS_VERSION_, '1.6.0.0', '<')}info{else}alert alert-info{/if}">{l s='This option is not required for the currently selected API' mod='mollie'}</div>
    <div id="{$input.name|escape:'htmlall':'UTF-8'}_container">
        {$smarty.block.parent}
    </div>
    <script type="text/javascript">
      (function () {
        function initMollieCheckboxAuto() {
          var source = document.getElementById('{$input.depends|escape:'javascript':'UTF-8'}');
          if (typeof source === 'undefined') {
            return setTimeout(initMollieCheckboxAuto, 100);
          }

          function checkInput (e) {
            var container = document.getElementById('{$input.name|escape:'javascript':'UTF-8'}_container');
            var info = document.getElementById('{$input.name|escape:'javascript':'UTF-8'}_info');
            if (e && e.target && e.target.value && e.target.value === '{$input.depends_value|escape:'javascript':'UTF-8'}') {
              container.style.display = 'block';
              info.style.display = 'none';
            } else {
              container.style.display = 'none';
              info.style.display = 'block';
            }
          }

          source.addEventListener('change', checkInput);
          checkInput({
            target: {
              value: document.getElementById('{$input.depends|escape:'javascript':'UTF-8'}').value
            }
          });
        }

        initMollieCheckboxAuto();
      }());
    </script>
  {else}
    {$smarty.block.parent}
  {/if}
{/block}
