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
{if $smarty.const._PS_VERSION_|@addcslashes:'\'' >= '1.6'}
    <div class="panel">
      <div class="panel-heading">
        <img src="{$module_dir|escape:'htmlall':'UTF-8' nofilter}views/img/mollie_panel_icon.png" height="32" width="32" style="height: 14px; width: 14px"> {$msg_title|escape:'htmlall':'UTF-8' nofilter}
      </div>
      <div class="mollie_refund_button_box">
        {if $status === 'form'}
          <form action="{$link->getAdminLink('AdminOrders', true)|escape:'htmlall':'UTF-8' nofilter}&id_order={$smarty.get.id_order|intval}&vieworder&Mollie_Refund"
                method="post"
                id="mollie_refund_form"
          >
            <div class="mollie_refund_desc">{$msg_description|escape:'htmlall':'UTF-8' nofilter}</div>
            <input id="mollie_refund"
                   name="Mollie_Refund"
                   type="submit"
                   class="btn btn-default"
                   value="{$msg_button|escape:'htmlall':'UTF-8' nofilter}"
                   onclick="window.MollieModule.refund(); return false;"
            >
          </form>
        {elseif $status === 'fail'}
          <div class="mollie_refund_fail">{$msg_fail|escape:'htmlall':'UTF-8' nofilter}</div>
          <div class="mollie_refund_details">{$msg_details|escape:'htmlall':'UTF-8' nofilter}</div>
        {elseif $status === 'success'}
          <div class="mollie_refund_success">{$msg_success|escape:'htmlall':'UTF-8' nofilter}</div>
          <br/>
          <div class="mollie_refund_details">{$msg_details|escape:'htmlall':'UTF-8' nofilter}</div>
        {/if}
      </div>
    </div>
{else}
  <div class="mollie_refund">
    <div class="mollie_panel">
      <div class="mollie_refund_message">
        <img src="{$img_src|escape:'htmlall':'UTF-8' nofilter}" alt=""/>{$msg_title|escape:'htmlall':'UTF-8' nofilter}
      </div>
      <div class="mollie_refund_button_box">
        {if $status === 'form'}
          <form action="{$link->getAdminLink('AdminOrders', true)|escape:'htmlall':'UTF-8' nofilter}&id_order={$smarty.get.id_order|intval}&vieworder&Mollie_Refund"
                method="post"
                id="mollie_refund_form"
          >
            <div class="mollie_refund_desc">{$msg_description|escape:'htmlall':'UTF-8' nofilter}</div>
            <input id="mollie_refund"
                   name="Mollie_Refund"
                   type="submit"
                   class="mollie_refund_button"
                   value="{$msg_button|escape:'htmlall':'UTF-8' nofilter}"
                   onclick="window.MollieModule.refund(); return false;"
            >
          </form>
        {elseif $status === 'fail'}
          <div class="mollie_refund_fail">{$msg_fail|escape:'htmlall':'UTF-8' nofilter}</div>
          <div class="mollie_refund_details">{$msg_details|escape:'htmlall':'UTF-8' nofilter}</div>
        {elseif $status === 'success'}
          <div class="mollie_refund_success">{$msg_success|escape:'htmlall':'UTF-8' nofilter}</div>
          <br/>
          <div class="mollie_refund_details">{$msg_details|escape:'htmlall':'UTF-8' nofilter}</div>
        {/if}
      </div>
    </div>
  </div>
{/if}
<script type="text/javascript">
  (function () {
    var refundButton = document.getElementById('mollie_refund');

    if (refundButton != null) {
      document.getElementById('mollie_refund').onclick = function (event) {
        event.preventDefault();
        window.MollieModule.debug = {if Configuration::get(Mollie::MOLLIE_DISPLAY_ERRORS)}true{else}false{/if};
        window.MollieModule.back.refund(function (value) {
          if (value) {
            document.getElementById('mollie_refund_form').submit();
          }
        }, {
          areYouSure: '{l s='Are you sure?' mod='mollie'}',
          areYouSureYouWantToRefund: '{l s='Are you sure you want to refund this order?' mod='mollie'}',
          refund: '{l s='Refund' mod='mollie'}',
          cancel: '{l s='Cancel' mod='mollie'}',
        });
      };
    }
  }());
</script>
