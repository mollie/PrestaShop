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
* @package     Mollie
* @license     Berkeley Software Distribution License (BSD-License 2) http://www.opensource.org/licenses/bsd-license.php
* @author      Mollie B.V. <info@mollie.nl>
* @copyright   Mollie B.V.
* @link        https://www.mollie.nl
*}

{extends "$layout"}

{block name="content"}
<script type="text/javascript">
    function mollie_issuer_submit()
    {
        window.location += '&issuer=' + document.getElementById('mollie_issuer').value;
    }
</script>
<form method="get" action="#">
    {if count($issuers)}
        <select id="mollie_issuer">
            <option value="">{l s=$msg_bankselect|escape mod='mollie'}</option>
            {foreach $issuers as $id => $name}
                <option value="{$id}">{l s=$module->lang($name)|escape mod='mollie'}</option>
            {/foreach}
        </select>
        <input type="button" value="{$msg_ok}" onclick="if(document.getElementById('mollie_issuer').options[document.getElementById('mollie_issuer').selectedIndex].value){ this.disabled=true; } mollie_issuer_submit();" />
    {/if}
    <br /><br />
    <a href="{$smarty.const._PS_BASE_URL_}{$smarty.const.__PS_BASE_URI__}">{l s=$msg_return mod='mollie'}</a>
</form>
{/block}