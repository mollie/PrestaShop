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
    expect(helpers.amountFromCartEvent({}, null)).toBeNull();
    expect(helpers.amountFromCartEvent(null, { totals: {} })).toBeNull();
  });

  test('allRequiredConditionsAccepted needs every box ticked', () => {
    expect(helpers.allRequiredConditionsAccepted([])).toBe(true);
    expect(helpers.allRequiredConditionsAccepted([{ checked: true }, { checked: true }])).toBe(true);
    expect(helpers.allRequiredConditionsAccepted([{ checked: true }, { checked: false }])).toBe(false);
  });
});
