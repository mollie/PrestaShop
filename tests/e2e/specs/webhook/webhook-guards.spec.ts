import { test, expect } from '@playwright/test';
import { hasApiKeyConfigured } from '../../helpers/config';

const WEBHOOK_PATH = '/index.php?fc=module&module=mollie&controller=webhook';

/**
 * `controllers/front/webhook.php` checks its guards in this order:
 *   1. no API client            -> 401
 *   2. missing security_token   -> 400
 *   3. missing id               -> 422
 *   4. lock already held        -> 409
 *
 * Because guard 1 short-circuits the rest, the 400/422/409 paths are only
 * reachable once the module has an API key stored — hence the split below
 * rather than asserting all four unconditionally.
 *
 * Which branch applies is read inside each test, NOT once at module scope.
 * Playwright evaluates describe bodies while collecting, before any setup
 * project has run, so a module-scope read always saw the shop as it was BEFORE
 * `mollie-connect` connected the key — and then raced it.
 */
function requireApiKey(present: boolean): void {
  test.skip(
    hasApiKeyConfigured() !== present,
    present
      ? 'module has no API key configured; every call short-circuits to 401'
      : 'module has an API key configured; the 401 guard is unreachable'
  );
}

test.describe('module without an API key', () => {

  test('every webhook call is rejected with 401', async ({ request }) => {
    requireApiKey(false);
    const res = await request.post(WEBHOOK_PATH, {
      form: { security_token: 'abc123', id: 'tr_test' },
    });
    expect(res.status()).toBe(401);
  });
});

test.describe('module with an API key', () => {
  test('missing security_token returns 400', async ({ request }) => {
    requireApiKey(true);
    const res = await request.post(WEBHOOK_PATH, { form: { id: 'tr_test' } });
    expect(res.status()).toBe(400);
  });

  test('missing transaction id returns 422', async ({ request }) => {
    requireApiKey(true);
    const res = await request.post(WEBHOOK_PATH, { form: { security_token: 'abc123' } });
    expect(res.status()).toBe(422);
  });

  // Timing-sensitive by nature: the two requests must genuinely overlap for the
  // lock to conflict. Deliberately not retried — a retry would mask a real
  // regression in the locking itself.
  test('concurrent calls with the same token: one gets 409', async ({ request }) => {
    requireApiKey(true);
    const form = { security_token: `dup-${process.pid}-${test.info().workerIndex}`, id: 'tr_test_dup' };
    const [first, second] = await Promise.all([
      request.post(WEBHOOK_PATH, { form }),
      request.post(WEBHOOK_PATH, { form }),
    ]);
    expect([first.status(), second.status()]).toContain(409);
  });
});
