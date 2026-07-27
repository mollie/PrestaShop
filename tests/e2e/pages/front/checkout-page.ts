import type { Page } from '@playwright/test';

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

  async selectMethod(label: string | RegExp) {
    await this.page.getByText(label).click({ force: true });
  }

  async acceptTerms() {
    await this.page.locator('.condition-label > .js-terms').click({ force: true });
  }

  async placeOrder() {
    await this.page.getByText('Place order').click();
  }

  async expectConfirmation() {
    await this.page
      .locator('#content-hook_order_confirmation > .card-block')
      .waitFor({ timeout: 30_000 });
  }

  async getOrderReference(): Promise<string> {
    const url = new URL(this.page.url());
    const ref = url.searchParams.get('order_number') || url.searchParams.get('reference');
    if (ref) return ref;
    const text = await this.page.locator('#content-hook_order_confirmation').innerText();
    const match = text.match(/([A-Z0-9]{8,})/);
    if (!match) throw new Error('Could not extract order reference from confirmation page');
    return match[1];
  }
}
