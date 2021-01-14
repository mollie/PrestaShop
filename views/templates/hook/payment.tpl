{**
* Mollie       https://www.mollie.nl
*
* @author      Mollie B.V. <info@mollie.nl>
* @copyright   Mollie B.V.
* @link        https://github.com/mollie/PrestaShop
* @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
*}
{if $warning != ''}
    <p class="payment_module" style="color:red;">{$warning|escape:'htmlall':'UTF-8'}</p>
{/if}

{* use this place holder for such cases which does not redirect user to new window. *}
{$nonRedirectPlaceholder = '#'}

<div class="mollie_methods">
    {foreach $methods as $method}
        <p class="payment_module">
            {if $mollieIframe === '1' && ($method['id_method'] === 'creditcard' || $method['id_method'] === $CARTES_BANCAIRES)}
            <a href="{$nonRedirectPlaceholder|escape:'html':'UTF-8'}"
               title="{$msg_pay_with|sprintf:$method['method_name']|escape:'htmlall':'UTF-8'}"
               id="mollie_link_{$method['id_method']|escape:'htmlall':'UTF-8'}"
               class="mollie_method js_call_iframe"
            >
                {else}
                <a href="{$link->getModuleLink('mollie', 'payment', ['method' => $method['id_method'], 'rand' => time()], true)|escape:'htmlall':'UTF-8'}"
                   title="{$msg_pay_with|sprintf:$method['method_name']|escape:'htmlall':'UTF-8'}"
                   id="mollie_link_{$method['id_method']|escape:'htmlall':'UTF-8'}"
                   class="mollie_method"
                >
                    {/if}

                    {if isset($method['image']) && $images !== 'hide'}
                        {if isset($method['image']['custom_logo'])}
                            <img class="mollie_image_custom"
                                 src="{$method['image']['custom_logo']|escape:'htmlall':'UTF-8'}"{if !empty($method['image']['custom_logo'])} onerror="this.src = ';;{$method['image']['custom_logo']|escape:'javascript':'UTF-8'}"{/if}
                                 alt="{$method['method_name']|escape:'htmlall':'UTF-8'}'">
                        {elseif $images === 'big'}
                            <img class="mollie_image_big"
                                 src="{$method['image']['size2x']|escape:'htmlall':'UTF-8'}"{if !empty($method['image']['size2x'])} onerror="this.src = ';;{$method['image']['size2x']|escape:'javascript':'UTF-8'}"{/if}
                                 alt="{$method['method_name']|escape:'htmlall':'UTF-8'}'">
                        {else}
                            <img class="mollie_image"
                                 src="{$method['image']['size1x']|escape:'htmlall':'UTF-8'}"{if !empty($method['image']['size1x'])} onerror="this.src = '{$method['image']['size1x']|escape:'javascript':'UTF-8'}'"{/if}
                                 alt="{$method['method_name']|escape:'htmlall':'UTF-8'}">
                        {/if}
                    {else}
                        <span class="mollie_margin"> &nbsp;</span>
                    {/if}
                    {if $method['title']}
                        {$method['title']|escape:'htmlall':'UTF-8'}
                    {else}
                        {$module->lang($method['method_name'])|escape:'htmlall':'UTF-8'}
                    {/if}
                    {if $method.fee}
                        <span>{l s='Payment Fee:' mod='mollie'}{$method.fee_display|escape:'html':'UTF-8'}</span>
                    {/if}
                </a>
        </p>
    {/foreach}
</div>

{include file="./init_urls.tpl"}

{if !empty($issuers['ideal']) && $issuer_setting === $ISSUERS_ON_CLICK}
    <script type="text/javascript">
        (function initMollieBanks() {
            if (typeof window.MollieModule === 'undefined'
                || typeof window.MollieModule.app === 'undefined'
                || typeof window.MollieModule.app.default === 'undefined'
                || typeof window.MollieModule.app.default.bankList === 'undefined'
            ) {
                {$web_pack_chunks|json_encode}.
                forEach(function (chunk) {
                    var elem = document.createElement('script');
                    elem.type = 'text/javascript';
                    document.querySelector('head').appendChild(elem);
                    elem.src = chunk;
                });

                return setTimeout(initMollieBanks, 100);
            }
            // Preload
            window.MollieModule.app.default.bankList();

            function showBanks(event) {
                event.preventDefault();
                var banks = {$issuers['ideal']|json_encode};
                var translations = {$mollie_translations|json_encode};
                window.MollieModule.app.default.bankList().then(function (fn) {
                    fn.default(banks, translations);
                });
            }

            var idealBtn = document.getElementById('mollie_link_ideal');
            if (idealBtn != null) {
                idealBtn.onclick = showBanks;
            }
        }());
    </script>
{/if}
