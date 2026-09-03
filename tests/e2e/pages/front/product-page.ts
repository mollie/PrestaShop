import type { Locator, Page } from '@playwright/test';

/**
 * The front-office product page, as far as the subscription coverage needs it.
 *
 * The module's attribute group is created with `public_name` "Subscription"
 * (`subscription/Install/AttributeInstaller.php`), and the classic theme renders
 * an attribute group's public name as the select's `aria-label` — so that, not a
 * generated element id, is what identifies the dropdown.
 */
export class FrontProductPage {
  constructor(private page: Page) {}

  async goto(productId: number) {
    await this.page.goto(`/en/index.php?id_product=${productId}&controller=product`);
    await this.page.locator('#main .product-information, #product-details').first().waitFor({ timeout: 20_000 });
  }

  subscriptionSelect(): Locator {
    return this.page.locator('select[aria-label="Subscription"]');
  }

  async subscriptionOptions(): Promise<string[]> {
    return this.subscriptionSelect().locator('option').allInnerTexts();
  }
}
