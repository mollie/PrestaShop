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

