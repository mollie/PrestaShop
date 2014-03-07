{**
* Copyright (c) 2012-2014, Mollie B.V.
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
* @category    Mollie
* @package     Mollie_Ideal
* @license     Berkeley Software Distribution License (BSD-License 2) http://www.opensource.org/licenses/bsd-license.php
* @author      Mollie B.V. <info@mollie.nl>
* @copyright   Mollie B.V.
* @link        https://www.mollie.nl
*}

<script type="text/javascript">
    function mollie_pay(method)
    {
        var link = document.getElementById('mollie_link_'+method),
            select = document.getElementById('mollie_issuer_'+method),
            target = link.href;

        if (select) {
            target += '&issuer=' + select.value;
        }
        window.location = target;
    }
</script>

{foreach $methods as $method}
    <p class="payment_module">
        <a href="{$link->getModuleLink('mollie', 'payment', ['method' => $method->id], true)|escape:'html'}"
           title="{l s='Pay with ' mod='mollie'}{l s=$method->description mod='mollie'}"
           id="mollie_link_{$method->id}"
           onclick="mollie_pay('{$method->id}'); return false;"
        >
            {if isset($method->image) && $images !== 'hide'}
                {if $images === 'big'}
                    <img src="{$method->image->bigger}" alt="{l s='' mod='mollie'}" />
                {else}
                    <img src="{$method->image->normal}" alt="{l s='' mod='mollie'}" />
                {/if}
            {/if}
            {l s=$method->description mod='mollie'}
        </a>
        <br />
        {if count($issuers[$method->id])}
            <select id="mollie_issuer_{$method->id}">
                <option value="">{l s='Select your bank:' mod='mollie'}</option>
                {foreach $issuers[$method->id] as $id => $name}
                    <option value="{$id}">{l s={$name} mod='mollie'}</option>
                {/foreach}
            </select>
        {/if}
    </p>
{/foreach}