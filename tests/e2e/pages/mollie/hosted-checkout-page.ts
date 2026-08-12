import type { Page } from '@playwright/test';

export type Outcome = 'paid' | 'failed' | 'canceled' | 'expired' | 'authorized';

/** Mollie's sandbox pages, shown instead of a real bank/card flow. */
export class HostedCheckoutPage {
  constructor(private page: Page) {}

  private outcomeControl(outcome: Outcome) {
    return this.page.locator(`[value="${outcome}"]`);
  }

  /** The bank buttons an issuer-list method (iDEAL, KBC/CBC) shows first. */
  private issuerButtons() {
    return this.page.locator('ul > li button');
  }

  /**
   * An issuer-list method puts a bank chooser in front of the outcome picker, so
   * whichever of the two rendered has to be handled before an outcome exists to
   * click.
   */
  async selectIssuerIfPresent() {
    await Promise.race([
      this.outcomeControl('paid').first().waitFor({ timeout: 30_000 }).catch(() => {}),
      this.issuerButtons().first().waitFor({ timeout: 30_000 }).catch(() => {}),
    ]);

    const hasOutcomes = (await this.outcomeControl('paid').count()) > 0;
    if (!hasOutcomes && (await this.issuerButtons().count()) > 0) {
      await this.issuerButtons().first().click();
    }
  }

  /**
   * Whether the sandbox offers this outcome for the current method — the
   * picker's option set is Mollie's to change, so a missing one is a skip for
   * the caller, not a failure.
   */
  async outcomeAvailable(outcome: Outcome): Promise<boolean> {
    await this.selectIssuerIfPresent();
    await this.outcomeControl('paid').first().waitFor({ timeout: 30_000 }).catch(() => {});
    return (await this.outcomeControl(outcome).count()) > 0;
  }

  async chooseOutcome(outcome: Outcome) {
    await this.selectIssuerIfPresent();

    const control = this.outcomeControl(outcome);
    await control.waitFor({ timeout: 30_000 });
    await control.click();
    await this.page.locator('.button.form__button').click();
  }
}
