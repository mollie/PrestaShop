{**
* Copyright (c) 2012-2020, Mollie B.V.
* All rights reserved.
*
* Redistribution and use in source and binary forms, with or without
* modification, are permitted provided that the following conditions are met:
*
* - Redistributions of source code must retain the above copyright notice,
*    this list of conditions and the following disclaimer.
* - Redistributions in binary form must reproduce the above copyright
*    notice, this list of conditions and the following disclaimer in the
*    documentation and/or other materials provided with the distribution.
*
* THIS SOFTWARE IS PROVIDED BY THE AUTHOR AND CONTRIBUTORS ``AS IS'' AND ANY
* EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
* WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
* DISCLAIMED. IN NO EVENT SHALL THE AUTHOR OR CONTRIBUTORS BE LIABLE FOR ANY
* DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
* (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
* SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
* CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
* LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
* OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH
* DAMAGE.
*
* @author     Mollie B.V. <info@mollie.nl>
* @copyright  Mollie B.V.
* @license    Berkeley Software Distribution License (BSD-License 2) http://www.opensource.org/licenses/bsd-license.php
* @category   Mollie
* @package    Mollie
* @link       https://www.mollie.nl
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
{include file="./qr_code.tpl"}
