{**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 *
 * @see        https://github.com/mollie/PrestaShop
 * @codingStandardsIgnoreStart
 *}
{extends file="helpers/form/form.tpl"}

{block name="input"}
    {if $input.type === 'mollie-support'}
        <div data-tab-id="general_settings">
            <div class="mm-block-mollie">
                <a class="helpbutton" href="https://www.mollie.com/dashboard/settings/profiles" target="_blank"></a>
                <p>
                    <strong>{l s='Developed by Invertus' mod='mollie'}</strong>
                    {l s=' - the most technically advanced agency in the PrestaShop ecosystem.' mod='mollie'}
                </p>
                <table>
                    <tbody>
                    <tr>
                        <td>
                            <div class="icon1"></div>
                            <a href="https://help.mollie.com/hc/en-us"
                               target="_blank">{l s='Go to Mollie help centre' mod='mollie'}</a>
                        </td>
                        <td>
                            <div class="icon3"></div>
                            <a href="https://www.mollie.com/en/contact"
                               target="_blank">{l s='Contact Mollie' mod='mollie'}</a>
                        </td>
                        <td>
                            <div class="icon2"></div>
                            <a href="https://www.invertus.eu/en/contact/"
                               target="_blank">{l s='Contact Invertus' mod='mollie'}</a>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
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
        <div id="{$input.name|escape:'htmlall':'UTF-8'}_container">
            <div class="alert alert-info">
                {l s='Configure the shipment information to send to Mollie' mod='mollie'}
                <br>{l s='You can use the following variables for the carrier URLs' mod='mollie'}
                <ul>
                    <li><strong>%%shipping_number%% </strong>: {l s='Shipping number' mod='mollie'} </li>
                    <li><strong>%%invoice.country_iso%%</strong>: {l s='Billing country code' mod='mollie'}</li>
                    <li><strong>%%invoice.postcode%% </strong>: {l s='Billing postcode' mod='mollie'}</li>
                    <li><strong>%%delivery.country_iso%%</strong>: {l s='Shipping country code' mod='mollie'}</li>
                    <li><strong>%%delivery.postcode%% </strong>: {l s='Shipping postcode' mod='mollie'}</li>
                    <li><strong>%%lang_iso%% </strong>: {l s='2-letter language code' mod='mollie'}</li>
                </ul>
            </div>
            <table class="list form alternate table">
                <thead>
                <tr>
                    <td class="left">{l s='Name' mod='mollie'}</td>
                    <td class="left">{l s='URL source' mod='mollie'}</td>
                    <td class="left">{l s='Custom URL' mod='mollie'}</td>
                </tr>
                </thead>
                <tbody>
                {foreach $input.carriers as $carrier}
                    <tr>
                        <td class="left">{$carrier.name|escape:'html':'UTF-8'}</td>
                        <td class="left">
                            <select name="MOLLIE_CARRIER_URL_SOURCE_{$carrier.id_carrier|intval}">
                                <option value="do_not_auto_ship"
                                        {if $carrier.source === "do_not_auto_ship"}selected{/if}>{l s='Do not automatically ship' mod='mollie'}</option>
                                <option value="no_tracking_info"
                                        {if $carrier.source === "no_tracking_info"}selected{/if}>{l s='No tracking information' mod='mollie'}</option>
                                <option value="carrier_url"
                                        {if $carrier.source === "carrier_url"}selected{/if}>{l s='Carrier URL' mod='mollie'}</option>
                                <option value="custom_url"
                                        {if $carrier.source === "custom_url"}selected{/if}>{l s='Custom URL' mod='mollie'}</option>
                                <option value="module"
                                        {if $carrier.source === "module"}selected{/if}>{l s='Module' mod='mollie'}</option>
                            </select>
                        </td>
                        <td class="left">
                            <input
                                    type="text"
                                    {if $carrier.source !== "custom_url"}disabled=""{/if}
                                    name="MOLLIE_CARRIER_CUSTOM_URL_{$carrier.id_carrier|intval}"
                                    value="{$carrier.custom_url|escape:'html':'UTF-8'}"
                            >
                        </td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
        </div>
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
              <label {if $value.value == 1} for="{$input.name|escape:'htmlall':'UTF-8'}_on"{else} for="{$input.name|escape:'htmlall':'UTF-8'}_off"{/if}>
            {if $value.value == 1}
                {l s='Yes' mod='mollie'}
            {else}
                {l s='No' mod='mollie'}
            {/if}
          </label>
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
             class="{if version_compare($smarty.const._PS_VERSION_, '1.6.0.0', '<')}info{else}alert alert-info{/if}">{l s='This option is not required for the selected API' mod='mollie'}</div>
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
    {* mollie-password and mollie-button types removed - API key functionality moved to AdminMollieAuthentication *}
    {* mollie-methods and mollie-payment-empty-alert removed - Payment methods now in AdminMolliePaymentMethods *}
    {elseif $input.type === 'mollie-button-update-order-total-restriction'}
        <div class="mollie-order-total-restriction-update">
            <div>
                <button type="button"
                        class="btn btn-default {if isset($input.class)}{$input.class|escape:'html':'UTF-8'}{/if}">{$input.text|escape:'html':'UTF-8'}</button>
            </div>
            <div>
                <p class="help-block">{if isset($input.help)}{$input.help|escape:'html':'UTF-8'}{/if}</p>
            </div>
        </div>
    {elseif $input.type === 'mollie-save-warning'}
        <div class="bootstrap">
            <div class="alert alert-warning js-mollie-save-warning hidden">
                <ul class="list-unstyled">
                    {l s="Scroll down and save the changes you made to your environment or API keys before configuring your payment methods." mod='mollie'}
                </ul>
            </div>
        </div>
    {elseif $input.type === 'mollie-hidden-input'}
        <div>
            <input type="hidden" name="{$input.name|escape:'html':'UTF-8'}" value="{$input.value|escape:'html':'UTF-8'}">
        </div>
    {else}
        {$smarty.block.parent}
    {/if}
{/block}
