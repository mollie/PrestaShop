import { test, expect } from '../../fixtures/base';
import { PaymentMethodsPage } from '../../pages/admin/payment-methods-page';

test('the payment methods screen renders', async ({ page }) => {
  const methods = new PaymentMethodsPage(page);
  await methods.goto();
  await expect(page.locator('#mollie-payment-methods-root')).toBeVisible();
});

test("toggling a method's settings panel does not error", async ({ page }) => {
  const methods = new PaymentMethodsPage(page);
  await methods.goto();

  const card = methods.card('bancontact');
  // The method list is fetched from Mollie, so it is only populated once an API
  // key is connected.
  test.skip((await card.count()) === 0, 'no methods listed — module has no API key connected');

  await methods.toggleSettings('bancontact');
  await expect(page.getByTestId('payment-method-bancontact-status')).toBeVisible();
});
