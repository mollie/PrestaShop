{**
* Mollie       https://www.mollie.nl
*
* @author      Mollie B.V. <info@mollie.nl>
* @copyright   Mollie B.V.
* @link        https://github.com/mollie/PrestaShop
* @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
*}
{if isset($this_version) && isset($release_version)}
    {assign var ="update" value = "<a href='https://github.com/mollie/PrestaShop/releases' target='_blank'>update</a>"}
    <div class="bootstrap">
        <div class="alert alert-warning">
            <button type="button" class="close js-mollie-upgrade-tip-close" data-dismiss="alert">×</button>
            <ul class="list-unstyled">
                {l s='You are currently using version %this_version% of this plugin. The latest version is %release_version%. We advice you to %update% to enjoy the latest features.' d='Modules.Mollie.Admin' mod='mollie' html=true sprintf=['%this_version%' => $this_version,'%release_version%' => $release_version, '%update%' => $update]}
            </ul>
        </div>
    </div>
{/if}
