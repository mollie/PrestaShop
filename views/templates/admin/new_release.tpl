{**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 *
 * @see        https://github.com/mollie/PrestaShop
 * @codingStandardsIgnoreStart
 *}
{if isset($this_version) && isset($release_version)}
    {assign var ="updateStart" value = '<a href="https://github.com/mollie/PrestaShop/releases" target="_blank">'}
    {assign var ="updateEnd" value = '</a>'}

  <div class="bootstrap">
        <div class="alert alert-warning">
            <button type="button" class="close js-mollie-upgrade-tip-close" data-dismiss="alert">×</button>
            <ul class="list-unstyled">
                {l s="You are currently using version %s of this module. The latest version is %s." mod='mollie' sprintf=[$this_version, $release_version]}
                {l s="[1]Update[/1] the module to enjoy the latest features." html=true mod='mollie' tags =[$updateStart, $updateEnd]}
            </ul>
        </div>
    </div>
{/if}
