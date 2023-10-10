<div class="prestashop-accounts-wrapper">
  <prestashop-accounts></prestashop-accounts>
</div>

<div class="prestashop-cloudsync-wrapper">
  <div id="prestashop-cloudsync"></div>
</div>

{if isset($cloudSyncPathCDC)}
  <script src="{$cloudSyncPathCDC|escape:'htmlall':'UTF-8'}"></script>
{/if}

{if isset($urlAccountsCdn)}
  <script src="{$urlAccountsCdn|escape:'htmlall':'UTF-8'}" rel=preload></script>
{/if}


