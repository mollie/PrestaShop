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
}
