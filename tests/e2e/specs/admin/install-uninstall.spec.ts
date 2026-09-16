import { test, expect } from '../../fixtures/base';

/**
 * The uninstall/reinstall cycle itself is driven by the
 * `installing-uninstalling-enabling-module` Makefile target that runs before
 * this project. This test asserts what that target never did: that the module
 * is left in a working, error-free state afterwards.
 */
test('module is left configurable after the uninstall/reinstall cycle', async ({ page }) => {
  await page.goto('/admin1/index.php?controller=AdminModules&configure=mollie');
  await page.locator('.btn-continue').click({ timeout: 5_000 }).catch(() => {});
  await expect(page.locator('#mollie-authentication-root')).toBeVisible({ timeout: 30_000 });
});

test('module tabs are all registered', async ({ page }) => {
  await page.goto('/admin1/index.php?controller=AdminMollieAuthentication');
  await page.locator('.btn-continue').click({ timeout: 5_000 }).catch(() => {});
  await expect(page.locator('#mollie-authentication-root')).toBeVisible({ timeout: 30_000 });

  for (const tab of [
    'subtab-AdminMollieAuthenticationParent',
    'subtab-AdminMolliePaymentMethodsParent',
    'subtab-AdminMollieAdvancedSettingsParent',
    'subtab-AdminMollieSubscriptionOrdersParent',
    'subtab-AdminMollieSubscriptionFAQParent',
    'subtab-AdminMollieLogsParent',
  ]) {
    await expect(page.locator(`#${tab}`)).toHaveCount(1);
  }
});
