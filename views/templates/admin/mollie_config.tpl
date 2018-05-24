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
* @author     mollie-ui b.V. <info@mollie.nl>
* @copyright  mollie-ui b.V.
* @license    Berkeley Software Distribution License (BSD-License 2) http://www.opensource.org/licenses/bsd-license.php
* @category   Mollie
* @package    Mollie
* @link       https://www.mollie.nl
*}

<div class="mollie_config_page">
  <form action="{$link->getAdminLink('AdminModules', true)|escape:'htmlall':'UTF-8' nofilter}&configure=mollie"
        method="post"
        class="mollie_config"
  >
    <!--Mollie header image-->
    <div class="form-group row">
      <div class="col-md-12">
        <img src="{$module_dir|escape:'htmlall':'UTF-8' nofilter}views/img/mollie_logo.png"
             style="max-width:100%; height:auto"
        >
      </div>
    </div>
    <!--/ Mollie header image-->

    <!--Mollie settings title and messages-->
    <div class="form-group row">
      <div class="col-md-12">
        <h2 style="text-align: left;">
          {$config_legend|escape:'htmlall':'UTF-8' nofilter}
        </h2>
      </div>
      {if $update_message}
        <div class="mollie_update_msg col-md-12">
          <span id="mollie_update_msg">
            {$update_message}
          </span>
        </div>
      {/if}
      {include file="./new_release.tpl"}
      {if $msg_result}
        <div class="mollie_result_msg col-md-12">
          <span id="mollie_result_msg">
            {$msg_result|escape:'htmlall':'UTF-8' nofilter}
          </span>
        </div>
      {/if}
      {if $msg_warning}
        <div class="mollie_update_msg col-md-12">
          <span id="mollie_result_msg">
            {$msg_warning|escape:'htmlall':'UTF-8' nofilter}
          </span>
        </div>
      {/if}
    </div>
    <!--/ Mollie settings title and messages-->

    <!--Mollie API key-->
    <div class="form-group row">
      <div class="col-sm-12 col-md-4 mollie_msg">
        <label for="Mollie_Api_Key">
          <strong>{l s='API Key' mod='mollie'}</strong>
        </label>
      </div>
      <div class="col-sm-12 col-md-8 mollie_input">
        <input name="Mollie_Api_Key"
               id="Mollie_Api_Key"
               value="{$val_api_key|escape:'htmlall':'UTF-8' nofilter}"
        >
        <br>
        {if version_compare($smarty.const._PS_VERSION_, '1.6.0.0', '<')}<br>{/if}
        <em class="mollie_desc">
          {{{l s='You can find your API key in your %sMollie Profile%s; it starts with test or live.' mod='mollie'}|escape:'htmlall':'UTF-8' nofilter}|sprintf:'<a href="https://www.mollie.nl/beheer/account/profielen/" target="_blank">':'</a>'}
        </em>
      </div>
    </div>
    <!--/ Mollie API key-->

    <!--Mollie payment description-->
    <div class="form-group row">
      <div class="col-sm-12 col-md-4 mollie_msg">
        <label for="Mollie_Description">
          <strong>{l s='Description' mod='mollie'}</strong>
        </label>
      </div>
      <div class="col-sm-12 col-md-8 mollie_input">
        <input name="Mollie_Description"
               id="Mollie_Description"
               value="{$val_desc|escape:'htmlall':'UTF-8' nofilter}"
        >
        <br>
        {if version_compare($smarty.const._PS_VERSION_, '1.6.0.0', '<')}<br>{/if}
        <em class="mollie_desc">{l s='Enter a description here. Note: Payment methods may have a character limit, best keep the description under 29 characters.' mod='mollie'}</em>
      </div>
    </div>
    <!--/ Mollie payment description-->

    <!--Mollie locale settings-->
    <div class="form-group row">
      <div class="col-sm-12 col-md-4 mollie_msg">
        <label for="Mollie_Paymentscreen_Locale">
          <strong>{l s='Send locale for payment screen' mod='mollie'}</strong>
        </label>
      </div>
      <div class="col-sm-12 col-md-8 mollie_input">
        <select name="Mollie_Paymentscreen_Locale"
                id="Mollie_Paymentscreen_Locale">
          {foreach $payscreen_locale_options as $value => $title}
            <option value="{$value|escape:'htmlall':'UTF-8' nofilter}"{if $value == $payscreen_locale_value} selected="selected"{/if}>
              {$title|escape:'htmlall':'UTF-8' nofilter}
            </option>
          {/foreach}
        </select>
        <br>
        {if version_compare($smarty.const._PS_VERSION_, '1.6.0.0', '<')}<br>{/if}
        <em class="mollie_desc">{{l s='Should the plugin send the current webshop %slocale%s to Mollie. Mollie payment screens will be in the same language as your webshop. Mollie can also detect the language based on the user\'s browser language.' mod='mollie'}|escape:'htmlall':'UTF-8'|sprintf:'<a href="https://en.wikipedia.org/wiki/Locale" target="_blank">':'</a>'}</em>
      </div>
    </div>
    <!--/ Mollie locale settings-->

    <!--Mollie image options-->
    <div class="form-group row">
      <div class="col-md-12 mollie_title">
        <h3>
          {l s='Visual settings' mod='mollie'}
        </h3>
      </div>
    </div>
    <!--/ Mollie image options-->

    <!--Mollie issuer settings-->
    <div class="form-group row">
      <div class="col-sm-12 col-md-4 mollie_msg">
        <label for="Mollie_Issuers">
          <strong>{l s='Issuer list' mod='mollie'}</strong>
        </label>
      </div>
      <div class="col-sm-12 col-md-8 mollie_input">
        <select name="Mollie_Issuers"
                id="Mollie_Issuers">
          {foreach $issuer_options as $value => $title}
            <option value="{$value|escape:'htmlall':'UTF-8' nofilter}"{if $value == $val_issuers} selected="selected"{/if}>
              {$title|escape:'htmlall':'UTF-8' nofilter}
            </option>
          {/foreach}
        </select>
        <br>
        {if version_compare($smarty.const._PS_VERSION_, '1.6.0.0', '<')}<br>{/if}
        <em class="mollie_desc">{l s='Some payment methods (eg. iDEAL) have an issuer list. This setting specifies where it is shown.' mod='mollie'}</em>
      </div>
    </div>
    <!--Mollie css settings-->
    <div class="form-group row">
      <div class="col-sm-12 col-md-4 mollie_msg">
        <label for="Mollie_Css">
          <strong>{l s='CSS file' mod='mollie'}</strong>
        </label>
      </div>
      <div class="col-sm-12 col-md-8 mollie_input">
        <input name="Mollie_Css"
               id="Mollie_Css"
               value="{$val_css|escape:'htmlall':'UTF-8' nofilter}"
        >
        <br>
        {if version_compare($smarty.const._PS_VERSION_, '1.6.0.0', '<')}<br>{/if}
        <em class="mollie_desc">{l s='Leave empty for default stylesheet. Should include file path when set.' mod='mollie'}</em>
        <em class="mollie_desc">{{l s='Hint: You can use %s{BASE}%s, %s{THEME}%s, %s{CSS}%s, %s{MOBILE}%s, %s{MOBILE_CSS}%s and %s{OVERRIDE}%s for easy folder mapping.' mod='mollie'}|escape:'htmlall':'UTF-8'|sprintf:'<kbd>':'</kbd>':'<kbd>':'</kbd>':'<kbd>':'</kbd>':'<kbd>':'</kbd>':'<kbd>':'</kbd>':'<kbd>':'</kbd>'}</em>
      </div>
    </div>
    <!--/ Mollie issuer settings-->
    
    <!-- Mollie qrenabled checkbox-->
    <div class="form-group row">
      <div class="col-sm-12 col-md-4 mollie_msg">
        <label for="Mollie_Paymentscreen_Locale">
          <strong>{l s='Enable or disable QR code.' mod='mollie'}</strong>
        </label>
      </div>
      <div class="col-sm-12 col-md-8 mollie_input">
        <label class="mollie_switch">
          <input name="Mollie_Qrenabled"
                 id="Mollie_Qrenabled"
                 type="checkbox"
                 value="1"
                  {if $val_qrenabled}
                    checked="checked"
                  {/if}
                 style="width: auto;"
                  >
          <span class="mollie_slider"></span>
        </label>
        <br>
        {if version_compare($smarty.const._PS_VERSION_, '1.6.0.0', '<')}<br>{/if}
        <em class="mollie_desc">
          {l s='Enable or disable iDEAL payments via a mobile device using a QR code.' mod='mollie'}
        </em>
      </div>
    </div>
    <!--/ Mollie qrenabled checkbox-->

    <!-- Mollie payment method list-->
    {if count($methods) && version_compare($smarty.const._PS_VERSION_, '1.6.0.3', '>=')}
      <div class="form-group row">
        <div class="col-sm-12 col-md-4 mollie_msg">
          <label for="Mollie_Css">
            <strong>{l s='Payment methods' mod='mollie'}</strong>
          </label>
        </div>
        <section class="module_list col-sm-12 col-md-8">
          <ul class="list-unstyled sortable">
            {foreach $methods as $index => $method}
              <li class="module_list_item draggable"
                  draggable="true"
                  data-pos="{$index|intval}"
                  data-method="{$method['id']|escape:'htmlall':'UTF-8'}"
              >
                <div class="module_col_position dragHandle">
                  <span class="positions">{{$index|intval} + 1}</span>
                  <div class="btn-group-vertical">
                    <a class="mollie-ui btn btn-primary btn-xs mollie-up">
                      <i class="icon-chevron-up"></i>
                    </a>
                    <a class="mollie-ui btn btn-primary btn-xs mollie-down">
                      <i class="icon-chevron-down"></i>
                    </a>
                  </div>
                </div>
                <div class="module_col_icon">
                  <img width="57" src="{$method['image']}" alt="mollie">
                </div>
                <div class="module_col_infos">
                  <div style="display: inline-block">
                    <span class="module_name">
                      {$method['name']|escape:'htmlall':'UTF-8'}
                    </span>
                  </div>
                  <label class="mollie_switch" style="float: right;width: 60px;height: 24px;right: 20px;top: 5px;">
                    <input type="checkbox"
                           value="1"
                           style="width: auto;"
                           {if !empty($method['enabled'])}checked="checked"{/if}
                    >
                    <span class="mollie_slider"></span>
                  </label>
                </div>
              </li>
            {/foreach}
          </ul>
        </section>
        <input type="hidden" name="Mollie_Payment_Methods" id="Mollie_Payment_Methods">
      </div>
      {include file="./sortable_payment_methods.tpl"}
    {/if}
    <!--/ Mollie payment method list-->

    <!--Mollie status settings-->
    {foreach $statuses as $i => $name}
      <div class="form-group row">
        <div class="col-md-12 mollie_title">
          <h3>
            {$title_status|ucfirst|sprintf:$lang[$name]|replace:'&quot;':'`'|escape:'htmlall':'UTF-8' nofilter}
          </h3>
        </div>
        <div class="col-sm-12 col-md-4 mollie_msg">
          <label for="Mollie_Status_{$name|escape:'htmlall':'UTF-8' nofilter}">
            <strong>{$msg_status_{$name}|escape:'htmlall':'UTF-8' nofilter}</strong>
          </label>
        </div>
        <div class="col-sm-12 col-md-8 mollie_input">
          <select name="Mollie_Status_{$name|escape:'htmlall':'UTF-8' nofilter}"
                  id="Mollie_Status_{$name|escape:'htmlall':'UTF-8' nofilter}">
            {foreach $all_statuses as $j => $status}
              {if $status['id_order_state'] == {$val_status_{$name}}}
                <option style="background-color:{$status['color']|escape:'htmlall':'UTF-8' nofilter};
                        color:white; border:3px solid black;"
                        value="{$status['id_order_state']|intval}"
                        selected="selected">
                  {$status['name']|escape:'htmlall':'UTF-8' nofilter} ({$status['id_order_state']|intval})
                </option>
              {else}
                <option style="background-color:{$status['color']|escape:'htmlall':'UTF-8' nofilter};
                        color:white;"
                        value="{$status['id_order_state']|intval}">
                  {$status['name']|escape:'htmlall':'UTF-8' nofilter} ({$status['id_order_state']|intval})
                </option>
              {/if}
            {/foreach}
          </select>
          <br>
          {if version_compare($smarty.const._PS_VERSION_, '1.6.0.0', '<')}<br>{/if}
          <em class="mollie_desc">{$desc_status_{$name}|escape:'htmlall':'UTF-8' nofilter}</em>
        </div>
        {if $name != constant('\\Mollie\\Api\\Types\\PaymentStatus::STATUS_PAID')}
          <div class="col-sm-12 col-md-4 mollie_msg">
            <label for="Mollie_Mail_When_{$name|escape:'htmlall':'UTF-8' nofilter}">
              <strong>{$msg_mail_{$name}|escape:'htmlall':'UTF-8' nofilter}</strong>
            </label>
          </div>
          <div class="col-sm-12 col-md-8 mollie_input">
            <label class="mollie_switch">
              <input name="Mollie_Mail_When_{$name|escape:'htmlall':'UTF-8' nofilter}"
                     id="Mollie_Mail_When_{$name|escape:'htmlall':'UTF-8' nofilter}"
                     type="checkbox"
                     value="1"
                      {if !empty($val_mail_{$name})}
                        checked="checked"
                      {/if}
                     style="width: auto;"
              >
              <span class="mollie_slider"></span>
            </label>
            <br>
            {if version_compare($smarty.const._PS_VERSION_, '1.6.0.0', '<')}<br>{/if}
            <em class="mollie_desc">{$desc_mail_{$name}|escape:'htmlall':'UTF-8' nofilter}</em>
          </div>
        {/if}
      </div>
    {/foreach}
    <!--/ Mollie status settings-->

    <!--Mollie debug settings-->
    <div class="form-group row">
      <div class="col-md-12 mollie_title">
        <h3>
          {l s='Debug level' mod='mollie'}
        </h3>
      </div>
      <div class="col-sm-12 col-md-4 mollie_msg">
        <label for="Mollie_Errors">
          <strong>{l s='Display errors' mod='mollie'}</strong>
        </label>
      </div>
      <div class="col-sm-12 col-md-8 mollie_input">
        <label class="mollie_switch">
          <input name="Mollie_Errors"
                 id="Mollie_Errors"
                 type="checkbox"
                 value="1"
                  {if $val_errors}
                    checked="checked"
                  {/if}
                 style="width: auto;"
          >
        <span class="mollie_slider"></span>
        </label>
        <br>
        {if version_compare($smarty.const._PS_VERSION_, '1.6.0.0', '<')}<br>{/if}
        <em class="mollie_desc">{l s='Enabling this feature will display error messages (if any) on the front page. Use for debug purposes only!' mod='mollie'}</em>
      </div>
      <div class="col-sm-12 col-md-4 mollie_msg">
        <strong>{l s='Log level' mod='mollie'}</strong>
      </div>
      <div class="col-sm-12 col-md-8 mollie_input">
        <select name="Mollie_Logger"
                id="Mollie_Logger">
          {foreach $logger_options AS $value => $title}
            <option value="{$value|escape:'htmlall':'UTF-8' nofilter}"{if $value == $val_logger} selected="selected"{/if}>
              {$title|escape:'htmlall':'UTF-8' nofilter}
            </option>
          {/foreach}
        </select>
        <br>
        {if version_compare($smarty.const._PS_VERSION_, '1.6.0.0', '<')}<br>{/if}
        <em class="mollie_desc">{{l s='Recommended level: Errors. Set to Everything to monitor incoming webhook requests. %sView logs%s' mod='mollie'}|escape:'htmlall':'UTF-8'|sprintf:"<a href=\"%s\">":'</a>'|sprintf:{$link->getAdminLink('AdminLogs')|escape:'htmlall':'UTF-8' nofilter}}</em>
      </div>
    </div>
    <!--/ Mollie debug settings-->

    <!--Mollie save options-->
    <div class="form-group row">
      <div class="col-sm-12 col-md-4 mollie_msg">
        <label for="Mollie_Config_Save">
          <strong>{l s='Save settings' mod='mollie'}</strong>
        </label>
      </div>
      <div class="col-sm-12 col-md-8 mollie_input">
        <input type="submit"
               class="mollie-ui btn btn-primary"
               name="Mollie_Config_Save"
               value="{$val_save|escape:'htmlall':'UTF-8' nofilter}"
        >
      </div>
    </div>
    <!--/ Mollie save options-->

  </form>
</div>
