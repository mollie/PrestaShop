import type { Page } from '@playwright/test';
import { getOrderReferenceById } from '../../helpers/orders';

/**
 * The seeded worker customers each carry one address per alias below, so the
 * billing/shipping country is chosen by clicking the alias.
 */
export type BillingCountry = 'NL' | 'DE' | 'UK' | 'PL' | 'CH';

/** Any product in the seeded catalogue works; #1 is the Hummingbird t-shirt. */
const DEFAULT_PRODUCT_ID = 1;

export class CheckoutPage {
  constructor(private page: Page) {}

  /**
   * Drives a fresh checkout up to the payment step: empties whatever the
   * worker's cart still holds, adds a product, then walks the address and
   * shipping steps. Deliberately does NOT reorder a previous order — a
   * worker customer has no order history to reorder from.
   */
  async start(billingCountry: BillingCountry, options: { quantity?: number } = {}) {
    await this.emptyCart();
    await this.addProduct(options.quantity ?? 1);
    await this.page.goto('/en/order');
    await this.chooseAddress(billingCountry);
    await this.confirmShipping();
  }

  async emptyCart() {
    await this.page.goto('/en/cart?action=show');
    // Each removal re-renders the cart, so re-query rather than iterating a list.
    for (let guard = 0; guard < 20; guard++) {
      const remove = this.page.locator('.remove-from-cart');
      if ((await remove.count()) === 0) return;
      await remove.first().click();
      await this.page.locator('.remove-from-cart').first().waitFor({ state: 'detached' }).catch(() => {});
    }
  }

  async addProduct(quantity = 1, productId: number = DEFAULT_PRODUCT_ID) {
    await this.page.goto(`/en/index.php?id_product=${productId}&controller=product`);
    if (quantity !== 1) {
      await this.page.locator('#quantity_wanted').fill(String(quantity));
    }
    await this.page.locator('.add-to-cart').first().click();
    await this.page.locator('#blockcart-modal, .cart-content').first().waitFor({ timeout: 20_000 });
  }

  /** Picks the delivery (and, by default, invoice) address by its alias. */
  async chooseAddress(alias: BillingCountry) {
    const block = this.page
      .locator('#checkout-addresses-step article')
      .filter({ has: this.page.locator('.address-alias', { hasText: new RegExp(`^${alias}$`) }) })
      .first();
    await block.locator('input[type="radio"]').first().check({ force: true });
    // Continue stays disabled while the chosen address is listed in
    // #not-valid-addresses, which is why the seed rows must pass Validate::isName.
    await this.page.locator('#checkout-addresses-step button[name="confirm-addresses"]').click();
  }

  async confirmShipping() {
    await this.page.locator('#js-delivery > .continue').click();
    await this.page.locator('#checkout-payment-step').waitFor({ timeout: 20_000 });
  }

  /** The payment option a label belongs to, whether or not it is selected. */
  paymentOption(label: string | RegExp) {
    return this.page.locator('.payment-option').filter({ hasText: label }).first();
  }

  /**
   * Checks the option's radio rather than clicking its label text. The theme
   * enables "Place order" from the radio's change event, so a forced click on a
   * matching text node leaves the button disabled and the order is never
   * submitted.
   */
  async selectMethod(label: string | RegExp) {
    const option = this.paymentOption(label);
    await option.locator('input[type="radio"]').first().check();
    // Selecting an option makes the theme re-render the confirmation block, so
    // wait for the submit button to come back before touching anything else.
    await this.confirmButton().waitFor({ state: 'visible', timeout: 20_000 });
  }

  async acceptTerms() {
    // check(), not click(force), so the checkbox is verified as actually ticked —
    // the submit button stays disabled otherwise.
    await this.page.locator('.condition-label > .js-terms').check({ force: true });
  }

  confirmButton() {
    return this.page.locator('#payment-confirmation button[type="submit"]').first();
  }

  /**
   * Submits the order and waits until the browser has actually left the
   * checkout step — for a Mollie method that means Mollie's own hosted page,
   * which lives on another origin.
   */
  async placeOrder() {
    const button = this.confirmButton();
    await button.waitFor({ state: 'visible' });
    // Both conditions matter. Before the theme's JS initialises, the button
    // already carries the `disabled` CLASS but not yet the disabled ATTRIBUTE,
    // so checking `disabled` alone passes instantly and the click lands on a
    // button that is not wired up yet — the order is silently never submitted.
    // Once the theme is ready and a method plus the terms box are set, neither
    // the attribute nor the class is present.
    await this.page.waitForFunction(
      () => {
        const b = document.querySelector<HTMLButtonElement>('#payment-confirmation button[type="submit"]');
        return !!b && !b.disabled && !b.classList.contains('disabled');
      },
      undefined,
      { timeout: 30_000 }
    );
    await button.click();
    // waitUntil: 'commit', not the default 'load'. Submitting hands off to a
    // module controller that immediately 302s on to Mollie, so that URL matches
    // the predicate but never fires a load event — waiting for 'load' hangs
    // until timeout even though the browser does reach Mollie.
    await this.page.waitForURL((url) => !url.pathname.replace(/\/$/, '').endsWith('/order'), {
      timeout: 60_000,
      waitUntil: 'commit',
    });
  }

  async expectConfirmation() {
    await this.page
      .locator('#content-hook_order_confirmation > .card-block')
      .waitFor({ timeout: 30_000 });
  }

  /**
   * A PrestaShop order reference is nine random uppercase letters — and so is
   * the word CONFIRMED on the confirmation page, which is exactly what a text
   * scrape picks up. The confirmation URL carries `id_order`, so resolve the
   * reference from that instead of guessing at the copy.
   */
  async getOrderReference(): Promise<string> {
    const url = new URL(this.page.url());
    const direct = url.searchParams.get('order_number') || url.searchParams.get('reference');
    if (direct) return direct;

    const idOrder = url.searchParams.get('id_order');
    if (idOrder) {
      const reference = getOrderReferenceById(idOrder);
      if (reference) return reference;
    }

    // Last resort: the confirmation block labels the reference explicitly.
    const text = await this.page.locator('#content-hook_order_confirmation').innerText();
    const labelled = text.match(/reference[^A-Z]*([A-Z]{9})/i);
    if (labelled) return labelled[1];

    throw new Error(`Could not determine order reference from ${this.page.url()}`);
  }
}
