import type { Locator, Page } from '@playwright/test';

/**
 * The customer's account page. The module adds its "Subscriptions" tile through
 * `hookDisplayCustomerAccount`, which renders nothing unless
 * `SubscriptionAvailabilityProvider::isAvailableForCustomer` says yes — so the
 * tile's presence is the observable half of that gate.
 */
export class MyAccountPage {
  constructor(private page: Page) {}

  async goto() {
    await this.page.goto('/en/my-account');
    await this.page.locator('#identity-link, #history-link').first().waitFor({ timeout: 20_000 });
  }

  subscriptionsLink(): Locator {
    return this.page.getByRole('link', { name: /subscriptions/i });
  }

  /** Opens the module's own FO controller behind the tile. */
  async openSubscriptions() {
    await this.subscriptionsLink().first().click();
    await this.page.locator('#content, .page-content').first().waitFor({ timeout: 20_000 });
  }
}
