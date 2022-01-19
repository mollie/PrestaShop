{**
* Mollie       https://www.mollie.nl
*
* @author      Mollie B.V. <info@mollie.nl>
* @copyright   Mollie B.V.
* @link        https://github.com/mollie/PrestaShop
* @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
*}
<div class="dropdown" style="margin-bottom: 20px">
  <button
    class="btn btn-secondary dropdown-toggle"
    type="button"
    data-toggle="dropdown"
    aria-haspopup="true"
    aria-expanded="false"
    id="mollie-issuer-dropdown-button"
  >
    {l s='Choose a bank' mod='mollie'}
  </button>
  <div class="dropdown-menu">
    {foreach $idealIssuers as $issuer}
      <a class="dropdown-item mollie-issuer-item" data-ideal-issuer="{$issuer['id']|escape:'htmlall':'UTF-8'}" style="cursor: pointer">
        <img src="{$issuer['image']['size2x']|escape:'htmlall':'UTF-8'}" style="height: 24px; width: auto;"> {$issuer['name']|escape:'htmlall':'UTF-8'}
      </a>
    {/foreach}
  </div>
</div>

<script type="text/javascript">
  (function () {
    var hiddenInput;

    function initBanks() {
      if (document.querySelector('input[name="issuer"]') == null) {
        setTimeout(initBanks, 100);
        return;
      }

      hiddenInput = document.querySelector('input[name="issuer"]');
      [].slice.call(document.querySelectorAll('.mollie-issuer-item')).forEach(function (item) {
        item.addEventListener('click', function (event) {
          var elem = event.target;
          if (elem.nodeName === 'IMG') {
            elem = elem.parentNode;
          }
          hiddenInput.value = elem.getAttribute('data-ideal-issuer');
          var dropdownButton = document.getElementById('mollie-issuer-dropdown-button');
          if (dropdownButton) {
            dropdownButton.innerText = elem.innerText;
          }
        });
      });
    }

    initBanks();
  }());
</script>
