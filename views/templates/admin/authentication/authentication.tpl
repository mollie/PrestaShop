{* React authorization component mount point *}
<div id="mollie-authentication-root"></div>

{* PrestaShop Account CDN Script *}
{if isset($urlAccountsCdn)}
    <script src="{$urlAccountsCdn|escape:'htmlall':'UTF-8'}" rel="preload"></script>
{/if}

{* CloudSync CDN Script *}
{if isset($urlCloudsync)}
    <script src="{$urlCloudsync|escape:'htmlall':'UTF-8'}"></script>
{/if}

{* Load ES module JavaScript *}
<script type="module" src="{$mollieAuthJsUrl}"></script>

