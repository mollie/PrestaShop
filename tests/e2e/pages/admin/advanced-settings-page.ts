import type { Page } from '@playwright/test';
import { dismissInvalidTokenWall } from '../../helpers/orders';

export class AdvancedSettingsPage {
  constructor(private page: Page) {}

  async goto() {
    await this.page.goto('/admin1/index.php?controller=AdminMollieAdvancedSettings');
    await dismissInvalidTokenWall(this.page);
    await this.page.locator('#mollie-advanced-settings-root').waitFor({ timeout: 30_000 });
  }

  /** Reaches the page by clicking the module's own sub-tab instead of a deep link. */
  async gotoViaTab() {
    await this.page.locator('#subtab-AdminMollieAdvancedSettingsParent a').first().click({ force: true });
    await this.page.locator('#mollie-advanced-settings-root').waitFor({ timeout: 30_000 });
  }

  /**
   * The form body arrives from an `action=getSettings` AJAX call after the React
   * root mounts, so callers must wait for it rather than probing immediately.
   * Returns false when it never renders (module has no API key connected).
   */
  async waitForForm(timeout = 20_000): Promise<boolean> {
    return this.page
      .getByTestId('advanced-settings-save')
      .waitFor({ state: 'visible', timeout })
      .then(() => true)
      .catch(() => false);
  }

  async save() {
    await this.page.getByTestId('advanced-settings-save').click();
  }

  async expectSavedSuccessfully() {
    await this.page
      .getByTestId('advanced-settings-notification')
      .filter({ hasText: /success/i })
      .waitFor({ timeout: 10_000 });
  }
}
