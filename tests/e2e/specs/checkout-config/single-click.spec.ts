import { test, expect } from '../../fixtures/base';
import { CheckoutPage } from '../../pages/front/checkout-page';
import { HostedCheckoutPage } from '../../pages/mollie/hosted-checkout-page';
import { MollieComponentsForm, CARD_3DS } from '../../pages/mollie/components-form';
import { paymentMethods } from '../../data/payment-methods';
import {
  getGlobalConfig,
  restoreGlobalConfig,
  setGlobalConfig,
  setMethodConfig,
  singleClickConfigKey,
} from '../../helpers/config';
import {
  deleteMollieCustomers,
  findMollieCustomerId,
  latestTransactionIdForCustomer,
} from '../../helpers/mol-customer';
import { isPubliclyReachableBaseUrl } from '../../helpers/env';
import { skipIfDisconnected } from '../../helpers/module-state';

/**
 * Single-click payments — the Cypress case C339355, "check if customerId is
 * passed during the 2nd payment", which had no Playwright equivalent: the
 * migration design put `MOLLIE_SINGLE_CLICK_PAYMENT` in a `workers: 1`
 * config-mutating project that was never created.
 *
 * What "the customerId is passed" means observably: with single-click on and
 * "Save card" ticked, the module creates a Mollie customer and records its
 * `cst_…` id in `ps_mol_customer` (`PaymentMethodService::getCustomerInfo` →
 * `CustomerService::processCustomerCreation`). On the customer's NEXT checkout
 * the module finds that row and renders "Use saved card" pre-checked, which is
 * the flag that makes the next payment be created against the saved customer
 * rather than a fresh one.
 *
 * Config-mutating and serial: the setting is global, and the second checkout
 * asserts what the first one stored.
 */
test.describe.configure({ mode: 'serial' });

const CARD = paymentMethods.find((m) => m.id === 'creditcard')!;

let previousSingleClick: string | null = null;

function requiresPublicHost(): void {
  test.skip(
    !isPubliclyReachableBaseUrl(),
    'E2E_BASE_URL is not publicly reachable: Mollie rejects an unreachable ' +
      'webhookUrl, so no checkout can complete. Run this project against the ' +
      'Cloudflare tunnel hostname.'
  );
}

test.beforeAll(() => {
  previousSingleClick = getGlobalConfig(singleClickConfigKey());
  setGlobalConfig(singleClickConfigKey(), '1');
  // The card method is assigned to the payments phase in the registry, and this
  // project has no cfg-payments dependency of its own.
  setMethodConfig(CARD.id, { enabled: true, api: 'payments' });
});

test.afterAll(() => {
  restoreGlobalConfig(singleClickConfigKey(), previousSingleClick);
});

test('with single-click on and no saved card, the checkout offers "Save card"', async ({ page, foCustomer }) => {
  test.setTimeout(180_000);

  // A card saved by an earlier run would put this customer straight into the
  // "use saved card" state and make the assertion below meaningless.
  deleteMollieCustomers(foCustomer.email);

  const checkout = new CheckoutPage(page);
  await checkout.start(CARD.billingCountry);

  const option = checkout.paymentOption(CARD.label, CARD.notLabel);
  skipIfDisconnected((await option.count()) === 0, 'the card method is not offered at the payment step');

  await checkout.selectMethod(CARD.label, CARD.notLabel);

  await expect(
    page.locator('#mollie-save-card'),
    'single-click is on but the checkout offers no "Save card" control'
  ).toHaveCount(1);
  await expect(
    page.locator('#mollie-use-saved-card'),
    'the checkout offers "Use saved card" for a customer that has none'
  ).toHaveCount(0);
});

test('saving a card records a Mollie customer, and the next checkout reuses it', async ({ page, foCustomer }) => {
  requiresPublicHost();
  // Two complete card checkouts through Mollie's sandbox.
  test.setTimeout(420_000);

  deleteMollieCustomers(foCustomer.email);

  const checkout = new CheckoutPage(page);
  await checkout.start(CARD.billingCountry);

  const option = checkout.paymentOption(CARD.label, CARD.notLabel);
  skipIfDisconnected((await option.count()) === 0, 'the card method is not offered at the payment step');

  // --- First payment: save the card.
  await checkout.selectMethod(CARD.label, CARD.notLabel);
  await page.locator('#mollie-save-card').check({ force: true });
  await new MollieComponentsForm(page).fill(CARD.id, CARD_3DS);
  await checkout.acceptTerms();
  await checkout.placeOrder();

  const hosted = new HostedCheckoutPage(page);
  await hosted.chooseOutcome('paid');
  await checkout.expectConfirmation();

  // The Mollie customer the module created for this shop customer. Polled: it is
  // written while the payment is being created, but the confirmation page can
  // render before the row is committed on a slow shop.
  let mollieCustomerId: string | null = null;
  await expect
    .poll(
      () => {
        mollieCustomerId = findMollieCustomerId(foCustomer.email);
        return mollieCustomerId;
      },
      {
        timeout: 30_000,
        message: `no ps_mol_customer row for ${foCustomer.email} after paying with "Save card" ticked`,
      }
    )
    .not.toBeNull();
  expect(mollieCustomerId, 'the stored customer id is not a Mollie customer id').toMatch(/^cst_[\w]+$/);

  // --- Second payment: the module must recognise the saved customer.
  await checkout.start(CARD.billingCountry);
  await checkout.selectMethod(CARD.label, CARD.notLabel);

  const useSaved = page.locator('#mollie-use-saved-card');
  await expect(
    useSaved,
    'the second checkout offers no "Use saved card" control, so the saved customer was not found'
  ).toHaveCount(1);
  // Pre-checked is what makes `mollieUseSavedCard` reach the module and the
  // payment be created against the stored customer id.
  await expect(useSaved, '"Use saved card" is not pre-selected on the second checkout').toBeChecked();

  const firstTransaction = latestTransactionIdForCustomer(foCustomer.email);

  await checkout.acceptTerms();
  await checkout.placeOrder();

  /**
   * Deliberately stops here rather than completing the second payment.
   *
   * Mollie's test profile holds no usable mandate for the saved customer, so it
   * answers a saved-customer payment with its own hosted card form — asking for
   * the card again. Driving that form would test Mollie's page, not the module.
   * What the module owns, and what C339355 was really about, is that the payment
   * it created against the stored customer id is one Mollie accepts: a rejected
   * `customerId` fails at creation, and the module then lands the customer on its
   * own error page with no transaction recorded at all.
   */
  let secondTransaction: string | null = null;
  await expect
    .poll(
      () => {
        secondTransaction = latestTransactionIdForCustomer(foCustomer.email);
        return secondTransaction !== firstTransaction ? secondTransaction : null;
      },
      {
        timeout: 30_000,
        message:
          'no new Mollie transaction was recorded for the saved-card payment — ' +
          'Mollie refused the payment the module created against the stored customer',
      }
    )
    .not.toBeNull();
  expect(secondTransaction, 'the second payment carries no Mollie transaction id').toMatch(/^(tr|ord)_[\w]+$/);
  expect(page.url(), 'the saved-card payment did not reach Mollie').toContain('mollie.com');

  // Still one customer, not a second one created for the same shop customer.
  expect(
    findMollieCustomerId(foCustomer.email),
    'the second payment replaced the stored Mollie customer instead of reusing it'
  ).toBe(mollieCustomerId);
});
