import { test, expect } from '../../fixtures/base';
import { AuthorizationPage } from '../../pages/admin/authorization-page';
import { envValue } from '../../helpers/env';

/** Mollie keys are `test_`/`live_` followed by at least 30 word characters. */
const MOLLIE_KEY = /^(test|live)_\w{30,}$/;

test('connects a test-mode API key successfully', async ({ page }) => {
  const apiKey = envValue('MOLLIE_TEST_API_KEY');
  // Shape-checked, not just truthy: a shell or .env quoting slip otherwise
  // reaches here as a non-empty string and fails as if the module were broken.
  test.skip(!apiKey || !MOLLIE_KEY.test(apiKey), 'MOLLIE_TEST_API_KEY is not set to a valid Mollie key');

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
