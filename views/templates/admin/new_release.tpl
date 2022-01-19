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
                {$github_url|escape:'html':'UTF-8'}
            </ul>
        </div>
    </div>
{/if}
