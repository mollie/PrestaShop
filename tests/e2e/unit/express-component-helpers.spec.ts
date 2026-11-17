import { test, expect } from '@playwright/test';
import path from 'node:path';

// The front script exports its pure helpers through module.exports when loaded in Node, and
// only touches the DOM when a `document` exists, so it can be required without a browser.
// eslint-disable-next-line @typescript-eslint/no-var-requires
const helpers = require(path.resolve(__dirname, '../../../views/js/front/expressComponent/expressComponent.js'));

test.describe('express component helpers', () => {
  test('buildButtonOptions defaults to wallets on and PayPal off', () => {
    expect(helpers.buildButtonOptions(undefined)).toEqual({
      buttons: {
        applepay: { visibility: 'automatic' },
        googlepay: { visibility: 'automatic' },
        paypal: { visibility: 'hidden' },
      },
    });
  });

  test('buildButtonOptions honours explicit visibility', () => {
    const options = helpers.buildButtonOptions({ applepay: 'hidden', googlepay: 'automatic', paypal: 'automatic' });

    expect(options.buttons.applepay.visibility).toBe('hidden');
    expect(options.buttons.googlepay.visibility).toBe('automatic');
    expect(options.buttons.paypal.visibility).toBe('automatic');
  });

  test('buildButtonOptions ignores unknown values', () => {
    const options = helpers.buildButtonOptions({ applepay: 'sometimes', paypal: 'yes' });

    expect(options.buttons.applepay.visibility).toBe('automatic');
    expect(options.buttons.paypal.visibility).toBe('hidden');
  });

  test('normalizeAmount renders two decimals and accepts a comma', () => {
    expect(helpers.normalizeAmount('120')).toBe('120.00');
    expect(helpers.normalizeAmount('120.5')).toBe('120.50');
    expect(helpers.normalizeAmount('59,9')).toBe('59.90');
    expect(helpers.normalizeAmount(12)).toBe('12.00');
  });

  test('normalizeAmount rejects what is not a number', () => {
    expect(helpers.normalizeAmount('')).toBeNull();
    expect(helpers.normalizeAmount('abc')).toBeNull();
    expect(helpers.normalizeAmount(null)).toBeNull();
    expect(helpers.normalizeAmount(undefined)).toBeNull();
  });

  test('shouldRecreate only when idle and the amount moved', () => {
    const mounted = { pending: false, mountedAmount: '59.90' };

    expect(helpers.shouldRecreate(mounted, '59.90')).toBe(false);
    expect(helpers.shouldRecreate(mounted, '59.9')).toBe(false);
    expect(helpers.shouldRecreate(mounted, '64.89')).toBe(true);
    expect(helpers.shouldRecreate({ pending: true, mountedAmount: '59.90' }, '64.89')).toBe(false);
    expect(helpers.shouldRecreate(mounted, null)).toBe(false);
    expect(helpers.shouldRecreate(mounted, 'abc')).toBe(false);
    expect(helpers.shouldRecreate(null, '10.00')).toBe(false);
  });

  test('isPaymentStepCurrent reads js-current-step and treats a missing step as current', () => {
    const current = { contains: (name: string) => name === 'js-current-step' };
    const other = { contains: () => false };

    expect(helpers.isPaymentStepCurrent(current)).toBe(true);
    expect(helpers.isPaymentStepCurrent(other)).toBe(false);
    expect(helpers.isPaymentStepCurrent(null)).toBe(true);
  });

  test('amountFromCartEvent reads the presented cart total from the event or the global cart', () => {
    expect(helpers.amountFromCartEvent({ resp: { cart: { totals: { total: { amount: 34.8, value: '€34.80' } } } } }, null)).toBe('34.80');
    expect(helpers.amountFromCartEvent({ resp: {} }, { totals: { total: { value: '43.2' } } })).toBe('43.20');
    expect(helpers.amountFromCartEvent({ resp: { success: true, totals: { total: { amount: 31.34 } } } }, null)).toBe('31.34');
    expect(helpers.amountFromCartEvent({ response: { totals: { total: { amount: 31.34 } } } }, { totals: { total: { amount: 1 } } })).toBe('31.34');
    expect(helpers.amountFromCartEvent({}, null)).toBeNull();
    expect(helpers.amountFromCartEvent(null, { totals: {} })).toBeNull();
  });

  test('amountFromCartEvent uses the tax included total when the shop shows prices tax excluded', () => {
    const totals = { total: { amount: 100, value: '€100.00' }, total_including_tax: { amount: 121, value: '€121.00' } };

    expect(helpers.amountFromCartEvent({ response: { totals } }, null)).toBe('121.00');
    expect(helpers.amountFromCartEvent({ resp: { cart: { totals } } }, null)).toBe('121.00');
  });

  test('allRequiredConditionsAccepted needs every box ticked', () => {
    expect(helpers.allRequiredConditionsAccepted([])).toBe(true);
    expect(helpers.allRequiredConditionsAccepted([{ checked: true }, { checked: true }])).toBe(true);
    expect(helpers.allRequiredConditionsAccepted([{ checked: true }, { checked: false }])).toBe(false);
  });

  test('isCollectPlacement is true for the cart and checkout strips only', () => {
    expect(helpers.isCollectPlacement('cart')).toBe(true);
    expect(helpers.isCollectPlacement('checkout')).toBe(true);
    expect(helpers.isCollectPlacement('payment')).toBe(false);
    expect(helpers.isCollectPlacement(null)).toBe(false);
  });

  test('pickActivePlacement keeps the payment strip while the payment step is current', () => {
    expect(helpers.pickActivePlacement({ payment: true, checkout: true }, true, false)).toBe('payment');
    expect(helpers.pickActivePlacement({ payment: true }, true, false)).toBe('payment');
  });

  test('pickActivePlacement uses the checkout strip only while personal information is current', () => {
    expect(helpers.pickActivePlacement({ payment: true, checkout: true }, false, true)).toBe('checkout');
    expect(helpers.pickActivePlacement({ payment: true, checkout: true }, false, false)).toBeNull();
    expect(helpers.pickActivePlacement({ payment: true }, false, true)).toBeNull();
  });

  test('pickActivePlacement falls back to the cart strip and to nothing', () => {
    expect(helpers.pickActivePlacement({ cart: true }, true, false)).toBe('cart');
    expect(helpers.pickActivePlacement({}, true, true)).toBeNull();
    expect(helpers.pickActivePlacement(null, true, true)).toBeNull();
  });

  test('shouldKeepMount only for the same session on a mounted node', () => {
    expect(helpers.shouldKeepMount('sess_1', { sessionId: 'sess_1' }, true)).toBe(true);
    expect(helpers.shouldKeepMount('sess_1', { sessionId: 'sess_2' }, true)).toBe(false);
    expect(helpers.shouldKeepMount('sess_1', { sessionId: 'sess_1' }, false)).toBe(false);
    expect(helpers.shouldKeepMount(null, { sessionId: undefined }, true)).toBe(false);
    expect(helpers.shouldKeepMount('sess_1', null, true)).toBe(false);
  });

  test('collectAction mounts, rechecks after a cart change, and leaves a refused node alone', () => {
    expect(helpers.collectAction(false, false, false)).toBe('create');
    expect(helpers.collectAction(true, false, false)).toBe('none');
    expect(helpers.collectAction(true, true, false)).toBe('recheck');
    expect(helpers.collectAction(false, false, true)).toBe('none');
    expect(helpers.collectAction(false, true, true)).toBe('create');
  });
});
