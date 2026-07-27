import type { Page } from '@playwright/test';
import { dismissInvalidTokenWall } from '../../helpers/orders';

export class PaymentMethodsPage {
  constructor(private page: Page) {}

  async goto() {
    await this.page.goto('/admin1/index.php?controller=AdminMolliePaymentMethods');
    await dismissInvalidTokenWall(this.page);
    await this.page.locator('#mollie-payment-methods-root').waitFor({ timeout: 30_000 });
  }

  card(methodId: string) {
    return this.page.getByTestId(`payment-method-${methodId}`);
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
