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

  statusMappingHeader() {
    return this.page.getByTestId('advanced-settings-status-mapping-toggle');
  }

  statusMappingRow(configKey: string) {
    return this.page.getByTestId(`status-mapping-row-${configKey}`);
  }

  /** The row's dropdown button, its text is the selected status. */
  statusMappingButton(configKey: string) {
    return this.statusMappingRow(configKey).locator('.radio-select-button');
  }

  /** Opens the row's dropdown, returns its option labels and closes it again. */
  async statusMappingOptions(configKey: string): Promise<string[]> {
    const options = this.statusMappingRow(configKey).locator('.radio-select-option-label');
    await this.statusMappingButton(configKey).click();
    // allInnerTexts() does not wait, so wait for the dropdown to render first.
    await options.first().waitFor();
    const labels = await options.allInnerTexts();
    await this.statusMappingButton(configKey).click();

    return labels;
  }

  async selectStatusMapping(configKey: string, label: string) {
    await this.statusMappingButton(configKey).click();
    await this.statusMappingRow(configKey).locator('.radio-select-option', { hasText: label }).click();
  }

  emailStatusesHeader() {
    return this.page.getByTestId('advanced-settings-email-statuses-toggle');
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
