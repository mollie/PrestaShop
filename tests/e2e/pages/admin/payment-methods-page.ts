import type { Page } from '@playwright/test';
import { dismissInvalidTokenWall } from '../../helpers/orders';

export class PaymentMethodsPage {
  constructor(private page: Page) {}

  async goto() {
    await this.page.goto('/admin1/index.php?controller=AdminMolliePaymentMethods');
    await dismissInvalidTokenWall(this.page);
    await this.page.locator('#mollie-payment-methods-root').waitFor({ timeout: 30_000 });
  }

  /**
   * The screen splits methods across two tabs and opens on "Enabled". A method
   * that is not enabled renders only under "Disabled", so a locator that does
   * not switch tabs first silently finds nothing.
   */
  async showEnabled() {
    await this.page.getByText(/enabled payment methods/i).first().click();
  }

  async showDisabled() {
    await this.page.getByText(/disabled payment methods/i).first().click();
  }

  card(methodId: string) {
    return this.page.getByTestId(`payment-method-${methodId}`);
  }

  /**
   * Brings a method's card into view without assuming which tab holds it —
   * whether it sits under Enabled or Disabled depends on global config that the
   * cfg-* setup projects own, so a hardcoded tab makes these tests
   * order-dependent.
   */
  async revealCard(methodId: string, timeoutPerTab = 10_000) {
    // The list arrives from an `action=getPaymentMethods` AJAX call after the
    // React root mounts, so each tab needs a bounded wait, not a bare count().
    await this.page
      .getByText(/enabled payment methods/i)
      .first()
      .waitFor({ timeout: 30_000 })
      .catch(() => {});

    for (const showTab of [() => this.showEnabled(), () => this.showDisabled()]) {
      await showTab().catch(() => {});
      const card = this.card(methodId);
      const found = await card
        .waitFor({ state: 'attached', timeout: timeoutPerTab })
        .then(() => true)
        .catch(() => false);
      if (found) return card;
    }
    return this.card(methodId);
  }

  async toggleSettings(methodId: string) {
    await this.page.getByTestId(`payment-method-${methodId}-toggle`).click();
  }

  async isActive(methodId: string): Promise<boolean> {
    const text = (await this.page.getByTestId(`payment-method-${methodId}-status`).textContent()) || '';
    const label = text.trim().toLowerCase();
    // "inactive" also contains "active", so the negative has to be checked first.
    return !label.includes('inactive') && label.includes('active');
  }
}
