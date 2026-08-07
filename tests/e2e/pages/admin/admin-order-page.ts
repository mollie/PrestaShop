import type { Page } from '@playwright/test';
import { findOrderByReference, dismissInvalidTokenWall } from '../../helpers/orders';

/**
 * The Mollie panel on the BO order view.
 *
 * The redesign replaced the old SweetAlert dialogs with Bootstrap modals, so the
 * Cypress-era selectors (`.swal-modal`, `#input-carrier`, `.btn-group >
 * [title=""]`) no longer match anything. These are the current hooks, confirmed
 * against a running shop.
 */
export class AdminOrderPage {
  constructor(private page: Page) {}

  async gotoByReference(reference: string) {
    const row = await findOrderByReference(this.page, reference);

    // Clicking the row body does not navigate, and a bare href*="/view" matches
    // the customer link in the same row first — which opens in a new tab, so the
    // current page never navigates. Match the order's own view link.
    const viewLink = row.locator('a[href*="/sell/orders/"][href*="/view"]').first();
    if (await viewLink.count()) {
      await viewLink.click();
    } else {
      await row.click();
    }

    await this.page.waitForURL(/\/view/, { timeout: 30_000, waitUntil: 'commit' });
    await dismissInvalidTokenWall(this.page);
  }

  /**
   * Reports a shipment, or returns false when Mollie does not consider the order
   * shippable.
   *
   * order_info.tpl renders the button `disabled` when `!canShip`, and clicking a
   * disabled button silently does nothing. Shippability follows Mollie's view of
   * the order, which lands via webhook, so the state is polled briefly before
   * giving up rather than read once.
   */

  /**
   * The Mollie panel's buttons do nothing until `order_info.js` binds its jQuery
   * click handlers on DOM ready. Clicking before that is silently swallowed and
   * the modal never opens, so wait for the handler itself to exist rather than
   * for mere visibility.
   */
  private async waitForClickHandler(selector: string) {
    await this.page
      .waitForFunction(
        (sel) => {
          const el = document.querySelector(sel);
          if (!el) return false;
          const jq = (window as unknown as { jQuery?: any }).jQuery;
          if (!jq || typeof jq._data !== 'function') return true; // cannot introspect
          const events = jq._data(el, 'events');
          return !!(events && events.click && events.click.length);
        },
        selector,
        { timeout: 30_000 }
      )
      .catch(() => {});
  }

  async ship(carrier: string, trackingNumber: string, trackingUrl: string): Promise<boolean> {
    const button = () => this.shipButton().first();
    await button().waitFor({ state: 'visible', timeout: 20_000 });
    await this.waitForClickHandler('.mollie-ship-btn');

    for (let attempt = 0; attempt < 4 && (await button().isDisabled()); attempt++) {
      await this.page.waitForTimeout(3_000);
      await this.page.reload();
      await button().waitFor({ state: 'visible', timeout: 20_000 });
    }
    if (await button().isDisabled()) return false;

    await button().click();

    const modal = this.page.locator('#mollieShipModal');
    await modal.waitFor({ state: 'visible', timeout: 20_000 });
    await modal.locator('#mollie-carrier').fill(carrier);
    await modal.locator('#mollie-tracking-number').fill(trackingNumber);
    await modal.locator('#mollie-tracking-url').fill(trackingUrl);

    await this.page.locator('#mollieShipModalConfirm').click();
    await modal.waitFor({ state: 'hidden', timeout: 30_000 });

    // Reporting a shipment captures an authorised payment, which changes what the
    // panel offers next (refund only becomes available once captured). The panel
    // is server-rendered, so reload before reading it again.
    await this.page.reload();
    await this.page
      .locator('.mollie-refund-btn, .mollie-ship-btn')
      .first()
      .waitFor({ state: 'visible', timeout: 20_000 })
      .catch(() => {});
    return true;
  }

  /**
   * Waits for the refund control to show up, reloading as it goes.
   *
   * The Mollie panel is server-rendered and only offers a refund once Mollie
   * reports the payment as captured. That confirmation arrives by webhook,
   * seconds after the shipment call returns, so the control routinely lags the
   * redirect back into the BO. A plain `expect(...).toBeVisible()` on the
   * default 5s budget catches it only when the webhook happens to be quick,
   * which is why the same method passed and failed across consecutive runs.
   */
  async waitForRefundControl(timeoutMs = 45_000): Promise<boolean> {
    const deadline = Date.now() + timeoutMs;
    const visible = () =>
      this.refundButton()
        .first()
        .isVisible()
        .catch(() => false);

    while (Date.now() < deadline) {
      if (await visible()) return true;
      await this.page.waitForTimeout(3_000);
      await this.page.reload().catch(() => {});
    }
    return visible();
  }

  /**
   * Refunds the order, or reports that Mollie does not currently allow it.
   *
   * order_info.tpl renders the button `disabled` when `!canRefund`, and clicking
   * a disabled button silently does nothing — so the modal must not be waited on
   * unconditionally. Refundability is Mollie's decision and varies by method.
   */
  async refund(): Promise<boolean> {
    const button = this.refundButton().first();
    await button.waitFor({ state: 'visible', timeout: 20_000 });
    if (await button.isDisabled()) return false;

    await this.waitForClickHandler('.mollie-refund-btn');
    await button.click();
    const modal = this.page.locator('#mollieRefundModal');
    await modal.waitFor({ state: 'visible', timeout: 20_000 });
    await this.page.locator('#mollieRefundModalConfirm').click();
    await modal.waitFor({ state: 'hidden', timeout: 30_000 });
    return true;
  }

