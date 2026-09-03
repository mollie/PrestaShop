import { test, expect } from '../../fixtures/base';
import { AuthorizationPage } from '../../pages/admin/authorization-page';
import { getGlobalConfig, setGlobalConfig } from '../../helpers/config';
import { envTestApiKey } from '../../helpers/mollie-key';

test('connects a test-mode API key successfully', async ({ page }) => {
  const apiKey = envTestApiKey();
  test.skip(!apiKey, 'MOLLIE_TEST_API_KEY is not set to a valid Mollie key');

  const auth = new AuthorizationPage(page);
  await auth.goto();
  await auth.connect(apiKey!, 'test');
  await expect.poll(() => auth.isConnected(), { timeout: 15_000 }).toBe(true);
});

test('the authorization screen renders its form', async ({ page }) => {
  const auth = new AuthorizationPage(page);
  await auth.goto();
  await expect(page.getByTestId('mollie-mode-test')).toBeVisible();
  await expect(page.getByTestId('mollie-mode-live')).toBeVisible();
  await expect(page.getByTestId('mollie-api-key-input')).toBeVisible();
  await expect(page.getByTestId('mollie-connect-button')).toBeVisible();
});

/**
 * The whole suite stands on the stored key (`connect.setup.ts`), so the one
 * regression that would poison everything downstream is the module *storing*
 * a key Mollie rejects. Shape-valid so it passes any client-side check and
 * the round trip to Mollie is what refuses it.
 */
test('a rejected API key does not replace the stored key', async ({ page }) => {
  const before = getGlobalConfig('MOLLIE_API_KEY_TEST');
  test.skip(!before, 'the module is not connected; there is no stored key to protect');

  const bogus = 'test_' + 'x'.repeat(30);
  const auth = new AuthorizationPage(page);
  await auth.goto();

  // The DB read below is only meaningful once the connect round trip has
  // actually finished, so wait for the AJAX carrying the bogus key.
  const connectResponse = page.waitForResponse(
    (r) => r.request().method() === 'POST' && (r.request().postData() ?? '').includes(bogus),
    { timeout: 30_000 }
  );

  try {
    await auth.connect(bogus, 'test');
    // A client-side rejection that never posts is also a pass — the key
    // staying unchanged is the invariant, not how it was refused.
    await connectResponse.catch(() => {});

    expect(
      getGlobalConfig('MOLLIE_API_KEY_TEST'),
      'the module replaced its stored API key with one Mollie rejected'
    ).toBe(before);

    // The screen must survive the failure — a blank page after a typo'd key
    // is a regression even if the key itself was refused correctly.
    await expect(page.locator('#mollie-authentication-root')).toBeVisible();
  } finally {
    // Never leave the shop poisoned for the rest of the run, even when the
    // assertion above has just failed.
    const after = getGlobalConfig('MOLLIE_API_KEY_TEST');
    if (before && after !== before) {
      setGlobalConfig('MOLLIE_API_KEY_TEST', before);
    }
  }
});
