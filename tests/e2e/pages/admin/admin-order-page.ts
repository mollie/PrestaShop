import type { Page } from '@playwright/test';
import { findOrderByReference } from '../../helpers/orders';

export class AdminOrderPage {
  constructor(private page: Page) {}

  async gotoByReference(reference: string) {
    const row = await findOrderByReference(this.page, reference);
    await row.click();
    await this.page.waitForLoadState('domcontentloaded');
  }

  async ship(carrier: string, code: string, url: string) {
    await this.page.locator('.btn-group > [title=""]').first().waitFor({ state: 'visible' });
    await this.page.locator('.btn-group > [title=""]').first().click();
    await this.page.locator('.swal-modal').waitFor();
    await this.page.locator('#input-carrier').fill(carrier);
    await this.page.locator('#input-code').fill(code);
    await this.page.locator('#input-url').fill(url);
    await this.page.locator(':nth-child(2) > .swal-button').click();
    await this.page.getByText('Shipment was made successfully!').waitFor({ timeout: 10_000 });
  }

  async refund() {
    await this.page.locator('.btn-group-action > .btn-group > .dropdown-toggle').first().click();
    await this.page.getByRole('button').nth(2).click();
    await this.page.locator('.swal-button--confirm').click();
    await this.page.locator('.alert-success').waitFor({ timeout: 10_000 });
  }
}
