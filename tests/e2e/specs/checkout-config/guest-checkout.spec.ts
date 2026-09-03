import { test, expect } from '../../fixtures/base';
import { GuestCheckoutPage } from '../../pages/front/guest-checkout-page';
import { HostedCheckoutPage } from '../../pages/mollie/hosted-checkout-page';
import { MollieComponentsForm, CARD_3DS, CARD_NON_3DS } from '../../pages/mollie/components-form';
import { paymentMethods } from '../../data/payment-methods';
import {
  getGlobalConfig,
  restoreGlobalConfig,
  setGlobalConfig,
  setMethodConfig,
  singleClickConfigKey,
} from '../../helpers/config';
import { isPubliclyReachableBaseUrl } from '../../helpers/env';
import { skipIfDisconnected } from '../../helpers/module-state';
import { querySingleValue } from '../../helpers/db';

/**
 * Credit-card checkout as a guest — the Cypress cases C339385 and C339386, both
 * of which were permanently `it.skip`ped there ("the Cart is cleaning the
 * cookies") and had no Playwright equivalent at all: every other checkout in
 * this suite runs as a pre-seeded, logged-in worker customer.
 *
 * The module has a real branch behind this. Every single-click control is
 * wrapped in `{if !$isGuest}` (`views/templates/hook/mollie_iframe.tpl` and
 * `mollie_single_click.tpl`), so a guest must be able to pay by card and must
 * never be offered to save one. That half needs no Mollie round-trip and runs
 * against a local shop; the two paid outcomes need a publicly reachable host,
 * like every other checkout in the suite.
 *
 * Config-mutating and serial: it enables guest checkout shop-wide and turns
 * single-click on to prove the guest branch suppresses it.
 */
test.describe.configure({ mode: 'serial' });

const GUEST_CHECKOUT_KEY = 'PS_GUEST_CHECKOUT_ENABLED';
const CARD = paymentMethods.find((m) => m.id === 'creditcard')!;

let previousGuestCheckout: string | null = null;
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
  previousGuestCheckout = getGlobalConfig(GUEST_CHECKOUT_KEY);
  previousSingleClick = getGlobalConfig(singleClickConfigKey());

  setGlobalConfig(GUEST_CHECKOUT_KEY, '1');
  // On, deliberately: with it off, "no save-card control for a guest" would pass
  // for the wrong reason — nobody would be offered one.
  setGlobalConfig(singleClickConfigKey(), '1');

  // The card method is assigned to the payments phase in the registry, and this
  // project has no cfg-payments dependency of its own.
  setMethodConfig(CARD.id, { enabled: true, api: 'payments' });
});

test.afterAll(() => {
  restoreGlobalConfig(GUEST_CHECKOUT_KEY, previousGuestCheckout);
  restoreGlobalConfig(singleClickConfigKey(), previousSingleClick);
});

test('a guest reaches the payment step, is offered the card method, and is not offered to save a card', async ({
  guestPage,
}) => {
  test.setTimeout(180_000);

  const guest = new GuestCheckoutPage(guestPage);
  const email = await guest.startAsGuest({ emailSuffix: 'nosave' });

  // The shop must genuinely have created a guest, not an account: `isGuest` is
  // what the module's template branches on.
  expect(
    querySingleValue(
      `SELECT is_guest FROM ps_customer WHERE email = '${email}' ORDER BY id_customer DESC LIMIT 1`
    ),
    `${email} was not created as a guest customer`
  ).toBe('1');

  const option = guest.checkout.paymentOption(CARD.label, CARD.notLabel);
  skipIfDisconnected((await option.count()) === 0, 'the card method is not offered at the payment step');

  await guest.checkout.selectMethod(CARD.label, CARD.notLabel);

  // Mollie Components must still mount for a guest — paying by card is allowed.
  await expect(
    guestPage.locator(`#card-number-${CARD.id} iframe`),
    'the card fields did not mount for a guest'
  ).toHaveCount(1);

  // …but neither single-click control may be rendered.
  await expect(
    guestPage.locator('#mollie-save-card'),
    'a guest was offered "Save card" even though the module wraps it in {if !$isGuest}'
  ).toHaveCount(0);
  await expect(
    guestPage.locator('#mollie-use-saved-card'),
    'a guest was offered "Use saved card"'
  ).toHaveCount(0);
});

for (const [label, card] of [
  ['a 3-D Secure enrolled card', CARD_3DS],
  ['a card that is not 3-D Secure enrolled', CARD_NON_3DS],
] as const) {
  test(`a guest pays with ${label}`, async ({ guestPage }) => {
    requiresPublicHost();
    test.setTimeout(240_000);

    const guest = new GuestCheckoutPage(guestPage);
    const email = await guest.startAsGuest({ emailSuffix: card.number.slice(0, 4) });

    const option = guest.checkout.paymentOption(CARD.label, CARD.notLabel);
    skipIfDisconnected((await option.count()) === 0, 'the card method is not offered at the payment step');

    await guest.checkout.selectMethod(CARD.label, CARD.notLabel);
    await new MollieComponentsForm(guestPage).fill(CARD.id, card);
    await guest.checkout.acceptTerms();
    await guest.checkout.placeOrder();

    // The sandbox asks for an outcome for the 3-D Secure enrolled card and
    // settles the other one itself, so what is asserted is the paid order both
    // paths must end in — not the presence of the picker.
    const hosted = new HostedCheckoutPage(guestPage);
    const askedForOutcome = await hosted.chooseOutcomeIfOffered('paid');
    test.info().annotations.push({
      type: 'note',
      description: askedForOutcome
        ? 'the sandbox asked for an outcome'
        : 'the sandbox completed the payment without asking for an outcome',
    });
    await guest.checkout.expectConfirmation();

    const reference = await guest.checkout.getOrderReference();
    expect(reference, 'no order reference on the guest confirmation page').toMatch(/^[A-Z0-9]{6,12}$/);

    // The order must belong to the guest the checkout created, not to a
    // logged-in customer that leaked in from another spec's session.
    expect(
      querySingleValue(
        `SELECT c.is_guest FROM ps_orders o JOIN ps_customer c ON c.id_customer = o.id_customer ` +
        `WHERE o.reference = '${reference}' LIMIT 1`
      ),
      `order ${reference} was not placed by a guest customer`
    ).toBe('1');
    expect(
      querySingleValue(
        `SELECT c.email FROM ps_orders o JOIN ps_customer c ON c.id_customer = o.id_customer ` +
        `WHERE o.reference = '${reference}' LIMIT 1`
      )
    ).toBe(email);
  });
}
