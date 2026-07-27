import type { Page } from '@playwright/test';

/**
 * Looks an order up by reference, never by grid position.
 *
 * Selector confirmed against a running PS8 shop: the Orders grid filter row
 * renders `<input id="order_reference" name="order[reference]" type="text">`
 * (not the `order_filters[...]` naming some other PS grids use).
 */
export async function findOrderByReference(page: Page, reference: string) {
  await page.goto('/admin1/index.php?controller=AdminOrders');
  await dismissInvalidTokenWall(page);

  const filter = page.locator('#order_reference');
  await filter.waitFor({ timeout: 20_000 });
  await filter.fill(reference);
  await filter.press('Enter');

  const row = page.getByRole('row').filter({ hasText: reference }).first();
  await row.waitFor({ timeout: 20_000 });
  return row;
}

/**
 * A tokenless `index.php?controller=...` deep link makes PrestaShop render its
 * "Invalid security token" interstitial first. Clicking through it lands on the
 * intended page with a valid token.
 */
export async function dismissInvalidTokenWall(page: Page) {
  const button = page.locator('.btn-continue');
  if (await button.count()) {
    await button.first().click();
    await page.waitForLoadState('domcontentloaded');
  }
}
