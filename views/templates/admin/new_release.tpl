{**
* Mollie       https://www.mollie.nl
*
* @author      Mollie B.V. <info@mollie.nl>
* @copyright   Mollie B.V.
* @link        https://github.com/mollie/PrestaShop
* @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
*}
{if isset($this_version) && isset($release_version)}
    <div class="bootstrap">
        <div class="alert alert-warning">
            <button type="button" class="close js-mollie-upgrade-tip-close" data-dismiss="alert">×</button>
            <ul class="list-unstyled">
                {l s='You are currently using version %this_version% of this plugin. The latest version is %release_version%. We advice you to [1]update[/1] to enjoy the latest features.'
                html=true
                sprintf=[
                '%this_version%' => $this_version,
                '%release_version%' => $release_version,
                '[1]' => '<a href="https://github.com/mollie/PrestaShop/releases" target="_blank">',
                '[/1]' => '</a>'
                ]}
            </ul>
        </div>
    </div>
{/if}
