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
      (function initMollieCarrierConfig() {
        if (typeof window.MollieModule === 'undefined'
          || typeof window.MollieModule.back === 'undefined'
          || typeof window.MollieModule.back.carrierConfig === 'undefined'
        ) {
          return setTimeout(initMollieCarrierConfig, 100);
        }

        window.MollieModule.back.carrierConfig(
          '{$input.name|escape:'javascript':'UTF-8'}',
          {
            carrierConfig: {$input.carrier_config|json_encode nofilter},
            legacy: {if version_compare($smarty.const._PS_VERSION_, '1.6.0.0', '<')}true{else}false{/if}
          },
          {
            name: '{l s='Name' mod='mollie' js=1}',
            urlSource: '{l s='URL Source' mod='mollie' js=1}',
            carrierUrl: '{l s='Carrier URL' mod='mollie' js=1}',
            customUrl: '{l s='Custom URL' mod='mollie' js=1}',
            module: '{l s='Module' mod='mollie' js=1}',
          }
        )
      }());
    </script>
  {elseif $input.type == 'switch' && version_compare($smarty.const._PS_VERSION_, '1.6.0.0', '<')}
    {foreach $input.values as $value}
      <input type="radio"
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
  {else}
    {$smarty.block.parent}
  {/if}
{/block}