  shipButton() {
    return this.page.locator('.mollie-ship-btn');
  }

  refundButton() {
    return this.page.locator('.mollie-refund-btn');
  }

  /* ------------------------------------------------------------------ *
   * Amount-based refund — Payments API only.
   *
   * A different widget from the per-line `.mollie-refund-btn` above:
   * order_info.tpl renders `#mollie-refund-amount` + `#mollie-initiate-refund`
   * only under `{if $mollie_api_type == 'payments'}`. The input is pre-filled
   * with the still-refundable amount and both are `disabled` once
   * `$isRefunded || $refundable_amount <= 0`, so a partial refund is expressed
   * by overwriting the input with less than that.
   * ------------------------------------------------------------------ */

  refundAmountInput() {
    return this.page.locator('#mollie-refund-amount');
  }

  initiateRefundButton() {
    return this.page.locator('#mollie-initiate-refund');
  }

  /**
   * The amount Mollie still considers refundable, as the panel advertises it,
   * or null when the control is not rendered at all (every Orders-API order).
   */
  async refundableAmount(): Promise<number | null> {
    const input = this.refundAmountInput();
    // Guarded: a successful refund schedules `location.reload()` 1.5s later, so
    // a read that lands mid-navigation throws rather than returning a stale value.
    const raw = await input
      .first()
      .inputValue({ timeout: 5_000 })
      .catch(() => null);
    if (raw === null) return null;
    const value = Number(raw);
    return Number.isFinite(value) ? value : null;
  }

  /**
   * Waits for the amount control to become usable, reloading as it goes.
   *
   * Same lag as `waitForRefundControl`: `$refundable_amount` is computed from
   * what Mollie reports, and Mollie only reports the payment as refundable once
   * the paid webhook has landed. Until then the input renders `disabled` with a
   * value of 0.
   */
  async waitForRefundAmountControl(timeoutMs = 45_000): Promise<boolean> {
    const deadline = Date.now() + timeoutMs;
    const usable = async () => {
      const input = this.refundAmountInput();
      if ((await input.count()) === 0) return false;
      if (await input.isDisabled().catch(() => true)) return false;
      return ((await this.refundableAmount()) ?? 0) > 0;
    };

    while (Date.now() < deadline) {
      if (await usable()) return true;
      await this.page.waitForTimeout(3_000);
      await this.page.reload().catch(() => {});
    }
    return usable();
  }

  /**
   * Refunds `amount` rather than the whole order, or reports that Mollie does
   * not currently allow it — same contract as `refund()`.
   *
   * `order_info.js` reads the input on click, refuses anything <= 0, and only
   * then opens the shared `#mollieRefundModal`; confirming posts `refundAmount`
   * to the module's AJAX controller.
   */
  async partialRefund(amount: number): Promise<boolean> {
    const input = this.refundAmountInput();
    const button = this.initiateRefundButton();
    if ((await input.count()) === 0 || (await button.count()) === 0) return false;
    if ((await input.isDisabled()) || (await button.isDisabled())) return false;

    await this.waitForClickHandler('#mollie-initiate-refund');
    // The input carries a `max`; a value above it makes the browser mark the
    // field invalid and the module rejects it server-side, so never round up.
    await input.fill(amount.toFixed(2));
    await button.click();

    const modal = this.page.locator('#mollieRefundModal');
    await modal.waitFor({ state: 'visible', timeout: 20_000 });
    await this.page.locator('#mollieRefundModalConfirm').click();
    await modal.waitFor({ state: 'hidden', timeout: 30_000 });
    return true;
  }

  /**
   * The outcome of the refund AJAX, read from the alert `order_info.js` prepends
   * into the panel.
   *
   * Both branches render synchronously in the `$.ajax` handlers, so this is not
   * a race — but the success branch also schedules `location.reload()` 1.5s
   * later, which is why the alert is read rather than asserted on afterwards.
   */
  async refundOutcome(timeoutMs = 30_000): Promise<{ ok: boolean; message: string }> {
    const panel = this.page.locator('.mollie-order-info-panel');
    const success = panel.locator('.alert-success').first();
    const failure = panel.locator('.alert-danger').first();

    const deadline = Date.now() + timeoutMs;
    while (Date.now() < deadline) {
      if (await success.isVisible().catch(() => false)) {
        return { ok: true, message: (await success.innerText().catch(() => '')).trim() };
      }
      if (await failure.isVisible().catch(() => false)) {
        return { ok: false, message: (await failure.innerText().catch(() => '')).trim() };
      }
      await this.page.waitForTimeout(250);
    }
    return { ok: false, message: 'no refund alert appeared within the timeout' };
  }

  /**
   * Polls the reloaded panel until the still-refundable amount has actually
   * dropped below `previous`.
   *
   * This is the assertion that proves the refund landed: the success alert only
   * proves the AJAX call returned `success`, whereas this re-reads the amount
   * the module recomputes from Mollie on the next render.
   */
  async waitForRefundableAmountBelow(previous: number, timeoutMs = 60_000): Promise<number | null> {
    const deadline = Date.now() + timeoutMs;
    let latest: number | null = null;

    while (Date.now() < deadline) {
      latest = await this.refundableAmount();
      if (latest !== null && latest < previous) return latest;
      await this.page.waitForTimeout(3_000);
      await this.page.reload().catch(() => {});
    }
    return latest !== null && latest < previous ? latest : null;
  }
}
