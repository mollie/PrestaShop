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

  async ship(carrier: string, trackingNumber: string, trackingUrl: string) {
    await this.page.locator('.mollie-ship-btn').first().click();

    const modal = this.page.locator('#mollieShipModal');
    await modal.waitFor({ state: 'visible', timeout: 20_000 });
    await modal.locator('#mollie-carrier').fill(carrier);
    await modal.locator('#mollie-tracking-number').fill(trackingNumber);
    await modal.locator('#mollie-tracking-url').fill(trackingUrl);

    await this.page.locator('#mollieShipModalConfirm').click();
    await modal.waitFor({ state: 'hidden', timeout: 30_000 });
  }

  async refund() {
    await this.page.locator('.mollie-refund-btn').first().click();

    const modal = this.page.locator('#mollieRefundModal');
    await modal.waitFor({ state: 'visible', timeout: 20_000 });
    await this.page.locator('#mollieRefundModalConfirm').click();
    await modal.waitFor({ state: 'hidden', timeout: 30_000 });
  }

  shipButton() {
    return this.page.locator('.mollie-ship-btn');
  }

  refundButton() {
    return this.page.locator('.mollie-refund-btn');
  }
}
