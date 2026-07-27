import type { Page } from '@playwright/test';

export class OrderHistoryPage {
  constructor(private page: Page) {}

  async goto() {
    await this.page.goto('/en/index.php?controller=history');
  }

  row(reference: string) {
    return this.page.getByRole('row').filter({ hasText: reference }).first();
  }

  /** Whether the signed-in customer has any order at all. */
  async hasAnyOrder(): Promise<boolean> {
    await this.goto();
    return (await this.page.locator('#content table tbody tr, .order-item').count()) > 0;
  }

  async hasOrder(reference: string): Promise<boolean> {
    await this.goto();
    return (await this.row(reference).count()) > 0;
  }
}
