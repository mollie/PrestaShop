import type { Page } from '@playwright/test';

export type Outcome = 'paid' | 'failed' | 'canceled' | 'expired' | 'authorized';

/** Mollie's sandbox outcome picker, shown instead of a real bank/card flow. */
export class HostedCheckoutPage {
  constructor(private page: Page) {}

  async chooseOutcome(outcome: Outcome) {
    await this.page.locator(`[value="${outcome}"]`).waitFor({ timeout: 30_000 });
    await this.page.locator(`[value="${outcome}"]`).click();
    await this.page.locator('.button.form__button').click();
  }
}
