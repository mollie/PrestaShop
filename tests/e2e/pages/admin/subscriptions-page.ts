import type { Page } from '@playwright/test';
import { dismissInvalidTokenWall } from '../../helpers/orders';

export class SubscriptionsPage {
  constructor(private page: Page) {}

  async gotoOrders() {
    await this.page.goto('/admin1/index.php?controller=AdminMollieSubscriptionOrders');
    await dismissInvalidTokenWall(this.page);
    await this.page.locator('#invertus_mollie_subscription_grid_panel').waitFor({ timeout: 20_000 });
  }

  async gotoFAQ() {
    await this.page.goto('/admin1/index.php?controller=AdminMollieSubscriptionFAQ');
    await dismissInvalidTokenWall(this.page);
    await this.page.getByText('Subscription creation').first().waitFor({ timeout: 20_000 });
  }

  async selectCarrierAndSave(optionIndex: number) {
    await this.page.locator('#form_carrier').selectOption({ index: optionIndex });
    await this.page.getByText('Save').click();
    await this.page.getByText('Options saved successfully.').waitFor({ timeout: 10_000 });
  }
}
