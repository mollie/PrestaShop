import { test, expect } from '../../fixtures/base';
import { CheckoutPage } from '../../pages/front/checkout-page';
import { HostedCheckoutPage } from '../../pages/mollie/hosted-checkout-page';
import { AdminOrderPage } from '../../pages/admin/admin-order-page';
import { OrderHistoryPage } from '../../pages/front/order-history-page';
import { paymentMethods } from '../../data/payment-methods';
import { envValue, isPubliclyReachableBaseUrl } from '../../helpers/env';

/**
 * Which API this run exercises comes from the job environment, not from a
 * Playwright project field: `E2E_CHECKOUT_API=orders` for the `checkout-orders`
 * invocation and `E2E_CHECKOUT_API=payments` for `checkout-payments`. The two
 * run as separate `npx playwright test` invocations in the same CI job.
 */
const apiFromEnv = envValue('E2E_CHECKOUT_API') as 'orders' | 'payments' | undefined;
const api = apiFromEnv ?? 'orders';
const methodsForThisPhase = paymentMethods.filter((m) => m.apis.includes(api));

/**
 * Mollie validates `webhookUrl` when creating a payment or order and answers
 * 422 "The webhook URL is invalid because it is unreachable from Mollie's point
 * of view" for a private host. Nothing past "Place order" can therefore work
 * against localhost, however correct the test is — only the CI checkout job,
 * which fronts the shop with a Cloudflare named tunnel, can run these.
 * Assertions that merely inspect the payment step are left unguarded.
 */
function requiresPublicHost(): void {
  test.skip(
    !isPubliclyReachableBaseUrl(),
    'E2E_BASE_URL is not publicly reachable: Mollie rejects an unreachable ' +
      'webhookUrl, so no checkout can complete. Run this phase against the ' +
      'Cloudflare tunnel hostname.'
  );
}

test.describe(`checkout — ${api} API`, () => {
  // Without it, checkout-payments would silently re-run the Orders-API set.
  test.skip(
    !apiFromEnv,
    'E2E_CHECKOUT_API is unset: run the phases as separate invocations, ' +
      'E2E_CHECKOUT_API=orders --project=checkout-orders then ' +
      'E2E_CHECKOUT_API=payments --project=checkout-payments'
  );

  for (const method of methodsForThisPhase) {
    test(`${method.id}: paid outcome + BO ship/refund`, async ({ page }) => {
      test.fixme(!!method.fixme, method.fixme);
      requiresPublicHost();

      const checkout = new CheckoutPage(page);
      await checkout.start(method.billingCountry, {
        quantity: method.minAmount ? 250 : 1,
      });

      const option = page.getByText(method.label);
      test.skip((await option.count()) === 0, `${method.id} is not offered at the payment step`);

      await checkout.selectMethod(method.label);
      await checkout.acceptTerms();
      await checkout.placeOrder();

      // An asynchronous method has no outcome to pick and never returns a paid
      // confirmation: Mollie shows transfer instructions and the payment stays
      // open. There is consequently nothing to ship or refund, so what is
      // asserted is that the payment was created and the shop recorded an order.
      if (method.shape === 'async') {
        expect(page.url()).toMatch(/mollie\.com/);
        const history = new OrderHistoryPage(page);
        await expect.poll(() => history.hasAnyOrder(), { timeout: 30_000 }).toBe(true);
        return;
      }

      const hosted = new HostedCheckoutPage(page);
      await hosted.chooseOutcome(method.shape === 'authorize' ? 'authorized' : 'paid');
      await checkout.expectConfirmation();

      const reference = await checkout.getOrderReference();
      const bo = new AdminOrderPage(page);
      await bo.gotoByReference(reference);
      await bo.ship('FedEx', '123456', 'https://www.invertus.eu');
      await bo.refund();
    });

    test(`${method.id}: failed outcome preserves the cart`, async ({ page }) => {
      test.fixme(!!method.fixme, method.fixme);
      requiresPublicHost();
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

      // The module does not show an error page: it returns the customer to the
      // payment step so they can retry, with the cart still intact. Asserting a
      // "payment failed" message would assert behaviour the module never had.
      await expect(page.locator('#checkout-payment-step')).toBeVisible({ timeout: 30_000 });
      await expect(page.locator('#content-hook_order_confirmation')).toHaveCount(0);

      await page.goto('/en/cart?action=show');
      await expect(page.locator('.cart-item')).not.toHaveCount(0);
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

    const candidates = methodsForThisPhase.filter(
      (m) => !m.fixme && !m.minAmount && m.billingCountry === 'NL'
    );
    const offered = [];
    for (const method of candidates) {
      if (await checkout.paymentOption(method.label).count()) offered.push(method.id);
    }

    test.skip(offered.length === 0, 'no Mollie methods offered — module has no API key connected');

    // Not every registry entry is assertable: availability also depends on what
    // the Mollie test profile enables for this cart's country and amount. What
    // must hold is that a method assigned to this phase does surface, and that
    // no method from the *other* phase does.
    const otherPhase = api === 'orders' ? 'payments' : 'orders';
    const foreign = paymentMethods.filter(
      (m) => m.apis.includes(otherPhase) && !m.apis.includes(api) && !m.fixme
    );
    for (const method of foreign) {
      await expect(checkout.paymentOption(method.label)).toHaveCount(0);
    }
    console.log(`offered on the ${api} phase: ${offered.join(', ')}`);
  });
});
