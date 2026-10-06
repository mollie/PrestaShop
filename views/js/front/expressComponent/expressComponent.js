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
 * Mounts the Mollie Express Component (Apple Pay, Google Pay, PayPal) on the checkout payment
 * step. A Checkout Session is bound to one cart total, so the component is re-created whenever
 * the total it was mounted for no longer matches the one rendered on the page.
 *
 * Pure helpers are exported for the Node test runner; everything that touches the DOM lives in
 * init() and only runs in a browser.
 */
(function (root, factory) {
  var helpers = factory();

  if (typeof module !== 'undefined' && module.exports) {
    module.exports = helpers;
  }

  if (root) {
    root.MollieExpressHelpers = helpers;
  }

  if (typeof document !== 'undefined') {
    helpers.init(root);
  }
})(typeof window !== 'undefined' ? window : (typeof globalThis !== 'undefined' ? globalThis : undefined), function () {
  'use strict';

  var CURRENT_STEP_CLASS = 'js-current-step';
  var MOUNTED_ATTRIBUTE = 'data-mollie-mounted';
  var REFRESH_MARGIN_SECONDS = 180;
  var RECHECK_DEBOUNCE_MS = 250;
  var BUTTON_WAIT_ATTEMPTS = 15;
  var BUTTON_WAIT_INTERVAL_MS = 400;

  function buildButtonOptions(buttons) {
    var source = buttons || {};

    return {
      buttons: {
        applepay: { visibility: source.applepay === 'hidden' ? 'hidden' : 'automatic' },
        googlepay: { visibility: source.googlepay === 'hidden' ? 'hidden' : 'automatic' },
        paypal: { visibility: source.paypal === 'automatic' ? 'automatic' : 'hidden' }
      }
    };
  }

  function normalizeAmount(value) {
    if (value === null || value === undefined) {
      return null;
    }

    var parsed = parseFloat(String(value).replace(',', '.'));

    if (isNaN(parsed)) {
      return null;
    }

    return parsed.toFixed(2);
  }

  function shouldRecreate(state, nextAmount) {
    if (!state || state.pending) {
      return false;
    }

    var amount = normalizeAmount(nextAmount);

    if (amount === null) {
      return false;
    }

    return amount !== state.mountedAmount;
  }

  function isPaymentStepCurrent(classList) {
    if (!classList) {
      return true;
    }

    return classList.contains(CURRENT_STEP_CLASS);
  }

  function allRequiredConditionsAccepted(checkboxes) {
    for (var i = 0; i < checkboxes.length; i++) {
      if (!checkboxes[i].checked) {
        return false;
      }
    }

    return true;
  }

  function init(root) {
    var config = root.mollieExpressComponent;

    if (!config) {
      return;
    }

    var state = {
      phase: 'idle',
      pending: false,
      mountedAmount: null,
      failedAmount: null,
      checkout: null,
      component: null,
      refreshTimer: null,
      wrapper: null
    };

    function log(message, data) {
      if (config.debug && root.console && root.console.warn) {
        root.console.warn('[Mollie Express] ' + message, data || '');
      }
    }

    function wrapper() {
      return document.getElementById('mollie-express-component');
    }

    function mountNode() {
      var node = wrapper();

      return node ? node.querySelector('.mollie-express-component__mount') : null;
    }

    function showError(message) {
      var node = wrapper();

      if (!node) {
        return;
      }

      var error = node.querySelector('.mollie-express-component__error');

      if (error) {
        error.textContent = message;
        error.hidden = false;
      }
    }

    function hideError() {
      var node = wrapper();
      var error = node ? node.querySelector('.mollie-express-component__error') : null;

      if (error) {
        error.hidden = true;
      }
    }

    function post(action, data) {
      var body = new URLSearchParams();
      body.set('action', action);
      body.set('ajax', '1');

      Object.keys(data || {}).forEach(function (key) {
        body.set(key, data[key]);
      });

      return fetch(config.ajaxUrl, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body.toString()
      }).then(function (response) {
        return response.json().then(function (json) {
          // JsonResponse::error() answers {success:false, errors:[...], data:[]}. A reply the
          // module wrote is a decision; anything else (login page, 5xx) is a transport failure.
          if (!response.ok || !json || json.success === false) {
            var error = new Error(json && json.errors ? JSON.stringify(json.errors) : 'HTTP ' + response.status);
            error.rejected = !!(json && json.success === false);

            throw error;
          }

          return json.data || json;
        });
      });
    }

    // The regular options of methods that have an express button carry a marker. They stay
    // hidden while the component shows and come back whenever it cannot.
    // PrestaShop renders the marker inside "#payment-option-N-additional-information", a sibling
    // of the option row "#payment-option-N-container", so the row is found by id, not by ancestry.
    function coveredOptions() {
      return Array.prototype.map.call(document.querySelectorAll('.mollie-express-covered'), function (marker) {
        var block = marker.closest('[id$="-additional-information"]');

        if (!block) {
          return null;
        }

        return document.getElementById(block.id.replace('-additional-information', '-container'));
      }).filter(Boolean);
    }

    function hideCoveredOptions() {
      coveredOptions().forEach(function (option) {
        option.classList.remove('mollie-express-fallback');
        option.style.display = 'none';
      });
    }

    function revealCoveredOptions() {
      coveredOptions().forEach(function (option) {
        option.classList.add('mollie-express-fallback');
        option.style.display = '';
      });
    }

    function pageAmount() {
      var node = wrapper();

      return normalizeAmount(node && node.getAttribute('data-amount')) || normalizeAmount(config.amount);
    }

    function teardown() {
      if (state.refreshTimer) {
        root.clearTimeout(state.refreshTimer);
        state.refreshTimer = null;
      }

      var component = state.component;
      state.component = null;
      state.checkout = null;

      if (!component) {
        return Promise.resolve();
      }

      return Promise.resolve(component.unmount()).catch(function () {});
    }

    function onSubmit(event) {
      event.defer();
      hideError();

      var checkboxes = document.querySelectorAll('#conditions-to-approve input[type="checkbox"][required]');

      if (!allRequiredConditionsAccepted(checkboxes)) {
        showError(config.i18n.acceptTerms);
        event.reject();

        return;
      }

      if (!config.validateCartAction) {
        event.resolve();

        return;
      }

      post(config.validateCartAction, {}).then(function (result) {
        if (result && result.ok) {
          event.resolve();

          return;
        }

        event.reject();
        showError(config.i18n.cartChanged);

        if (result && result.clientAccessToken) {
          state.mountedAmount = null;
          recreate();
        }
      }).catch(function (error) {
        // The module said no (order exists, cart not ready): stop the payment. A transport
        // failure must not block a payment the shopper already authorised.
        if (error && error.rejected) {
          log('submit refused by the shop', error);
          showError(config.i18n.cartChanged);
          event.reject();

          return;
        }

        log('submit check failed, continuing', error);
        event.resolve();
      });
    }

    function waitForButtons(target) {
      return new Promise(function (resolve) {
        var attempts = 0;

        (function check() {
          var rendered = target.querySelector('iframe, button, [role="button"]');
          var sized = target.getBoundingClientRect().height > 0 && target.children.length > 0;

          if (rendered || sized) {
            resolve(true);

            return;
          }

          attempts += 1;

          if (attempts >= BUTTON_WAIT_ATTEMPTS) {
            resolve(false);

            return;
          }

          root.setTimeout(check, BUTTON_WAIT_INTERVAL_MS);
        })();
      });
    }

    function scheduleRefresh() {
      var ttl = parseInt(config.tokenTtlSeconds, 10) || 900;
      var delay = Math.max(ttl - REFRESH_MARGIN_SECONDS, 60) * 1000;

      state.refreshTimer = root.setTimeout(function () {
        state.refreshTimer = null;

        if (document.visibilityState !== 'visible') {
          document.addEventListener('visibilitychange', function onVisible() {
            document.removeEventListener('visibilitychange', onVisible);
            state.mountedAmount = null;
            recreate();
          });

          return;
        }

        state.mountedAmount = null;
        recreate();
      }, delay);
    }

    function recreate() {
      var node = wrapper();
      var target = mountNode();

      if (!node || !target || state.pending) {
        return;
      }

      if (!root.Mollie2 || typeof root.Mollie2.Checkout !== 'function') {
        log('Mollie.js v2 is not loaded');
        state.phase = 'unavailable';
        revealCoveredOptions();

        return;
      }

      state.pending = true;
      state.phase = 'creating';

      post(config.createSessionAction, {}).then(function (session) {
        return teardown().then(function () {
          target.innerHTML = '';

          var checkout = root.Mollie2.Checkout(session.clientAccessToken, { locale: config.locale });
          checkout.on('submit', onSubmit);

          var component = checkout.create('express-component', buildButtonOptions(session.buttons || config.buttons));

          return Promise.resolve(component.mount(target)).then(function () {
            state.checkout = checkout;
            state.component = component;
            state.mountedAmount = normalizeAmount(session.amount && session.amount.value) || pageAmount();
            state.phase = 'mounted';
            node.setAttribute(MOUNTED_ATTRIBUTE, '1');
            hideError();
            scheduleRefresh();

            // mount() resolves even when this browser gets no wallet button at all (Chrome
            // without Google Pay, no Apple Pay, PayPal hidden). Reveal the block only once Mollie
            // has put something into it, so the shopper never sees an empty "Express checkout".
            return waitForButtons(target).then(function (hasButtons) {
              node.hidden = !hasButtons;

              if (!hasButtons) {
                log('no wallet button available in this browser');
                revealCoveredOptions();

                return;
              }

              hideCoveredOptions();
            });
          });
        });
      }).catch(function (error) {
        log('express component unavailable', error);
        state.phase = 'unavailable';
        state.mountedAmount = null;
        // Remember the total this failed for, so DOM churn does not retry until the cart changes.
        state.failedAmount = pageAmount();

        if (node) {
          node.hidden = true;
        }

        revealCoveredOptions();
      }).then(function () {
        state.pending = false;
      });
    }

    function maybeRecreate() {
      var step = document.getElementById('checkout-payment-step');

      if (!isPaymentStepCurrent(step ? step.classList : null)) {
        return;
      }

      var node = wrapper();

      if (!node) {
        return;
      }

      var amount = normalizeAmount(node.getAttribute('data-amount'));

      if (state.phase === 'unavailable' && amount === state.failedAmount) {
        return;
      }

      if (!node.hasAttribute(MOUNTED_ATTRIBUTE) || shouldRecreate(state, amount)) {
        recreate();
      }
    }

    var debounceTimer = null;

    function debouncedRecheck() {
      if (debounceTimer) {
        root.clearTimeout(debounceTimer);
      }

      debounceTimer = root.setTimeout(function () {
        debounceTimer = null;
        maybeRecreate();
      }, RECHECK_DEBOUNCE_MS);
    }

    function observe() {
      var step = document.getElementById('checkout-payment-step');

      if (step && root.MutationObserver) {
        new root.MutationObserver(debouncedRecheck).observe(step, { attributes: true, attributeFilter: ['class'] });
      }

      // One page checkout modules replace the payment list through AJAX; the mount div comes back unmounted.
      if (root.MutationObserver) {
        new root.MutationObserver(function (mutations) {
          for (var i = 0; i < mutations.length; i++) {
            if (mutations[i].addedNodes.length) {
              debouncedRecheck();

              return;
            }
          }
        }).observe(document.body, { childList: true, subtree: true });
      }

      if (root.prestashop && typeof root.prestashop.on === 'function') {
        root.prestashop.on('updatedCart', debouncedRecheck);
        root.prestashop.on('updatedDeliveryForm', debouncedRecheck);
      }

      root.addEventListener('pagehide', function () {
        teardown();
      });
    }

    function start() {
      if (!wrapper()) {
        // No mount point on this theme: the regular options must stay available.
        revealCoveredOptions();

        return;
      }

      hideCoveredOptions();
      observe();
      maybeRecreate();
    }

    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', start);

      return;
    }

    start();
  }

  return {
    buildButtonOptions: buildButtonOptions,
    normalizeAmount: normalizeAmount,
    shouldRecreate: shouldRecreate,
    isPaymentStepCurrent: isPaymentStepCurrent,
    allRequiredConditionsAccepted: allRequiredConditionsAccepted,
    init: init
  };
});
