{**
* Mollie       https://www.mollie.nl
*
* @author      Mollie B.V. <info@mollie.nl>
* @copyright   Mollie B.V.
* @link        https://github.com/mollie/PrestaShop
* @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
*}
<!doctype html>
<html>
<head>
  <title>{l s='Mollie iDEAL QR' mod='mollie'}</title>
  <style>
    body {
      font-family: Helvetica, Arial, Sans-Serif;
      text-align: center;
    }
    h1 {
      font-size: 1.6em;
    }
    p {
      font-size: 1.2em;
    }
    .ideal-container {
      width: 100%;
    }
    .ideal-logo {
      margin: 0 auto;
    }
  </style>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
  <div class="ideal-container">
    <img class="ideal-logo" src="{$ideal_logo|escape:'htmlall':'UTF-8'}" alt="">
  </div>
  {if !empty($canceled)}
    <h1>{l s='Welcome back' mod='mollie'}</h1>
    <p>{l s='The payment has been canceled.' mod='mollie'}</p>
  {else}
    <h1>{l s='Welcome back' mod='mollie'}</h1>
    <p>{l s='The payment has been completed. Thank you for your order!' mod='mollie'}</p>
  {/if}
</body>
</html>
