/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 *
 * @see        https://github.com/mollie/PrestaShop
 * @codingStandardsIgnoreStart
 */

/**
 * Mollie.js is loaded with `async` so a slow js.mollie.com never holds back the checkout page,
 * which means it can arrive after DOMContentLoaded. whenSettled() runs the callback once, as soon
 * as the SDK global exists, or once its script failed or the page finished loading without it.
 * The callback checks the global itself.
 */
(function (root, factory) {
  var api = factory();

  if (typeof module !== 'undefined' && module.exports) {
    module.exports = api;
  }

  if (root) {
    root.MollieSdk = api;
  }
})(typeof window !== 'undefined' ? window : undefined, function () {
  'use strict';

  function whenSettled(root, globalName, scriptSrcPart, callback) {
    var doc = root.document;

    if (root[globalName] || !doc || doc.readyState === 'complete') {
      callback();

      return;
    }

    var settled = false;
    var settle = function () {
      if (settled) {
        return;
      }
      settled = true;
      callback();
    };

    var scripts = doc.querySelectorAll('script[src*="' + scriptSrcPart + '"]');

    for (var i = 0; i < scripts.length; i++) {
      scripts[i].addEventListener('load', settle);
      scripts[i].addEventListener('error', settle);
    }

    root.addEventListener('load', settle);
  }

  return {
    whenSettled: whenSettled
  };
});
