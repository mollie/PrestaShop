import type { Page } from '@playwright/test';
import { dismissInvalidTokenWall } from '../../helpers/orders';

export class AuthorizationPage {
  constructor(private page: Page) {}

  async goto() {
    await this.page.goto('/admin1/index.php?controller=AdminModules&configure=mollie');
    await dismissInvalidTokenWall(this.page);
    await this.page.locator('#mollie-authentication-root').waitFor({ timeout: 30_000 });
  }

  async connect(apiKey: string, mode: 'live' | 'test') {
    await this.page.getByTestId(`mollie-mode-${mode}`).click();
    await this.page.getByTestId('mollie-api-key-input').fill(apiKey);
    await this.page.getByTestId('mollie-connect-button').click();
  }

  async isConnected(): Promise<boolean> {
    return this.page.getByTestId('mollie-connection-status').isVisible();
  }
}
