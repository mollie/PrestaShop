{**
* Mollie       https://www.mollie.nl
*
* @author      Mollie B.V. <info@mollie.nl>
* @copyright   Mollie B.V.
* @link        https://github.com/mollie/PrestaShop
* @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
*}
{if !empty($mollieCheckMethods)}
<script type="text/javascript">
  (function () {
    var request = new XMLHttpRequest();
    request.open('GET', '{$mollieProcessUrl|escape:'javascript':'UTF-8'}&action=MollieMethodConfig', true);
    request.send();
    request = null;
  }());
</script>
{/if}

