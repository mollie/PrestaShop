import type { Page } from '@playwright/test';
import { getOrderReferenceById } from '../../helpers/orders';

/**
 * The seeded worker customers each carry one address per alias below, so the
 * billing/shipping country is chosen by clicking the alias.
 */
export type BillingCountry = 'NL' | 'DE' | 'UK' | 'PL' | 'CH';

/** Any product in the seeded catalogue works; #1 is the Hummingbird t-shirt. */
const DEFAULT_PRODUCT_ID = 1;

/**
 * The cheapest SHIPPABLE product in the seeded catalogue, for carts that must
 * land BELOW a method's minimum order value. #1 costs EUR 120 on the PS1785
 * seed, so a single unit of it already clears in3's minimum and cannot express
 * "too small" on that version.
 *
 * Must not be virtual: PrestaShop drops the whole delivery step for a virtual
 * cart, and the checkout walk then waits forever on `#checkout-delivery-step`.
 * #12 is EUR 9 but virtual, which is exactly that trap.
 */
const CHEAPEST_PRODUCT_ID = 8;

export class CheckoutPage {
  constructor(private page: Page) {}

  /**
   * Drives a fresh checkout up to the payment step: empties whatever the
   * worker's cart still holds, adds a product, then walks the address and
   * shipping steps. Deliberately does NOT reorder a previous order — a
   * worker customer has no order history to reorder from.
   *
   * Pass `minTotal` rather than a fixed `quantity` whenever the cart has to
   * clear a payment method's minimum: unit prices differ between the PS8 and
   * PS1785 seeds, so any hard-coded quantity lands on a different total per
   * version.
   */
  async start(
    billingCountry: BillingCountry,
    options: { quantity?: number; minTotal?: number; productId?: number | 'cheapest' } = {}
  ) {
    const productId =
      options.productId === 'cheapest'
        ? CHEAPEST_PRODUCT_ID
        : options.productId ?? DEFAULT_PRODUCT_ID;
    await this.emptyCart();

    let quantity = options.quantity ?? 1;
    if (options.minTotal !== undefined) {
      const unit = await this.unitPrice(productId);
      quantity = Math.max(1, Math.ceil(options.minTotal / unit));
    }

    await this.addProduct(quantity, productId);
    await this.page.goto('/en/order');
    await this.chooseAddress(billingCountry);
    await this.confirmShipping();
  }

  /**
   * Tax-inclusive unit price as the theme advertises it. Each of these carries
   * the raw number in a `content` attribute, so this never parses a
   * currency-formatted, locale-dependent string. More than one selector because
   * the PS8 and PS1785 themes do not agree: PS1785 renders no `[itemprop]` on
   * the price at all.
   */
  private async unitPrice(productId: number = DEFAULT_PRODUCT_ID): Promise<number> {
    await this.page.goto(`/en/index.php?id_product=${productId}&controller=product`);

    const sources = [
      '.current-price-value',
      'meta[property="product:price:amount"]',
      '[itemprop="price"]',
    ];
    for (const selector of sources) {
      const el = this.page.locator(selector).first();
      if ((await el.count()) === 0) continue;
      const price = Number(await el.getAttribute('content'));
      if (Number.isFinite(price) && price > 0) return price;
    }
    throw new Error(`Could not read a unit price for product ${productId}`);
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

  /**
   * Re-opens the addresses step when the checkout has already moved past it.
   *
   * PrestaShop's one-page checkout goes straight to the payment step whenever
   * the cart already carries an address and a carrier. The earlier steps
   * collapse to `-complete` with `display: none` content, so their radios stay
   * in the DOM but are invisible and `check()` fails with a bare "Element is
   * not visible" that says nothing about the real cause. A worker that has
   * already run one checkout hits this on its next test, which is why it
   * surfaced on the one method that switches billing country rather than
   * looking like the general ordering problem it is.
   */
  private async openStep(stepId: string, revealed: string) {
    const step = this.page.locator(stepId);
    await step.waitFor({ timeout: 20_000 });

    const target = step.locator(revealed).first();

    // Retried rather than clicked once: the theme re-renders the step list over
    // AJAX, so an opener click can land while the node is being replaced and be
    // lost. Each pass re-reads the state instead of trusting the first look.
    for (let attempt = 0; attempt < 4; attempt++) {
      if (await target.isVisible().catch(() => false)) return;

      const classes = (await step.getAttribute('class')) ?? '';
      // Only a step the checkout has already reached can be opened by clicking.
      // One that is not current *yet* opens on its own, and clicking its title
      // does nothing while the content stays hidden.
      if (!classes.includes('-current') && /-complete|-clickable|-reachable/.test(classes)) {
        // `.step-edit` is the theme's own re-open control; the title is
        // clickable too once the step is `-clickable`, so fall back to it.
        const edit = step.locator('.step-edit').first();
        const opener =
          (await edit.count()) > 0 ? edit : step.locator('.step-title').first();
        await opener.click({ force: true }).catch(() => {});
      }

      await target.waitFor({ state: 'visible', timeout: 5_000 }).catch(() => {});
    }

    await target.waitFor({ state: 'visible', timeout: 10_000 });
  }

  /** Picks the delivery (and, by default, invoice) address by its alias. */
  async chooseAddress(alias: BillingCountry) {
    await this.openStep('#checkout-addresses-step', 'article input[type="radio"]');
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
    // Same collapse as the addresses step: a cart that already has a carrier
    // sends the checkout straight to payment, leaving this step `-complete`
    // with its Continue button present but hidden.
    await this.openStep('#checkout-delivery-step', '#js-delivery .continue');
    await this.page.locator('#js-delivery > .continue').click();
    await this.page.locator('#checkout-payment-step').waitFor({ timeout: 20_000 });
  }

  /** The payment option a label belongs to, whether or not it is selected. */
  paymentOption(label: string | RegExp) {
    return this.page.locator('.payment-option').filter({ hasText: label }).first();
  }

  /**
   * The theme wires the whole payment step from $(document).ready: one
   * delegated change handler on <body> enables "Place order" when a method
   * radio plus every terms checkbox are set. `#checkout-payment-step` becomes
   * visible while the document is still parsing — before theme.js has run —
   * so a click that lands in that window fires a change event no handler
   * receives, and nothing ever re-evaluates the button. PS1785 loses that
   * race on every run; PS8 happens to win it. Waiting for the element is
   * therefore not enough: wait until the delegated handler exists (or the
   * load event has passed, by which point every ready callback has run).
   */
  private async waitForPaymentStepReady() {
    await this.page.waitForFunction(
      () => {
        if (document.readyState === 'complete') return true;
        const $ = (window as any).jQuery;
        const events = $ && $._data && $._data(document.body, 'events');
        const change: Array<{ selector?: string }> | undefined = events && events.change;
        return !!change && change.some((h) => (h.selector ?? '').includes('payment-option'));
      },
      undefined,
      { timeout: 20_000 }
    );
  }

  /**
   * Checks the option's radio rather than clicking its label text. The theme
   * enables "Place order" from the radio's change event, so a forced click on a
   * matching text node leaves the button disabled and the order is never
   * submitted.
   */
  async selectMethod(label: string | RegExp) {
    await this.waitForPaymentStepReady();
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
