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
        <script type="text/javascript">
            (function () {
                window.MollieModule = window.MollieModule || {ldelim}{rdelim};
                window.MollieModule.urls = window.MollieModule.urls || {ldelim}{rdelim};
                window.MollieModule.urls.publicPath = '{$publicPath|escape:'javascript':'UTF-8'}';
                window.MollieModule.debug = {if Configuration::get(Mollie::MOLLIE_DISPLAY_ERRORS)}true{else}false{/if};
            }());
        </script>
        {foreach $input.paymentMethods as $paymentMethod}
            {assign var = 'methodObj' value=$paymentMethod.obj}
            <div data-tab-id="general_settings" class="payment-method border border-bottom">
                <a class="text collapsed" data-toggle="collapse" href="#payment-method-form-{$paymentMethod.id}"
                   role="button"
                   aria-expanded="true" aria-controls="#payment-method-form-{$paymentMethod.id}">
                    <svg class="bi bi-chevron-compact-up" width="1em" height="1em" viewBox="0 0 16 16"
                         fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd"
                              d="M7.776 5.553a.5.5 0 01.448 0l6 3a.5.5 0 11-.448.894L8 6.56 2.224 9.447a.5.5 0 11-.448-.894l6-3z"
                              clip-rule="evenodd"/>
                    </svg>
                    {l s=$paymentMethod.name mod='mollie'}
                </a>
                <div class="collapse multi-collapse" id="payment-method-form-{$paymentMethod.id}">
                    <div class="form-group">
                        <label class="control-label col-lg-3">
                            {l s='Enabled' mod='mollie'}
                        </label>
                        <div class="col-lg-9">
                            <select name="MOLLIE_METHOD_ENABLED_{$paymentMethod.id}" class="fixed-width-xl">
                                <option value="0" {if $methodObj->enabled === '0'} selected {/if}>{l s='No' mod='mollie'}</option>
                                <option value="1" {if $methodObj->enabled === '1'} selected {/if}>{l s='Yes' mod='mollie'}</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-lg-3">
                            {l s='Title' mod='mollie'}
                        </label>
                        <div class="col-lg-9">
                            <input type="text" name="MOLLIE_METHOD_TITLE_{$paymentMethod.id}" class="fixed-width-xl"
                                   value="{$methodObj->title}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-lg-3">
                            {l s='Method' mod='mollie'}
                        </label>
                        <div class="col-lg-9">
                            <select name="MOLLIE_METHOD_API_{$paymentMethod.id}" class="fixed-width-xl">
                                <option value="payments" {if $methodObj->method === 'payments'} selected {/if}>{l s='Payments API' mod='mollie'}</option>
                                <option value="orders" {if $methodObj->method === 'orders'} selected {/if}>{l s='Orders API' mod='mollie'}</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-lg-3">
                            {l s='Description' mod='mollie'}
                        </label>
                        <div class="col-lg-9">
                            <input type="text" name="MOLLIE_METHOD_DESCRIPTION_{$paymentMethod.id}"
                                   class="fixed-width-xl" value="{$methodObj->description}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-lg-3">
                            {l s='Payment of applicable countries' mod='mollie'}
                        </label>
                        <div class="col-lg-9">
                            <select name="MOLLIE_METHOD_APPLICABLE_COUNTRIES_{$paymentMethod.id}"
                                    class="fixed-width-xl">
                                <option value="0" {if $methodObj->method === '0'} selected {/if}>{l s='Selected countries' mod='mollie'}</option>
                                <option value="1" {if $methodObj->method === '1'} selected {/if}>{l s='All Allowed Countries' mod='mollie'}</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-lg-3">
                            {l s='Payment from certain countries' mod='mollie'}
                        </label>
                        <div class="col-lg-9">
                            <select name="MOLLIE_METHOD_CERTAIN_COUNTRIES_{$paymentMethod.id}[]"
                                    class="fixed-width-xl chosen" multiple="multiple">
                                {foreach $input.countries as $country}
                                    <option value="{$country.id}"
                                            {if {$country.id|in_array:$paymentMethod.countries}}selected{/if}>{$country.name}</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-lg-3">
                            {l s='Minimum order value' mod='mollie'}
                        </label>
                        <div class="col-lg-9">
                            <input type="text" name="MOLLIE_METHOD_MINIMUM_ORDER_VALUE_{$paymentMethod.id}"
                                   class="fixed-width-xl js-mollie-amount" value="{$methodObj->minimal_order_value}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-lg-3">
                            {l s='Maximum order value' mod='mollie'}
                        </label>
                        <div class="col-lg-9">
                            <input type="text" name="MOLLIE_METHOD_MAX_ORDER_VALUE_{$paymentMethod.id}"
                                   class="fixed-width-xl  js-mollie-amount" value="{$methodObj->max_order_value}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-lg-3">
                            {l s='Payment Surcharge' mod='mollie'}
                        </label>
                        <div class="col-lg-9">
                            <select name="MOLLIE_METHOD_SURCHARGE_TYPE_{$paymentMethod.id}"
                                    class="fixed-width-xl">
                                <option value="0" {if $methodObj->surcharge === '0'} selected {/if}>
                                    {l s='Fixed Fee' mod='mollie'}
                                </option>
                                <option value="1" {if $methodObj->surcharge === '1'} selected {/if}>
                                    {l s='Percentage' mod='mollie'}
                                </option>
                                <option value="2" {if $methodObj->surcharge === '2'} selected {/if}>
                                    {l s='Fixed Fee and Percentage' mod='mollie'}
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-lg-3">
                            {l s='Payment Surcharge Fixed Amount' mod='mollie'}
                        </label>
                        <div class="col-lg-9">
                            <input type="text" name="MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_{$paymentMethod.id}"
                                   class="fixed-width-xl js-mollie-amount" value="{$methodObj->surcharge_fixed_amount}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-lg-3">
                            {l s='Payment Surcharge percentage' mod='mollie'}
                        </label>
                        <div class="col-lg-9">
                            <input type="text" name="MOLLIE_METHOD_SURCHARGE_PERCENTAGE_{$paymentMethod.id}"
                                   class="fixed-width-xl js-mollie-amount" value="{$methodObj->surcharge_percentage}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-lg-3">
                            {l s='Payment Surcharge limit' mod='mollie'}
                        </label>
                        <div class="col-lg-9">
                            <input type="text" name="MOLLIE_METHOD_SURCHARGE_LIMIT_{$paymentMethod.id}"
                                   class="fixed-width-xl js-mollie-amount" value="{$methodObj->surcharge_limit}">
                        </div>
                    </div>
                </div>
            </div>
        {/foreach}
        {foreach $webpack_urls as $webpack_url}
            <script type="text/javascript" src={$webpack_url}></script>
        {/foreach}
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
                window.MollieModule = window.MollieModule || {ldelim}{rdelim};
                window.MollieModule.urls = window.MollieModule.urls || {ldelim}{rdelim};
                window.MollieModule.urls.publicPath = '{$publicPath|escape:'javascript':'UTF-8'}';
                window.MollieModule.debug = {if Configuration::get(Mollie::MOLLIE_DISPLAY_ERRORS)}true{else}false{/if};
            }());
            (function () {
                function initMollieCarriers() {
                    function initMollieCarrierConfig() {
                        if (typeof window.MollieModule === 'undefined'
                            || typeof window.MollieModule.app === 'undefined'
                            || typeof window.MollieModule.app.default === 'undefined'
                            || typeof window.MollieModule.app.default.carrierConfig === 'undefined'
                        ) {
                            return setTimeout(initMollieCarrierConfig, 100);
                        }

                        window.MollieModule.app.default.carrierConfig().then(function (fn) {
                            fn.default(
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
                            );
                        });
                    }

                    function checkInput() {
                        debugger;
                        var container = document.getElementById('{$input.name|escape:'javascript':'UTF-8'}_container');
                        var input = document.getElementById('{$input.name|escape:'javascript':'UTF-8'}');
                        if (input == null) {
                            var newInput = document.createElement('DIV');
                            newInput.id = '{$input.name|escape:'javascript':'UTF-8'}';
                            container.closest('.form-group').style.display = 'block';
                            container.appendChild(newInput);
                            initMollieCarrierConfig();
                        }
                    }
                    debugger;
                    checkInput();
                }
                debugger;

                initMollieCarriers();
            }());
        </script>
    {elseif $input.type == 'mollie-carrier-switch'}
        <div id="{$input.name|escape:'htmlall':'UTF-8'}_info" style="display: none"
             class="{if version_compare($smarty.const._PS_VERSION_, '1.6.0.0', '<')}info{else}alert alert-info{/if}">{l s='This option is not required for the currently selected API' mod='mollie'}</div>
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
                    {if isset($input.br) && $input.br}<br/>{/if}
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
                    if (source == null) {
                        return setTimeout(initMollieCarriersAuto, 100);
                    }

                    function checkInput(e) {
                        var container = document.getElementById('{$input.name|escape:'javascript':'UTF-8'}_container');
                        if (e && e.target && e.target.value && e.target.value === '{$input.depends_value|escape:'javascript':'UTF-8'}') {
                            container.closest('.form-group').style.display = 'block';

                        } else {
                            container.closest('.form-group').style.display = 'none';
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
                                title="{$value.label|escape:'htmlall':'UTF-8'}"/>
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
            {if isset($input.br) && $input.br}<br/>{/if}
            {if isset($value.p) && $value.p}<p>{$value.p|escape:'htmlall':'UTF-8'}</p>{/if}
        {/foreach}
    {elseif $input.type === 'checkbox'}
        <div id="{$input.name|escape:'htmlall':'UTF-8'}_info" style="display: none"
             class="{if version_compare($smarty.const._PS_VERSION_, '1.6.0.0', '<')}info{else}alert alert-info{/if}">{l s='This option is not required for the currently selected API' mod='mollie'}</div>
        <div id="{$input.name|escape:'htmlall':'UTF-8'}_container">
            {$smarty.block.parent}
        </div>
        <script type="text/javascript">
            (function () {
                function initMollieCheckboxAuto() {
                    var source = document.getElementById('{$input.depends|escape:'javascript':'UTF-8'}');
                    if (source == null) {
                        return setTimeout(initMollieCheckboxAuto, 100);
                    }

                    function checkInput(e) {
                        var container = document.getElementById('{$input.name|escape:'javascript':'UTF-8'}_container');
                        if (e && e.target && e.target.value && e.target.value === '{$input.depends_value|escape:'javascript':'UTF-8'}') {
                            container.closest('.form-group').style.display = 'block';
                        } else {
                            container.closest('.form-group').style.display = 'none';
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
    {elseif $input.type === 'mollie-description'}
        <div id="{$input.name|escape:'htmlall':'UTF-8'}_info" style="display: none"
             class="{if version_compare($smarty.const._PS_VERSION_, '1.6.0.0', '<')}info{else}alert alert-info{/if}">{l s='This option is not required for the currently selected API' mod='mollie'}</div>
        <div id="{$input.name|escape:'htmlall':'UTF-8'}_container">
            {$input.type = 'text'}
            {$smarty.block.parent}
        </div>
        <script type="text/javascript">
            (function () {
                function initMollieDescriptionAuto() {
                    var source = document.getElementById('{$input.depends|escape:'javascript':'UTF-8'}');
                    if (source == null) {
                        return setTimeout(function () {
                            initMollieDescriptionAuto.apply(null, arguments);
                        }, 100);
                    }

                    function checkInput(e) {
                        var container = document.getElementById('{$input.name|escape:'javascript':'UTF-8'}_container');
                        if (e && e.target && e.target.value && e.target.value === '{$input.depends_value|escape:'javascript':'UTF-8'}') {
                            container.closest('.form-group').style.display = 'block';
                            $(container.closest('.form-group')).prev('.form-group').css("display", "block");
                        } else {
                            container.closest('.form-group').style.display = 'none';
                            $(container.closest('.form-group')).prev('.form-group').css("display", "none");
                        }
                    }

                    source.addEventListener('change', checkInput);
                    checkInput({
                        target: {
                            value: document.getElementById('{$input.depends|escape:'javascript':'UTF-8'}').value
                        }
                    });
                }

                initMollieDescriptionAuto();
            }());
        </script>
    {elseif $input.type === 'mollie-switch'}
        <div id="{$input.name|escape:'htmlall':'UTF-8'}_info" style="display: none"
             class="{if version_compare($smarty.const._PS_VERSION_, '1.6.0.0', '<')}info{else}alert alert-info{/if}">{l s='This option is not required for the currently selected API' mod='mollie'}</div>
        <div id="{$input.name|escape:'htmlall':'UTF-8'}_container">
            {if version_compare($smarty.const._PS_VERSION_, '1.6.0.0', '<')}
                {foreach $input.values as $value}
                    <input
                    type="radio"
                    name="{$input.name|escape:'htmlall':'UTF-8'}"
                    id="{$input.name|escape:'htmlall':'UTF-8'}_{$value.id|escape:'htmlall':'UTF-8'}"
                    value={if {$value.value|escape:'htmlall':'UTF-8'} == 1} "1" {else} "0" {/if}
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
                    {if isset($input.br) && $input.br}<br/>{/if}
                    {if isset($value.p) && $value.p}<p>{$value.p|escape:'htmlall':'UTF-8'}</p>{/if}
                {/foreach}
            {else}
                <span class="switch prestashop-switch fixed-width-lg">
          {foreach $input.values as $value}
              <input
              type="radio"
              name="{$input.name|escape:'htmlall':'UTF-8'}"{if $value.value == 1}
              id="{$input.name|escape:'htmlall':'UTF-8'}_on"{else}id="{$input.name|escape:'htmlall':'UTF-8'}_off"{/if}
              value={if {$value.value|escape:'htmlall':'UTF-8'} == 1} "1" {else} "0" {/if}
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
                function initMollieAccount() {
                    var source = $('input[name="MOLLIE_ACCOUNT_SWITCH"]');

                    function checkInput(e) {
                        if ($('input[name="MOLLIE_ACCOUNT_SWITCH"]:checked').val() == '0') {
                            e.closest('.form-group').next('.form-group').hide();
                            e.closest('.form-group').find('.help-block').show();
                        } else {
                            e.closest('.form-group').next('.form-group').show();
                            e.closest('.form-group').find('.help-block').hide();
                        }
                    }

                    setTimeout(function () {
                        checkInput(source);
                    }, 100);

                    $(source).on('change', function () {
                        checkInput(source);
                    });
                }

                initMollieAccount();
            }());
        </script>
    {else}
        {$smarty.block.parent}
    {/if}
{/block}