{**
* Mollie       https://www.mollie.nl
*
* @author      Mollie B.V. <info@mollie.nl>
* @copyright   Mollie B.V.
* @link        https://github.com/mollie/PrestaShop
* @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
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
                               target="_blank">{l s='More info on Mollie' mod='mollie'}</a>
                        </td>
                        <td>
                            <div class="icon3"></div>
                            <a href="https://www.mollie.com/en/contact"
                               target="_blank">{l s='Contact Mollie' mod='mollie'}</a>
                        </td>
                        <td>
                            <div class="icon2"></div>
                            <a href="https://www.invertus.eu/contacts/"
                               target="_blank">{l s='Contact Invertus' mod='mollie'}</a>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    {elseif $input.type === 'mollie-methods'}
        <script type="text/javascript">
            (function () {
                window.MollieModule = window.MollieModule || {ldelim}{rdelim};
                window.MollieModule.urls = window.MollieModule.urls || {ldelim}{rdelim};
                window.MollieModule.urls.publicPath = '{$publicPath|escape:'javascript':'UTF-8'}';
                window.MollieModule.debug = {if $input.displayErrors}true{else}false{/if};
            }());
        </script>
        <ul id="js-payment-methods-sortable" class="ui-sortable payment-methods-sortable" data-tab-id="general_settings">
            {foreach $input.paymentMethods as $paymentMethod}
                {assign var = 'methodObj' value=$paymentMethod.obj}
                <li class="payment-method border border-bottom">
                    <input type="hidden" name="payment_option_position[{$paymentMethod.obj->id|intval}]" value="{$paymentMethod.obj->position}" class="js-payment-option-position">
                    <span class="js-sort-handle sort-handle">
          <span class="ui-icon ui-icon-arrowthick-2-n-s"></span>
          <a class="text collapsed payment-method__text" data-toggle="collapse" href="#payment-method-form-{$paymentMethod.id|escape:'html':'UTF-8'}"
             role="button"
             aria-expanded="true" aria-controls="#payment-method-form-{$paymentMethod.id|escape:'html':'UTF-8'}">
            <svg class="bi bi-chevron-compact-up mollie-svg" width="1em" height="1em" viewBox="0 0 16 16"
                 fill="currentColor" xmlns="http://www.w3.org/2000/svg">
              <path fill-rule="evenodd"
                    d="M7.776 5.553a.5.5 0 01.448 0l6 3a.5.5 0 11-.448.894L8 6.56 2.224 9.447a.5.5 0 11-.448-.894l6-3z"
                    clip-rule="evenodd"/>
            </svg>
            {l s=$paymentMethod.name mod='mollie'}
          </a>
          <td class="text-center">
            {if $methodObj->enabled}
                <a href="#" class="payment-check-link"
                   data-action="deactivate"
                   onclick="togglePaymentMethod(this, '{$paymentMethod.id|escape:'html':'UTF-8'}'); return false;">
                <i class="icon-check text-success"></i>
              </a>
            {else}
              <a href="#" class="payment-check-link"
                 data-action="activate"
                 onclick="togglePaymentMethod(this, '{$paymentMethod.id|escape:'html':'UTF-8'}'); return false;">
                <i class="icon-remove text-danger"></i>
              </a>
            {/if}
          </td>
        </span>
                    <div class="collapse multi-collapse" id="payment-method-form-{$paymentMethod.id|escape:'html':'UTF-8'}">
                        <div class="form-group">
                            <label class="control-label col-lg-3">
                                {l s='Enabled' mod='mollie'}
                            </label>
                            <div class="col-lg-9">
                                <select name="MOLLIE_METHOD_ENABLED_{$paymentMethod.id|escape:'html':'UTF-8'}" class="fixed-width-xl">
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
                                <input type="text" name="MOLLIE_METHOD_TITLE_{$paymentMethod.id|escape:'html':'UTF-8'}" class="fixed-width-xl"
                                       value="{$methodObj->title|escape:'html':'UTF-8'}">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-lg-3">
                                {l s='Method' mod='mollie'}
                            </label>
                            <div class="col-lg-9">
                                <select name="MOLLIE_METHOD_API_{$paymentMethod.id|escape:'html':'UTF-8'}" class="fixed-width-xl">
                                    {if !in_array($paymentMethod.id, $input.onlyOrderMethods)}
                                        <option value="payments" {if $methodObj->method === 'payments'} selected {/if}>{l s='Payments API' mod='mollie'}</option>
                                    {/if}
                                    <option value="orders" {if $methodObj->method === 'orders'} selected {/if}>{l s='Orders API' mod='mollie'}</option>
                                </select>
                                <p class="help-block">
                                    {$input.methodDescription}
                                </p>
                            </div>
                        </div>
                        <div class="form-group payment-api-description">
                            <label class="control-label col-lg-3 required">
                                {l s='Description' mod='mollie'}
                            </label>
                            <div class="col-lg-9">
                                <input type="text" name="MOLLIE_METHOD_DESCRIPTION_{$paymentMethod.id|escape:'html':'UTF-8'}"
                                       class="fixed-width-xl"
                                        {if !empty($methodObj->description)}
                                            value="{$methodObj->description|escape:'html':'UTF-8'}"
                                        {else}
                                            value='{literal}{orderNumber}{/literal}'
                                        {/if}
                                       required="required">
                                <p class="help-block">
                                    {l s='The description to be used for this transaction. These variables are available:' mod='mollie'}
                                </p>
                                <p class="help-block">
                                    <b>{l s='{orderNumber}' mod='mollie'}</b>,
                                    <b>{l s='{storeName}' mod='mollie'}</b>,
                                    <b>{l s='{countryCode}' mod='mollie'}</b>,
                                    <b>{l s='{cart.id}' mod='mollie'}</b>,
                                    <b>{l s='{order.reference}' mod='mollie'}</b>,
                                    <b>{l s='{customer.firstname}' mod='mollie'}</b>,
                                    <b>{l s='{customer.lastname}' mod='mollie'}</b>,
                                    <b>{l s='{customer.company}' mod='mollie'}</b>,
                                </p>
                                <p class="help-block">
                                    {l s='(Note: This only works when the method is set to Payments API)' mod='mollie'}
                                </p>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-lg-3">
                                {l s='Payment allowed from:' mod='mollie'}
                            </label>
                            <div class="col-lg-9">
                                <select name="MOLLIE_METHOD_APPLICABLE_COUNTRIES_{$paymentMethod.id|escape:'html':'UTF-8'}"
                                        class="fixed-width-xl">
                                    <option value="0" {if $methodObj->is_countries_applicable === '0'} selected {/if}>{l s='All countries' mod='mollie'}</option>
                                    <option value="1" {if $methodObj->is_countries_applicable === '1'} selected {/if}>{l s='Selected Countries' mod='mollie'}</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-lg-3">
                                {l s='Allow payment from specific countries:' mod='mollie'}
                            </label>
                            <div class="col-lg-9">
                                <select name="MOLLIE_METHOD_CERTAIN_COUNTRIES_{$paymentMethod.id|escape:'html':'UTF-8'}[]"
                                        class="fixed-width-xl chosen mollie-chosen" multiple="multiple">
                                    {foreach $input.countries as $country}
                                        <option value="{$country.id|intval}"
                                                {if {$country.id|in_array:$paymentMethod.countries}}selected{/if}>{$country.name|escape:'html':'UTF-8'}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-lg-3">
                                {l s='Exclude payment from specific countries:' mod='mollie'}
                            </label>
                            <div class="col-lg-9">
                                <select name="MOLLIE_METHOD_EXCLUDE_CERTAIN_COUNTRIES_{$paymentMethod.id|escape:'html':'UTF-8'}[]"
                                        class="fixed-width-xl chosen mollie-chosen" multiple="multiple">
                                    {foreach $input.countries as $excludedCountry}
                                        <option value="{$excludedCountry.id|intval}"
                                                {if {$excludedCountry.id|in_array:$paymentMethod.excludedCountries}}selected{/if}>{$excludedCountry.name|escape:'html':'UTF-8'}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-lg-3">
                                {l s='Payment Surcharge' mod='mollie'}
                            </label>
                            <div class="col-lg-9">
                                <select name="MOLLIE_METHOD_SURCHARGE_TYPE_{$paymentMethod.id|escape:'html':'UTF-8'}"
                                        class="fixed-width-xl">
                                    <option value="0" {if $methodObj->surcharge === '0'} selected {/if}>
                                        {l s='No fee' mod='mollie'}
                                    </option>
                                    <option value="1" {if $methodObj->surcharge === '1'} selected {/if}>
                                        {l s='Fixed Fee' mod='mollie'}
                                    </option>
                                    <option value="2" {if $methodObj->surcharge === '2'} selected {/if}>
                                        {l s='Percentage' mod='mollie'}
                                    </option>
                                    <option value="3" {if $methodObj->surcharge === '3'} selected {/if}>
                                        {l s='Fixed Fee and Percentage' mod='mollie'}
                                    </option>
                                </select>
                                <p class="help-block">
                                    {l s='You can display payment fee in your email template by adding "{payment_fee}" in email translations. For more information visit: ' mod='mollie'}
                                    <a href='http://doc.prestashop.com/display/PS17/Translations#Translations-Emailtemplates'
                                       target="_blank">{l s='Translations.' mod='mollie'}</a>
                                </p>
                                <p class="help-block">
                                    {l s="The total surcharge fee should have taxes included." mod='mollie'}
                                </p>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-lg-3">
                                {l s='Payment Surcharge Fixed Amount' mod='mollie'}
                            </label>
                            <div class="col-lg-9">
                                <input type="text" name="MOLLIE_METHOD_SURCHARGE_FIXED_AMOUNT_{$paymentMethod.id|escape:'html':'UTF-8'}"
                                       class="fixed-width-xl js-mollie-amount" value="{$methodObj->surcharge_fixed_amount|escape:'html':'UTF-8'}">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-lg-3">
                                {l s='Payment Surcharge percentage' mod='mollie'}
                            </label>
                            <div class="col-lg-9">
                                <input type="text" name="MOLLIE_METHOD_SURCHARGE_PERCENTAGE_{$paymentMethod.id|escape:'html':'UTF-8'}"
                                       class="fixed-width-xl js-mollie-amount" value="{$methodObj->surcharge_percentage|escape:'html':'UTF-8'}">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-lg-3">
                                {l s='Payment Surcharge limit' mod='mollie'}
                            </label>
                            <div class="col-lg-9">
                                <input type="text" name="MOLLIE_METHOD_SURCHARGE_LIMIT_{$paymentMethod.id|escape:'html':'UTF-8'}"
                                       class="fixed-width-xl js-mollie-amount" value="{$methodObj->surcharge_limit|escape:'html':'UTF-8'}">
                            </div>
                        </div>
                        {if $paymentMethod.id|escape:'html':'UTF-8' === 'creditcard'}
                            <div class="form-group">
                                <label class="control-label col-lg-3">
                                    {l s='Use Custom Logo' mod='mollie'}
                                </label>
                                <div class="col-lg-9">
                                    <select name="MOLLIE_SHOW_CUSTOM_LOGO"
                                            class="fixed-width-xl">
                                        <option value="0" {if $input.showCustomLogo === '0'} selected {/if}>
                                            {l s='No' mod='mollie'}
                                        </option>
                                        <option value="1" {if $input.showCustomLogo === '1'} selected {/if}>
                                            {l s='Yes, Upload custom logo' mod='mollie'}
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group js-form-group-custom-logo">
                                <label class="control-label col-lg-3">
                                </label>
                                <div class="col-lg-4">
                                    <input id="MOLLIE_CUSTOM_LOGO"
                                           type="file"
                                           name="MOLLIE_CUSTOM_LOGO"
                                           class="hide"
                                           accept=".png, .jpg"
                                    >
                                    <div class="dummyfile input-group">
                                        <span class="input-group-addon"><i class="icon-file"></i></span>
                                        <input id="MOLLIE_CUSTOM_LOGO-name"
                                               type="text"
                                               name="MOLLIE_CUSTOM_LOGO"
                                               readonly=""
                                        >
                                        <span class="input-group-btn">
                                        <button id="MOLLIE_CUSTOM_LOGO-selectbutton" type="button"
                                                name="submitAddAttachments"
                                                class="btn btn-default">
                                            <i class="icon-folder-open"></i>
                                            {l s='Add file' mod='mollie'}
                                        </button>
						        	</span>
                                    </div>
                                    <p class="help-block">
                                        {l s='Please use .png/.jpg logo with max size of 256x64.' mod='mollie'}
                                    </p>
                                </div>
                            </div>
                            <div class="form-group js-form-group-custom-logo">
                                <label class="control-label col-lg-3">
                                    {l s='Your custom logo' mod='mollie'}
                                </label>
                                <div class="col-lg-9">
                                    <img src="{$input.customLogoUrl|escape:'html':'UTF-8'}"
                                         class="js-mollie-credit-card-custom-logo
                                     {if $input.customLogoExist === false}hidden{/if}
                                ">
                                </div>
                            </div>
                        {/if}
                        {if $paymentMethod.id|escape:'html':'UTF-8' === 'voucher'}
                            <div class="form-group">
                                <label class="control-label col-lg-3">
                                    {l s='Category' mod='mollie'}
                                </label>
                                <div class="col-lg-9">
                                    <select name="MOLLIE_VOUCHER_CATEGORY"
                                            class="fixed-width-xl">
                                        <option value="null" {if $input.voucherCategory === 'null'} selected {/if}>
                                            {l s='None' mod='mollie'}
                                        </option>
                                        <option value="meal" {if $input.voucherCategory === 'meal'} selected {/if}>
                                            {l s='meal' mod='mollie'}
                                        </option>
                                        <option value="gift" {if $input.voucherCategory === 'gift'} selected {/if}>
                                            {l s='gift' mod='mollie'}
                                        </option>
                                        <option value="eco" {if $input.voucherCategory === 'eco'} selected {/if}>
                                            {l s='eco' mod='mollie'}
                                        </option>
                                    </select>
                                    <p class="help-block">
                                        {l s="The category selected here will be used for all products in your webshop." mod='mollie'}
                                    </p>
                                </div>
                            </div>
                        {/if}
                    </div>
                </li>
            {/foreach}
        </ul>
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
                {l s='Here you can configure what information about the shipment is sent to Mollie' mod='mollie'}
                <br>{l s='You can use the following variables for the Carrier URLs' mod='mollie'}
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
                    <td class="left">{l s='URL Source' mod='mollie'}</td>
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
    {elseif $input.type === 'mollie-password'}
        <div class="input-group fixed-width-lg">
            <span class="input-group-addon">
                <i class="icon-key"></i>
            </span>
            <input type="password"
                   id="{if isset($input.id)}{$input.id|intval}{else}{$input.name|escape:'html':'UTF-8'}{/if}"
                   name="{$input.name|escape:'html':'UTF-8'}"
                   class="{if isset($input.class)}{$input.class|escape:'html':'UTF-8'}{/if} js-visible-password"
                   value="{$fields_value[$input.name]|escape:'html':'UTF-8'}"
                   {if isset($input.autocomplete) && !$input.autocomplete}autocomplete="off"{/if}
                    {if isset($input.required) && $input.required } required="required" {/if}
            />
            <span class="input-group-btn">
              <button
                      class="btn"
                      type="button"
                      data-action="show-password"
                      data-text-show="{l s='Show' mod='mollie'}"
                      data-text-hide="{l s='Hide' mod='mollie'}"
              >
                {l s='Show' d='Shop.Theme.Actions'}
              </button>
        </div>
    {elseif $input.type === 'mollie-button'}
        <button type="button" class="btn btn-default {if isset($input.class)}{$input.class|escape:'html':'UTF-8'}{/if}">{$input.text|escape:'html':'UTF-8'}</button>
    {elseif $input.type === 'mollie-payment-empty-alert'}

        <div data-tab-id="general_settings">
            <div class="alert alert-info">
                {l s='You haven\'t enabled any of the Mollie payments.' mod='mollie'}<br>
                {l s='Please enable payments in your Mollie Account Dashboard.' mod='mollie'}&nbsp;<b><a href="https://www.mollie.com/dashboard/settings/profiles">{l s='Click here to configure.' mod='mollie'}</a></b>
            </div>
        </div>

    {elseif $input.type === 'mollie-button-update-order-total-restriction'}
        <div class="mollie-order-total-restriction-update">
            <div>
                <button type="button" class="btn btn-default {if isset($input.class)}{$input.class|escape:'html':'UTF-8'}{/if}">{$input.text|escape:'html':'UTF-8'}</button>
            </div>
            <div>
                <p class="help-block">{if isset($input.help)}{$input.help|escape:'html':'UTF-8'}{/if}</p>
            </div>
        </div>
    {else}
        {$smarty.block.parent}
    {/if}
{/block}
