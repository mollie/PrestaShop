import { expect, type Page } from '@playwright/test';
import { CheckoutPage } from './checkout-page';

/** Any product in the seeded catalogue works; #1 is the Hummingbird t-shirt. */
const DEFAULT_PRODUCT_ID = 1;

/**
 * Address data for the guest, mirroring the seeded NL worker address so the
 * cart lands in the same country the method registry assumes.
 */
const GUEST_ADDRESS = {
  address1: 'Rijksstraatweg 161',
  postcode: '1969 LE',
  city: 'Heemskerk',
  country: 'Netherlands',
  phone: '0251 232 417',
};

/**
 * Checkout as a guest — no account, no stored addresses, so both the personal
 * information and the address steps are filled in by hand. This is the one
 * checkout shape the worker-customer fixture cannot express, and the module has
 * a real branch behind it: every single-click control is wrapped in
 * `{if !$isGuest}` (`views/templates/hook/mollie_iframe.tpl`), so a guest must
 * never be offered "Save card".
 *
 * Composes `CheckoutPage` rather than extending it: only the first two steps
 * differ, and the payment step is identical for a guest.
 */
export class GuestCheckoutPage {
  readonly checkout: CheckoutPage;

  constructor(private page: Page) {
    this.checkout = new CheckoutPage(page);
  }

  /**
   * Walks a brand-new guest from an empty cart to the payment step and returns
   * the email address it registered, so the caller can assert against the
   * customer row the shop created.
   *
   * The email is unique per call — PrestaShop refuses a guest whose address is
   * already taken by a registered customer, and a re-run must not collide with
   * the row the previous run left behind.
   */
  async startAsGuest(options: { productId?: number; quantity?: number; emailSuffix?: string } = {}): Promise<string> {
    const productId = options.productId ?? DEFAULT_PRODUCT_ID;
    const email = `e2e-guest-${options.emailSuffix ?? 'x'}-${Date.now()}@mollie-test.invertus.eu`;

    await this.checkout.addProduct(options.quantity ?? 1, productId);
    await this.page.goto('/en/order');

    await this.fillPersonalInformation(email);
    await this.fillNewAddress();
    await this.checkout.confirmShipping();

    return email;
  }

  /**
   * The guest tab of the personal-information step. `#customer-form` scoping is
   * required, not cosmetic: the sign-in tab in the same step carries a second
   * `#field-email`, and an unscoped locator matches both.
   */
  private async fillPersonalInformation(email: string) {
    const form = this.page.locator('#customer-form');
    await form.waitFor({ timeout: 20_000 });

    await form.locator('#field-firstname').fill('Playwright');
    await form.locator('#field-lastname').fill('Guest');
    await form.locator('#field-email').fill(email);
    // The password field is present but optional in the guest tab; leaving it
    // empty is what makes PrestaShop create a guest rather than an account.

    // Which consent boxes exist depends on the shop's GDPR modules and privacy
    // settings, so every required one is ticked rather than a fixed list.
    const consents = form.locator('input[type="checkbox"][required]');
    for (let i = 0; i < (await consents.count()); i++) {
      await consents.nth(i).check({ force: true });
    }

    await form.locator('button[name="continue"]').click();
  }

  /**
   * The new-address form. Country first, then the text fields: changing the
   * country makes the theme refetch the address format over AJAX and re-render
   * the whole form, dropping anything already typed.
   */
  private async fillNewAddress() {
    const step = this.page.locator('#checkout-addresses-step');
    const country = step.locator('#field-id_country');
    await country.waitFor({ timeout: 20_000 });

    // Retried, because the theme also refetches the format for the DEFAULT
    // country while the step is still rendering: a selection made before that
    // response lands is overwritten by it, and the form comes back on the
    // default country (the US on this seed, whose format then requires a State
    // the form never filled — which is how the step gets stuck with no visible
    // complaint at all).
    for (let attempt = 0; attempt < 4; attempt++) {
      await country.selectOption({ label: GUEST_ADDRESS.country }).catch(() => {});
      await this.page.waitForTimeout(1_500);
      if ((await this.selectedCountry()) === GUEST_ADDRESS.country) break;
    }
    expect(
      await this.selectedCountry(),
      'the address form kept resetting the country away from ' + GUEST_ADDRESS.country
    ).toBe(GUEST_ADDRESS.country);

    await step.locator('#field-address1').fill(GUEST_ADDRESS.address1);
    await step.locator('#field-city').fill(GUEST_ADDRESS.city);
    await step.locator('#field-postcode').fill(GUEST_ADDRESS.postcode);
    // Optional on some shops, required on others; filling it is harmless and
    // keeps the address valid enough to leave #not-valid-addresses.
    await step.locator('#field-phone').fill(GUEST_ADDRESS.phone).catch(() => {});

    // Whether a State is part of the format is the country's business, so it is
    // filled when the format asks for it rather than never or always.
    const state = step.locator('#field-id_state');
    if (await state.isVisible().catch(() => false)) {
      await state.selectOption({ index: 1 }).catch(() => {});
    }

    await step.locator('button[name="confirm-addresses"]').click();

    // A rejected address leaves the step current with its complaint inline, and
    // the next step then times out on a locator that says nothing about why.
    const delivery = this.page.locator('#checkout-delivery-step');
    const accepted = await this.page
      .waitForFunction(
        () => !(document.querySelector('#checkout-delivery-step')?.className ?? '').includes('-unreachable'),
        undefined,
        { timeout: 30_000 }
      )
      .then(() => true)
      .catch(() => false);

    if (!accepted) {
      const complaints = (await step.locator('.js-address-error, .help-block, .alert').allInnerTexts())
        .map((t) => t.trim())
        .filter(Boolean)
        .join(' | ');
      // PrestaShop rejects an invalid address field with HTML validation only,
      // which leaves no message on the page at all — so report which fields the
      // browser itself considers invalid.
      const invalid = await step.locator(':invalid').evaluateAll((els) =>
        els.map((el) => `${el.getAttribute('name') ?? el.tagName}="${(el as HTMLInputElement).value ?? ''}"`)
      );
      throw new Error(
        `the guest address was not accepted; the checkout is still on the addresses step. ` +
          `Form said: ${complaints || '(nothing)'}; invalid fields: ${invalid.join(', ') || '(none)'}; ` +
          `country: ${await this.selectedCountry()}; delivery step class: ${await delivery.getAttribute('class')}`
      );
    }
  }

  /** The country the address form is currently rendered for, by its visible name. */
  private async selectedCountry(): Promise<string> {
    return this.page
      .locator('#checkout-addresses-step #field-id_country option:checked')
      .first()
      .innerText()
      .then((t) => t.trim())
      .catch(() => '(none)');
  }
}
