<script type="text/javascript">
    function mollie_issuer_submit()
    {
        window.location += '&issuer=' + document.getElementById('mollie_issuer').value;
    }
</script>
<form method="get" action="#">
    {if count($issuers)}
        <select id="mollie_issuer">
            <option value="">{$msg_bankselect|escape}</option>
            {foreach $issuers as $id => $name}
                <option value="{$id}">{$module->lang($name)|escape}</option>
            {/foreach}
        </select>
        <input type="button" value="{$msg_ok}" onclick="if(document.getElementById('mollie_issuer').options[document.getElementById('mollie_issuer').selectedIndex].value){this.disabled=true;}mollie_issuer_submit()" />
    {/if}
    <br /><br />
    <a href="{$smarty.const._PS_BASE_URL_}{$smarty.const.__PS_BASE_URI__}">{$msg_return}</a>
</form>