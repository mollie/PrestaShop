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
 * which means it can arrive after DOMContentLoaded. wait() holds a callback until the SDK global
 * exists, its script failed, or the page finished loading without it. One wait is kept per global,
 * so every caller shares it and a callback passed twice runs once.
 *
 * This file is registered before the SDK tags, so the capturing listeners below also see an SDK
 * script that fails before any caller asked to wait.
 */
(function (root, factory) {
  var api = factory();

  if (typeof module !== 'undefined' && module.exports) {
    module.exports = api;
  }

  if (root) {
    root.MollieSdk = api.create(root);
  }
})(typeof window !== 'undefined' ? window : undefined, function () {
  'use strict';

  function create(root) {
    var finishedScripts = [];
    var waits = {};

    var recordScript = function (event) {
      var target = event.target;

      if (target && target.tagName === 'SCRIPT' && target.src) {
        finishedScripts.push(target.src);
      }
    };

    root.addEventListener('load', recordScript, true);
    root.addEventListener('error', recordScript, true);

    function sdkScripts(scriptSrcPart) {
      var scripts = root.document.querySelectorAll('script[src*="' + scriptSrcPart + '"]');

      return Array.prototype.slice.call(scripts);
    }

    function hasSettled(globalName, scriptSrcPart) {
      if (root[globalName] || root.document.readyState === 'complete') {
        return true;
      }

      return sdkScripts(scriptSrcPart).some(function (script) {
        return finishedScripts.indexOf(script.src) !== -1;
      });
    }

    function startWait(globalName, scriptSrcPart) {
      var entry = { callbacks: [] };
      var settle = function () {
        if (waits[globalName] !== entry) {
          return;
        }
        delete waits[globalName];
        entry.callbacks.forEach(function (callback) {
          callback();
        });
      };

      sdkScripts(scriptSrcPart).forEach(function (script) {
        script.addEventListener('load', settle);
        script.addEventListener('error', settle);
      });
      root.addEventListener('load', settle);

      waits[globalName] = entry;

      return entry;
    }

    /**
     * Returns true when the callback will run once the SDK settles, false when it already has,
     * in which case the callback is not called and the caller goes on without waiting.
     */
    function wait(globalName, scriptSrcPart, callback) {
      var entry = waits[globalName];

      if (!entry && hasSettled(globalName, scriptSrcPart)) {
        return false;
      }

      entry = entry || startWait(globalName, scriptSrcPart);

      if (entry.callbacks.indexOf(callback) === -1) {
        entry.callbacks.push(callback);
      }

      return true;
    }

    function isWaiting(globalName) {
      return !!waits[globalName];
    }

    return {
      wait: wait,
      isWaiting: isWaiting
    };
  }

  return {
    create: create
  };
});
