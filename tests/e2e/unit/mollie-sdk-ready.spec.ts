import { test, expect } from '@playwright/test';
import path from 'node:path';

// eslint-disable-next-line @typescript-eslint/no-var-requires
const sdk = require(path.resolve(__dirname, '../../../views/js/front/mollie_sdk_ready.js'));

type FakePage = {
  root: EventTarget & Record<string, unknown>;
  script: EventTarget;
};

function fakePage(readyState: string, withScript = true): FakePage {
  const script = new EventTarget();
  const root = new EventTarget() as EventTarget & Record<string, unknown>;
  root.document = {
    readyState,
    querySelectorAll: (selector: string) => (withScript && selector.includes('js.mollie.com/v1/') ? [script] : []),
  };

  return { root, script };
}

function counter() {
  const calls = { count: 0 };

  return { calls, callback: () => { calls.count++; } };
}

test.describe('Mollie SDK readiness', () => {
  test('runs at once when the SDK global already exists', () => {
    const { root } = fakePage('loading');
    root.Mollie = () => undefined;
    const { calls, callback } = counter();

    sdk.whenSettled(root, 'Mollie', 'js.mollie.com/v1/', callback);

    expect(calls.count).toBe(1);
  });

  test('runs at once when the page already finished loading', () => {
    const { root } = fakePage('complete');
    const { calls, callback } = counter();

    sdk.whenSettled(root, 'Mollie', 'js.mollie.com/v1/', callback);

    expect(calls.count).toBe(1);
  });

  test('waits for the async SDK script and runs only once', () => {
    const { root, script } = fakePage('interactive');
    const { calls, callback } = counter();

    sdk.whenSettled(root, 'Mollie', 'js.mollie.com/v1/', callback);
    expect(calls.count).toBe(0);

    root.Mollie = () => undefined;
    script.dispatchEvent(new Event('load'));
    root.dispatchEvent(new Event('load'));

    expect(calls.count).toBe(1);
  });

  test('settles when the SDK script fails to load', () => {
    const { root, script } = fakePage('interactive');
    const { calls, callback } = counter();

    sdk.whenSettled(root, 'Mollie', 'js.mollie.com/v1/', callback);
    script.dispatchEvent(new Event('error'));

    expect(calls.count).toBe(1);
  });

  test('falls back to the page load event when no SDK script tag is found', () => {
    const { root } = fakePage('interactive', false);
    const { calls, callback } = counter();

    sdk.whenSettled(root, 'Mollie', 'js.mollie.com/v1/', callback);
    expect(calls.count).toBe(0);

    root.dispatchEvent(new Event('load'));

    expect(calls.count).toBe(1);
  });
});
