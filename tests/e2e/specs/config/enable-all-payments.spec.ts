import { test, expect } from '../../fixtures/base';
import { PaymentMethodsPage } from '../../pages/admin/payment-methods-page';
import { paymentMethods } from '../../data/payment-methods';
import { setMethodConfig, clearMethodConfig, isMethodEnabled, methodImages } from '../../helpers/config';
import { skipIfDisconnected } from '../../helpers/module-state';

/**
 * The Cypress suite's "Enabling All payments in Module BO" (C339341 / C339377),
 * restored. The Playwright suite seeds `ps_mol_payment_method` with SQL in the
 * cfg-* setups because clicking 21 methods through the form on every run is
 * neither fast nor what most specs are about — but that means only ONE method
 * (`admin/method-toggle.spec.ts`) ever travels the real save path. This spec
 * walks the whole registry through it, which is where per-method save bugs
 * actually live: a method whose settings panel fails to mount, a save that
 * reports success without persisting, a method the module cannot format.
 *
 * Serial by construction — it owns every method row while it runs, and puts the
 * Orders-API assignment back afterwards so the shop is left as `cfg-orders`
 * established it.
 */
test.describe.configure({ mode: 'serial' });

test.afterAll(() => {
  // Same statements as setup/cfg-orders.setup.ts. Restoring rather than leaving
  // every method enabled keeps a later invocation's phase assumptions intact.
  clearMethodConfig();
  for (const method of paymentMethods) {
    if (method.apis.includes('orders')) {
      setMethodConfig(method.id, { enabled: true, api: 'orders' });
    }
  }
});

for (const method of paymentMethods) {
  test(`${method.id}: enabling it through the BO form persists`, async ({ page }) => {
    test.fixme(!!method.fixme, method.fixme);
    // A BO page load plus a save round-trip does not always fit the 30s default.
    test.setTimeout(90_000);

    const methods = new PaymentMethodsPage(page);
    await methods.goto();

    // An empty list is a connected-module regression; one missing method is
    // Mollie's test profile being what it is (riverty, for instance, is in the
    // registry but not currently offered). Only the first deserves a failure.
    skipIfDisconnected((await methods.cardCount()) === 0, 'the Mollie method list is empty');

    // 5s per tab, not the 10s default: the list is known to have rendered by
    // now, so a card that is not in the first tab is in the second or nowhere.
    const card = await methods.revealCard(method.id, 5_000);
    test.skip(
      (await card.count()) === 0,
      `${method.id} is not offered by the Mollie test profile — no card in the BO list`
    );

    await methods.setEnabledViaForm(method.id, true);

    // The row, not the badge: what the save was supposed to change.
    expect(
      isMethodEnabled(method.id),
      `${method.id}: the form reported a successful save but the method row is not enabled`
    ).toBe(true);

    // Saving through the form is also what copies Mollie's image set onto the
    // row (`PaymentMethodSettingsHandler`), which the SQL seeds cannot do — and
    // what the checkout logo then renders from.
    const images = methodImages(method.id);
    expect(images, `${method.id}: no images_json on the row after saving`).not.toBeNull();
    expect(
      images,
      `${method.id}: images_json is still empty after saving, so the module did not pick up the method data`
    ).not.toBe('[]');
  });
}
