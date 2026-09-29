import { test, expect } from '../../fixtures/base';
import { CheckoutPage } from '../../pages/front/checkout-page';
import { paymentMethods } from '../../data/payment-methods';
import { skipIfDisconnected } from '../../helpers/module-state';

test('checkout payment step renders on a mobile viewport', async ({ page }) => {
  const checkout = new CheckoutPage(page);
  await checkout.start('NL');
  await expect(page.locator('#checkout-payment-step')).toBeVisible();
  await expect(page.locator('.payment-options')).toBeVisible();
});

test('an Orders-API method is offered on a mobile viewport', async ({ page }) => {
  const method = paymentMethods.find((m) => m.apis.includes('orders') && !m.fixme);
  test.skip(!method, 'no Orders-API method in the registry');

  const checkout = new CheckoutPage(page);
  await checkout.start(method!.billingCountry);
  // Scoped to the payment options like every other availability assertion — a
  // bare getByText also matches incidental page copy and ignores `notLabel`.
  const option = checkout.paymentOption(method!.label, method!.notLabel);
  skipIfDisconnected((await option.count()) === 0, 'no Mollie method is offered at the payment step');
  await expect(option).toBeVisible();
});
