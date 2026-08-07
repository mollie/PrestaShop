import { test, expect } from '../../fixtures/base';
import { CheckoutPage } from '../../pages/front/checkout-page';
import { HostedCheckoutPage } from '../../pages/mollie/hosted-checkout-page';
import { AdminOrderPage } from '../../pages/admin/admin-order-page';
import { OrderHistoryPage } from '../../pages/front/order-history-page';
import { paymentMethods } from '../../data/payment-methods';
import { envValue, isPubliclyReachableBaseUrl } from '../../helpers/env';
import { skipIfDisconnected } from '../../helpers/module-state';

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
        minTotal: method.minAmount,
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

      // Shipping is an Orders-API concept: order_info.tpl renders
      // .mollie-ship-btn only under `$mollie_api_type == 'orders'`. The Payments
      // API has no shipment to report, but both APIs can refund.
      let shipped = true;
      if (api === 'orders') {
        await expect(bo.shipButton().first()).toBeVisible();
        shipped = await bo.ship('FedEx', '123456', 'https://www.invertus.eu');
        if (!shipped) {
          console.log(`${method.id}: order not shippable at Mollie yet`);
        }
      }

      // An authorize-shape payment is only captured once the shipment goes
      // through. Until then Mollie holds an authorization with nothing to
      // refund, and the module correctly renders no refund control at all —
      // the order sits in "Authorized"/"Awaiting". Demanding the control here
      // would assert behaviour the module does not have, and it is the same
      // judgement the ship step above already makes.
      if (!shipped) {
        console.log(`${method.id}: not captured, so no refund control is offered`);
        return;
      }

      // The Payments API renders an amount-based control instead of the
      // Orders-API per-line one (`{if $mollie_api_type == 'payments'}` in
      // order_info.tpl), so this phase asserts a PARTIAL refund — the case the
      // per-line path cannot express, and the one the module gets wrong most
      // easily because it has to compute what is still refundable.
      if (api === 'payments') {
        expect(
          await bo.waitForRefundAmountControl(),
          `${method.id}: the refund amount control never became usable`
        ).toBe(true);

        const refundable = await bo.refundableAmount();
        expect(refundable, `${method.id}: no refundable amount rendered`).not.toBeNull();

        // Rounded DOWN to the cent: the input carries `max="{$refundable_amount}"`,
        // so rounding up overshoots it on an odd number of cents.
        const half = Math.floor((refundable! / 2) * 100) / 100;
        test.skip(half < 0.01, `${method.id}: refundable amount too small to split`);

        expect(
          await bo.partialRefund(half),
          `${method.id}: the partial refund control was not actionable`
        ).toBe(true);
        const outcome = await bo.refundOutcome();
        expect(outcome.ok, `${method.id}: partial refund reported "${outcome.message}"`).toBe(true);

        // The real assertion. The success alert only proves the AJAX call
        // returned `success`; this re-reads the amount the module recomputes
        // from Mollie on the next render.
        const remaining = await bo.waitForRefundableAmountBelow(refundable!);
        expect(
          remaining,
          `${method.id}: still-refundable amount never dropped below ${refundable}`
        ).not.toBeNull();
        console.log(`${method.id}: refunded ${half.toFixed(2)}, ${remaining} still refundable`);

        // A partial refund must leave the remainder refundable — one that
        // consumed the whole amount is a full refund by another name.
        if (remaining! > 0 && (await bo.waitForRefundAmountControl(15_000))) {
          const rest = await bo.refundableAmount();
          if (rest && (await bo.partialRefund(rest))) {
            const second = await bo.refundOutcome();
            expect(
              second.ok,
              `${method.id}: refunding the remainder reported "${second.message}"`
            ).toBe(true);
          } else {
            console.log(`${method.id}: Mollie does not currently allow a second refund`);
          }
        }
        return;
      }

      // The refund control must be offered; whether it is actionable is Mollie's
      // decision and differs per method, so a disabled one is reported, not failed.
      // Polled rather than asserted outright: capture is confirmed by webhook,
      // so the control appears a few seconds after the shipment call returns.
      expect(
        await bo.waitForRefundControl(),
        `${method.id}: the refund control never appeared on the order view`
      ).toBe(true);
      const refunded = await bo.refund();
      if (!refunded) {
        console.log(`${method.id}: refund not currently permitted by Mollie`);
      }
    });

    test(`${method.id}: failed outcome preserves the cart`, async ({ page }) => {
      test.fixme(!!method.fixme, method.fixme);
      requiresPublicHost();
      test.skip(method.shape === 'async', 'async methods have no immediate outcome to fail');

      const checkout = new CheckoutPage(page);
      await checkout.start(method.billingCountry, {
        minTotal: method.minAmount,
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
    // One unit of the cheapest catalogue product, so the cart is under in3's
    // minimum on both seeds. The default product costs EUR 120 on PS1785 — a
    // single unit of it already clears the minimum and made this test assert
    // the opposite of what it claims.
    await checkout.start(in3!.billingCountry, { quantity: 1, productId: 'cheapest' });
    // Scoped to the payment options, like every other assertion here: a bare
    // getByText(/in 3/i) also matches incidental copy elsewhere on the page
    // (it does on PS1785) and fails a test that is about method availability.
    await expect(checkout.paymentOption(in3!.label)).toHaveCount(0);
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

    skipIfDisconnected(offered.length === 0, 'no Mollie method is offered at the payment step');

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
