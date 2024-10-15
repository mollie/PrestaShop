{**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 *
 * @see        https://github.com/mollie/PrestaShop
 * @codingStandardsIgnoreStart
 *
 *}

<div
  class="btn btn-default button js-log-button"
  data-toggle="modal"
  data-log-id="{$log_id|escape:'htmlall':'UTF-8'}"
  data-information-type="{$log_information_type|escape:'htmlall':'UTF-8'}"
  data-target="log-modal-{$log_id|escape:'htmlall':'UTF-8'}-{$log_information_type|escape:'htmlall':'UTF-8'}"
>
    {l s='View' mod='mollie'}
</div>

<div id="log-modal-{$log_id|escape:'htmlall':'UTF-8'}-{$log_information_type|escape:'htmlall':'UTF-8'}" class="modal">
  <div class="log-modal-overlay"></div>

  <div class="log-modal-window">
    <div class="log-modal-title">
      <h4>
          {if $log_information_type === 'request'}
              {$log_id|escape:'htmlall':'UTF-8'}: {l s='Request data' mod='mollie'}
          {elseif $log_information_type === 'response'}
              {$log_id|escape:'htmlall':'UTF-8'}: {l s='Response data' mod='mollie'}
          {elseif $log_information_type === 'context'}
              {$log_id|escape:'htmlall':'UTF-8'}: {l s='Context data' mod='mollie'}
          {/if}
      </h4>
    </div>

    <div class="log-modal-content">
      <div class="log-modal-content-spinner hidden"></div>
      <pre class="log-modal-content-data hidden"></pre>
    </div>
  </div>
</div>


