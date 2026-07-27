import { test, expect } from '../../fixtures/base';
import { AuthorizationPage } from '../../pages/admin/authorization-page';

test('connects a test-mode API key successfully', async ({ page }) => {
  const apiKey = process.env.MOLLIE_TEST_API_KEY;
  test.skip(!apiKey, 'MOLLIE_TEST_API_KEY is not set');

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
