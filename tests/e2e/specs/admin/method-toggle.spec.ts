import { test, expect } from '../../fixtures/base';
import { PaymentMethodsPage } from '../../pages/admin/payment-methods-page';
import { CheckoutPage } from '../../pages/front/checkout-page';
import { skipIfDisconnected } from '../../helpers/module-state';

/**
 * The one flow no other spec exercises: enabling/disabling a method through
 * the BO form itself. Everything else seeds `ps_mol_payment_method` with SQL
 * (the cfg-* setups), which bypasses `PaymentMethodService::savePaymentMethod`
 * entirely — the suite would stay green if saving through the UI broke.
 *
 * Klarna, deliberately: the Mollie test profile offers it (its checkout specs
 * run), and no test in the same invocation asserts its availability — the
 * mobile spec pins bancontact, and the checkout projects run in a separate CI
 * job whose cfg-* setup rewrites the rows anyway. Toggling bancontact here
 * would race the mobile spec's "an Orders-API method is offered" assertion.
 */
const METHOD_ID = 'klarna';
const METHOD_LABEL = /klarna/i;
const BILLING_COUNTRY = 'NL';

test('toggling a method through the BO form is reflected at the checkout', async ({ page }) => {
  // Two full checkout walks plus two BO save round-trips do not fit the 30s default.
  test.setTimeout(240_000);

  const methods = new PaymentMethodsPage(page);
  await methods.goto();
  const card = await methods.revealCard(METHOD_ID);
  skipIfDisconnected((await card.count()) === 0, 'the Mollie method list is empty');

  const checkout = new CheckoutPage(page);

  // Disable through the form → the method must vanish from the payment step.
  await methods.setEnabledViaForm(METHOD_ID, false);
  await checkout.start(BILLING_COUNTRY);
  await expect(checkout.paymentOption(METHOD_LABEL)).toHaveCount(0);

  // Re-enable through the form → it must be offered again. This also restores
  // the state the cfg-orders setup established, so a passing run is neutral
  // to any spec that runs after it.
  await methods.goto();
  await methods.setEnabledViaForm(METHOD_ID, true);
  await checkout.start(BILLING_COUNTRY);
  await expect(checkout.paymentOption(METHOD_LABEL)).toBeVisible();
});
