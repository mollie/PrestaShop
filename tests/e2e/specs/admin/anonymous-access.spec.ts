import { test, expect } from '@playwright/test';

/**
 * Bare `@playwright/test` on purpose: `fixtures/base` overrides storageState
 * with a logged-in BO+FO session, and these tests exist precisely to arrive
 * with no session at all.
 */

test('the payment methods screen redirects an anonymous visitor to the BO login', async ({ page }) => {
  await page.goto('/admin1/index.php?controller=AdminMolliePaymentMethods');

  await expect(page.locator('#passwd')).toBeVisible({ timeout: 20_000 });
  await expect(page.locator('#mollie-payment-methods-root')).toHaveCount(0);
});

test('the module AJAX controller returns no data to an anonymous request', async ({ request }) => {
  const res = await request.get(
    '/admin1/index.php?controller=AdminMollieAjax&action=getPaymentMethods',
    { maxRedirects: 0 }
  );

  expect(res.status()).toBe(302);
  expect(res.headers()['location'] ?? '').toContain('AdminLogin');
});
