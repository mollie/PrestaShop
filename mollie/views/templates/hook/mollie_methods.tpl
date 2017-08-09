<script type="text/javascript">
    function mollie_click(method)
    {
        var issuers = document.getElementById('mollie_issuer_box_'+method);
        if (issuers && issuers.className == 'mollie_hidden')
        {
            issuers.className = 'mollie_issuers';
        }
        else
        {
            mollie_pay(method)
        }
    }
    function mollie_pay(method)
    {
        var link = document.getElementById('mollie_link_'+method),
            select = document.getElementById('mollie_issuer_'+method),
            target = link.href;

        if (select)
        {
            target += '&issuer=' + select.value;
        }
        window.location = target;
    }
</script>

<style type="text/css">
    .mollie_hidden
    {
        display: none;
    }
</style>

{if $warning != ''}
    <p class="payment_module" style="color:red;">{$warning}</p>
{/if}

<div class="mollie_methods">
{foreach $methods as $method}
    <p class="payment_module">
        <a href="{$link->getModuleLink('mollie', 'payment', ['method' => $method->id], true)|escape:'html'}"
           title="{$msg_pay_with|sprintf:$method->description|escape}"
           id="mollie_link_{$method->id|escape}"
           class="mollie_method"
           {if !Module::isEnabled('onepagecheckout')} 
                onclick="mollie_click('{$method->id|escape}'); return false;" 
           {/if}
        >
            {if isset($method->image) && $images !== 'hide'}
                {if $images === 'big'}
                    <img class="mollie_image_big" src="{$method->image->bigger}" alt="" />
                {else}
                    <img class="mollie_image" src="{$method->image->normal}" alt="" />
                {/if}
            {else}
                <span class="mollie_margin">&nbsp;</span>
            {/if}
            {$module->lang($method->description)|escape}
        </a>
        <br />
        {if isset($issuers[$method->id]) && count($issuers[$method->id])}
            <span id="mollie_issuer_box_{$method->id|escape}"{if $issuer_setting === Mollie::ISSUERS_ON_CLICK} class="mollie_hidden"{else} class="mollie_issuers"{/if}>
                <select id="mollie_issuer_{$method->id|escape}">
                    <option value="">{$msg_bankselect|escape}</option>
                    {foreach $issuers[$method->id] as $id => $name}
                        <option value="{$id}">{$module->lang($name)|escape}</option>
                    {/foreach}
                </select>
                <input type="button" onclick="this.disabled=true;mollie_pay('{$method->id|escape}')" value="OK" />
            </span>
        {/if}
    </p>
{/foreach}
</div>