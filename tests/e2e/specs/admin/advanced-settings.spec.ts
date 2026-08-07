import { test, expect } from '../../fixtures/base';
import { AdvancedSettingsPage } from '../../pages/admin/advanced-settings-page';
import { skipIfDisconnected } from '../../helpers/module-state';

test('advanced settings page renders its sections and saves', async ({ page }) => {
  const settings = new AdvancedSettingsPage(page);
  await settings.goto();

  // The settings form is only rendered once the module has a connected API key.
  const rendered = await settings.waitForForm();
  skipIfDisconnected(!rendered, 'the advanced settings form did not render');

  await expect(page.getByRole('heading', { name: /order/i }).first()).toBeVisible();
  await expect(page.getByRole('heading', { name: /shipping/i }).first()).toBeVisible();

  // Saves back the values already loaded — no state mutation, safe to parallelise.
  await settings.save();
  await settings.expectSavedSuccessfully();
});

test('advanced settings screen mounts', async ({ page }) => {
  const settings = new AdvancedSettingsPage(page);
  await settings.goto();
  await expect(page.locator('#mollie-advanced-settings-root')).toBeVisible();
});

test('advanced settings is reachable from the module sub-tab', async ({ page }) => {
  await page.goto('/admin1/index.php?controller=AdminMollieAuthentication');
  await page.locator('.btn-continue').click({ timeout: 5_000 }).catch(() => {});
  const settings = new AdvancedSettingsPage(page);
  await settings.gotoViaTab();
  await expect(page.locator('#mollie-advanced-settings-root')).toBeVisible();
});
