import { test, expect } from '@playwright/test';
import path from 'node:path';

// eslint-disable-next-line @typescript-eslint/no-var-requires
const sdkWait = require(path.resolve(__dirname, '../../../views/js/front/mollie_sdk_wait.js'));

const V1_SRC = 'https://js.mollie.com/v1/mollie.js';
const V2_SRC = 'https://js.mollie.com/v2/mollie.js?compatible';

type Listener = (event: { target: unknown }) => void;

class FakeTarget {
  private listeners: { type: string; listener: Listener; capture: boolean }[] = [];

  addEventListener(type: string, listener: Listener, capture = false) {
    this.listeners.push({ type, listener, capture });
  }

  fire(type: string) {
    this.dispatch(type, this, () => true);
  }

  /**
   * A script's load and error events do not bubble, so only capturing window listeners see them.
   */
  capture(type: string, target: unknown) {
    this.dispatch(type, target, (capture) => capture);
  }

  private dispatch(type: string, target: unknown, accepts: (capture: boolean) => boolean) {
    this.listeners
      .filter((entry) => entry.type === type && accepts(entry.capture))
      .forEach((entry) => entry.listener({ target }));
  }
}

class FakeScript extends FakeTarget {
  tagName = 'SCRIPT';

  constructor(public src: string) {
    super();
  }
}

/**
 * Browser order for a script event: the window capture listener first, then the script's own.
 */
function fakePage(readyState = 'interactive', srcs: string[] = [V1_SRC, V2_SRC]) {
  const scripts = srcs.map((src) => new FakeScript(src));
  const root = new FakeTarget() as FakeTarget & Record<string, unknown>;
  root.document = {
    readyState,
    querySelectorAll: (selector: string) => scripts.filter((script) => selector.includes(script.src.split('/')[3])),
  };
  const timers = new Map<number, () => void>();
  let nextTimer = 1;
  root.setTimeout = (callback: () => void) => {
    timers.set(nextTimer, callback);

    return nextTimer++;
  };
  root.clearTimeout = (id: number) => timers.delete(id);
  const runTimers = () => {
    const due = Array.from(timers.values());
    timers.clear();
    due.forEach((callback) => callback());
  };
  const finish = (type: 'load' | 'error', src: string) => {
    const script = scripts.find((candidate) => candidate.src === src)!;
    root.capture(type, script);
    script.fire(type);
  };

  return { root, sdk: sdkWait.create(root), finish, runTimers, timers };
}

function counter() {
  const calls = { count: 0 };

  return { calls, callback: () => { calls.count++; } };
}

test.describe('Mollie SDK wait', () => {
  test('does not wait when the SDK global already exists', () => {
    const { root, sdk } = fakePage('loading');
    root.Mollie = () => undefined;
    const { calls, callback } = counter();

    expect(sdk.wait('Mollie', 'js.mollie.com/v1/', callback)).toBe(false);
    expect(sdk.isWaiting('Mollie')).toBe(false);
    expect(calls.count).toBe(0);
  });

  test('does not wait when the page already finished loading', () => {
    const { sdk } = fakePage('complete');
    const { calls, callback } = counter();

    expect(sdk.wait('Mollie', 'js.mollie.com/v1/', callback)).toBe(false);
    expect(calls.count).toBe(0);
  });

  test('waits for the async SDK script and runs the callback once', () => {
    const { root, sdk, finish } = fakePage();
    const { calls, callback } = counter();

    expect(sdk.wait('Mollie', 'js.mollie.com/v1/', callback)).toBe(true);
    expect(sdk.isWaiting('Mollie')).toBe(true);
    expect(calls.count).toBe(0);

    root.Mollie = () => undefined;
    finish('load', V1_SRC);
    root.fire('load');

    expect(calls.count).toBe(1);
    expect(sdk.isWaiting('Mollie')).toBe(false);
  });

  test('shares one wait between callers and runs a repeated callback once', () => {
    const { sdk, finish } = fakePage();
    const first = counter();
    const second = counter();

    sdk.wait('Mollie', 'js.mollie.com/v1/', first.callback);
    sdk.wait('Mollie', 'js.mollie.com/v1/', first.callback);
    sdk.wait('Mollie', 'js.mollie.com/v1/', second.callback);
    finish('error', V1_SRC);

    expect(first.calls.count).toBe(1);
    expect(second.calls.count).toBe(1);
  });

  test('keeps the v1 and v2 waits apart', () => {
    const { sdk, finish } = fakePage();
    const v1 = counter();
    const v2 = counter();

    sdk.wait('Mollie', 'js.mollie.com/v1/', v1.callback);
    sdk.wait('Mollie2', 'js.mollie.com/v2/', v2.callback);
    finish('load', V2_SRC);

    expect(v1.calls.count).toBe(0);
    expect(v2.calls.count).toBe(1);
    expect(sdk.isWaiting('Mollie')).toBe(true);
  });

  test('settles when the SDK script fails to load', () => {
    const { sdk, finish } = fakePage();
    const { calls, callback } = counter();

    sdk.wait('Mollie', 'js.mollie.com/v1/', callback);
    finish('error', V1_SRC);

    expect(calls.count).toBe(1);
    expect(sdk.wait('Mollie', 'js.mollie.com/v1/', callback)).toBe(false);
  });

  test('does not wait for an SDK script that failed before anyone asked', () => {
    const { sdk, finish } = fakePage();
    const { calls, callback } = counter();

    finish('error', V1_SRC);

    expect(sdk.wait('Mollie', 'js.mollie.com/v1/', callback)).toBe(false);
    expect(sdk.isWaiting('Mollie')).toBe(false);
    expect(calls.count).toBe(0);
  });

  test('falls back to the page load event when no SDK script tag is found', () => {
    const { root, sdk } = fakePage('interactive', []);
    const { calls, callback } = counter();

    expect(sdk.wait('Mollie', 'js.mollie.com/v1/', callback)).toBe(true);
    root.fire('load');

    expect(calls.count).toBe(1);
  });

  test('gives up after the timeout when the SDK script never answers', () => {
    const { sdk, runTimers } = fakePage();
    const { calls, callback } = counter();

    sdk.wait('Mollie', 'js.mollie.com/v1/', callback);
    runTimers();

    expect(calls.count).toBe(1);
    expect(sdk.isWaiting('Mollie')).toBe(false);
    expect(sdk.wait('Mollie', 'js.mollie.com/v1/', callback)).toBe(false);
  });

  test('runs the callbacks once more when the SDK arrives after the timeout', () => {
    const { root, sdk, finish, runTimers } = fakePage();
    const { calls, callback } = counter();

    sdk.wait('Mollie', 'js.mollie.com/v1/', callback);
    runTimers();
    root.Mollie = () => undefined;
    finish('load', V1_SRC);
    root.fire('load');

    expect(calls.count).toBe(2);
  });

  test('clears the timeout once the SDK settles', () => {
    const { sdk, finish, timers } = fakePage();
    const { calls, callback } = counter();

    sdk.wait('Mollie', 'js.mollie.com/v1/', callback);
    expect(timers.size).toBe(1);
    finish('error', V1_SRC);

    expect(timers.size).toBe(0);
    expect(calls.count).toBe(1);
  });
});
