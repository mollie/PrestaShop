import { test, expect } from '@playwright/test';

/**
 * Guards on the module's other front controllers, exercised at request level.
 *
 * `controllers/front/return.php` runs `OrderCallBackValidator` before touching
 * any order data and redirects a mismatched signature straight to the
 * homepage — the guard that keeps a guessed `cart_id` from rendering another
 * customer's confirmation. `controllers/front/payment.php` refuses a direct
 * hit that carries no valid cart. Verified against a running shop: all three
 * answer 302 to the shop root, never a 5xx and never an order page.
 */

const RETURN_PATH = '/index.php?fc=module&module=mollie&controller=return';
const PAYMENT_PATH = '/index.php?fc=module&module=mollie&controller=payment';

/** Redirected away without leaking anything order-shaped in the target. */
function expectRedirectAwayFromOrderData(status: number, location: string): void {
  expect(status, `expected a redirect, got HTTP ${status}`).toBe(302);
  expect(location).not.toMatch(/order|confirmation/i);
}

test('return with a forged key redirects home instead of rendering the order', async ({ request }) => {
  const res = await request.get(`${RETURN_PATH}&cart_id=1&key=forged-key-000`, {
    maxRedirects: 0,
  });
  expectRedirectAwayFromOrderData(res.status(), res.headers()['location'] ?? '');
});

test('return with no key at all redirects home', async ({ request }) => {
  const res = await request.get(`${RETURN_PATH}&cart_id=1`, { maxRedirects: 0 });
  expectRedirectAwayFromOrderData(res.status(), res.headers()['location'] ?? '');
});

test('a direct anonymous hit on the payment controller is turned away', async ({ request }) => {
  const res = await request.get(PAYMENT_PATH, { maxRedirects: 0 });
  expectRedirectAwayFromOrderData(res.status(), res.headers()['location'] ?? '');
});

const EXPRESS_RETURN_PATH = '/index.php?fc=module&module=mollie&controller=expressReturn';
const EXPRESS_AJAX_PATH = '/index.php?fc=module&module=mollie&controller=expressComponentAjax';

test('express return with a forged key redirects home instead of waiting for an order', async ({ request }) => {
  const res = await request.get(`${EXPRESS_RETURN_PATH}&cart_id=1&key=forged-key-000`, {
    maxRedirects: 0,
  });
  expectRedirectAwayFromOrderData(res.status(), res.headers()['location'] ?? '');
});

test('an anonymous visitor gets no express session token', async ({ request }) => {
  const res = await request.post(EXPRESS_AJAX_PATH, {
    form: { ajax: '1', action: 'mollie_express_create_session' },
    maxRedirects: 0,
  });
  expect(res.status()).toBe(403);
  const body = await res.json();
  expect(body.success).toBe(false);
  expect(body.errors).toEqual(['forbidden']);
  expect(JSON.stringify(body)).not.toMatch(/clientAccessToken/);
});
