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
<?php

return json_decode("<%=
  JSON.stringify(
    webpack
      .chunks
      .filter(item => Object.keys(webpackConfig.entry).includes(item.id))
      .map(item => {
        return {
          name: item.id,
          files: [...item.siblings.map(sibling => `${sibling}${htmlWebpackPlugin.options.production ? `-v${htmlWebpackPlugin.options.version}` : ''}.min.js`), item.files[0]],
        };
      }))
      .replace(/"/mg, '\\"')
%>", true);
