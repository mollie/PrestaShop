{**
* Copyright (c) 2012-2018, Mollie B.V.
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

<section class="mollie_issuer_page">
  <form method="post" action="">
    <div class="form-group row">
      <div class="mollie_issuer">
        <div class="text-center">
          {if count($issuers)}
            <h4>{$msg_bankselect|escape:'htmlall':'UTF-8' nofilter}</h4>
            {foreach $issuers as $issuer}
              <button type="submit"
                      name="issuer"
                      class="mollie_button"
                      value="{$issuer->id|escape:'htmlall':'UTF-8' nofilter}">
                <img src="{$issuer->image->size2x|escape:'htmlall':'UTF-8' nofilter}"/>
                <p class="mollie_p">{$issuer->name|escape:'htmlall':'UTF-8' nofilter}</p>
              </button>
            {/foreach}
            <input type="hidden"
                   name="method"
                   value="ideal"
            >
            {include file="../hook/qr_code.tpl"}
          {/if}
          <span class="clearfix" style="font-size: 20px">{l s='or choose a different payment method' mod='mollie'}</span>
          <a href="{$link->getPageLink('order.php', true, null, ['step' => 3])|escape:'htmlall':'UTF-8' nofilter}"
             class="mollie_btn mollie_btn_primary">
            {$msg_return|escape:'htmlall':'UTF-8' nofilter}
          </a>
        </div>
      </div>
    </div>
  </form>
</section>
