import type { Page } from '@playwright/test';
import { querySingleValue } from './db';

/** Resolves an order's reference from its id, which the confirmation URL carries. */
export function getOrderReferenceById(idOrder: string | number): string | null {
  return querySingleValue(
    `SELECT reference FROM ps_orders WHERE id_order = ${Number(idOrder)} LIMIT 1`
  );
}

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
 * Clears the two interstitials PrestaShop puts in front of admin pages:
 *
 * - "Invalid security token" for a tokenless `index.php?controller=…` deep link.
 * - "/security/compromised" when the request's scheme does not match the shop's
 *   configured one, which happens behind a TLS-terminating tunnel.
 *
 * Both are click-through, and clicking lands on the intended page.
 */
export async function dismissInvalidTokenWall(page: Page) {
  for (let pass = 0; pass < 2; pass++) {
    const button = page.locator('.btn-continue, .btn-outline-danger').filter({
      hasText: /continue|i understand the risk/i,
    });
    if ((await button.count()) === 0) return;
    await button.first().click();
    await page.waitForLoadState('domcontentloaded');
  }
}
