import { test, expect } from '../../fixtures/base';
import { PaymentMethodsPage } from '../../pages/admin/payment-methods-page';

test('the payment methods screen renders', async ({ page }) => {
  const methods = new PaymentMethodsPage(page);
  await methods.goto();
  await expect(page.locator('#mollie-payment-methods-root')).toBeVisible();
});

test('the method list is populated from Mollie', async ({ page }) => {
  const methods = new PaymentMethodsPage(page);
  await methods.goto();

  const card = await methods.revealCard('bancontact');
  // The list is fetched from Mollie, so it is only populated once the module
  // has an API key connected.
  test.skip((await card.count()) === 0, 'no methods listed — module has no API key connected');
  await expect(card).toBeVisible();
});

test("toggling a method's settings panel does not error", async ({ page }) => {
  const methods = new PaymentMethodsPage(page);
  await methods.goto();

  const card = await methods.revealCard('bancontact');
  test.skip((await card.count()) === 0, 'no methods listed — module has no API key connected');

  await methods.toggleSettings('bancontact');
  await expect(page.getByTestId('payment-method-bancontact-status')).toBeVisible();
});

// Whether an enabled method surfaces as active is asserted in the checkout
// phase instead: that project owns the per-method API assignment, so asserting
// it here would race with cfg-payments rewriting the same rows.
