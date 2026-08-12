import { test as setup, expect } from '@playwright/test';
import { AuthorizationPage } from '../pages/admin/authorization-page';
import { hasApiKeyConfigured } from '../helpers/config';
import { envTestApiKey } from '../helpers/mollie-key';

/**
 * Connects the module's test API key, so no later project has to discover for
 * itself that the shop was never connected.
 *
 * Without this, `make e2eh8_local` leaves `MOLLIE_API_KEY_TEST` NULL and every
 * checkout test skips itself as "<method> is not offered at the payment step" —
 * a green run that exercised nothing. The key was previously connected only as
 * a side effect of `specs/admin/authorization.spec.ts`, which the checkout
 * projects neither depend on nor run in the same invocation as.
 *
 * Idempotent: a shop that already carries a key is left alone, so this costs one
 * DB read on a warm shop.
 */
setup('connect the Mollie test API key', async ({ page }) => {
  const apiKey = envTestApiKey();

  if (hasApiKeyConfigured()) return;

  setup.skip(
    !apiKey,
    'MOLLIE_TEST_API_KEY is not set to a valid Mollie key: the module stays ' +
      'unconnected and every method-dependent test will skip itself'
  );

  const auth = new AuthorizationPage(page);
  await auth.goto();
  await auth.connect(apiKey!, 'test');
  await expect
    .poll(() => auth.isConnected(), { timeout: 15_000 })
    .toBe(true);

  // The UI reporting success is not the same as the key being stored — the
  // rest of the suite reads it straight out of ps_configuration.
  expect(
    hasApiKeyConfigured(),
    'the module reported a successful connection but stored no API key'
  ).toBe(true);
});
