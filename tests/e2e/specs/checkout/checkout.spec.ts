import { test, expect } from '../../fixtures/base';
import { CheckoutPage } from '../../pages/front/checkout-page';
import { HostedCheckoutPage } from '../../pages/mollie/hosted-checkout-page';
import { AdminOrderPage } from '../../pages/admin/admin-order-page';
import { paymentMethods } from '../../data/payment-methods';

/**
 * Which API this run exercises comes from the job environment, not from a
 * Playwright project field: `E2E_CHECKOUT_API=orders` for the `checkout-orders`
 * invocation and `E2E_CHECKOUT_API=payments` for `checkout-payments`. The two
 * run as separate `npx playwright test` invocations in the same CI job.
 */
const api = (process.env.E2E_CHECKOUT_API as 'orders' | 'payments') || 'orders';
const methodsForThisPhase = paymentMethods.filter((m) => m.apis.includes(api));

test.describe(`checkout — ${api} API`, () => {
  for (const method of methodsForThisPhase) {
    test(`${method.id}: paid outcome + BO ship/refund`, async ({ page }) => {
      test.fixme(!!method.fixme, method.fixme);

      const checkout = new CheckoutPage(page);
      await checkout.start(method.billingCountry, {
        quantity: method.minAmount ? 250 : 1,
      });

      const option = page.getByText(method.label);
      test.skip((await option.count()) === 0, `${method.id} is not offered at the payment step`);

      await checkout.selectMethod(method.label);
      await checkout.acceptTerms();
      await checkout.placeOrder();

      const hosted = new HostedCheckoutPage(page);
      await hosted.chooseOutcome(method.shape === 'authorize' ? 'authorized' : 'paid');
      await checkout.expectConfirmation();

      const reference = await checkout.getOrderReference();
      const bo = new AdminOrderPage(page);
      await bo.gotoByReference(reference);
      await bo.ship('FedEx', '123456', 'https://www.invertus.eu');
      await bo.refund();
    });

    test(`${method.id}: failed outcome is surfaced`, async ({ page }) => {
      test.fixme(!!method.fixme, method.fixme);
      test.skip(method.shape === 'async', 'async methods have no immediate outcome to fail');

      const checkout = new CheckoutPage(page);
      await checkout.start(method.billingCountry, {
        quantity: method.minAmount ? 250 : 1,
      });

      const option = page.getByText(method.label);
      test.skip((await option.count()) === 0, `${method.id} is not offered at the payment step`);

      await checkout.selectMethod(method.label);
      await checkout.acceptTerms();
      await checkout.placeOrder();

      const hosted = new HostedCheckoutPage(page);
      await hosted.chooseOutcome('failed');
      await expect(
        page.getByText(/payment.*(failed|declined|unsuccessful|cancell?ed)/i).first()
      ).toBeVisible({ timeout: 20_000 });
    });
  }

  test('in3 is hidden below its minimum order value', async ({ page }) => {
    test.skip(api !== 'orders', 'in3 is only configured on the orders phase');
    const in3 = paymentMethods.find((m) => m.id === 'in3');
    test.skip(!in3, 'in3 is not in the registry');

    const checkout = new CheckoutPage(page);
    // A single unit is far below in3's minimum order value.
    await checkout.start(in3!.billingCountry, { quantity: 1 });
    await expect(page.getByText(in3!.label)).toHaveCount(0);
  });

  test('every enabled method for this phase renders at the payment step', async ({ page }) => {
    const checkout = new CheckoutPage(page);
    await checkout.start('NL');

    const expected = methodsForThisPhase.filter(
      (m) => !m.fixme && !m.minAmount && m.billingCountry === 'NL'
    );
    test.skip(
      (await page.locator('.payment-options').getByText(/mollie|bancontact|ideal/i).count()) === 0,
      'Mollie methods absent — module has no API key connected'
    );

    for (const method of expected) {
      await expect(page.getByText(method.label).first()).toBeVisible();
    }
  });
});
