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

  /** Whether the browser is still on one of Mollie's own pages. */
  private onMollie(): boolean {
    try {
      return new URL(this.page.url()).hostname.endsWith('mollie.com');
    } catch {
      return false;
    }
  }

  /**
   * Picks the outcome only if the sandbox asks for one, and reports which
   * happened.
   *
   * Not every payment reaches the outcome picker: a card that is not enrolled
   * for 3-D Secure, and a payment created against a saved customer, are
   * completed by the sandbox on the spot and the browser is already on its way
   * back to the shop. Waiting for `[value="paid"]` in those cases times out on a
   * payment that in fact succeeded — which is precisely what the Cypress
   * non-3DS case encoded by expecting the confirmation page directly.
   */
  async chooseOutcomeIfOffered(outcome: Outcome, timeout = 30_000): Promise<boolean> {
    const deadline = Date.now() + timeout;
    let sawIssuerList = false;

    while (Date.now() < deadline) {
      // Left Mollie without ever being asked: the sandbox settled it itself.
      if (!this.onMollie()) return false;

      if ((await this.outcomeControl(outcome).count()) > 0) {
        await this.chooseOutcome(outcome);
        return true;
      }

      // An issuer list stands between the payment and the outcome picker; click
      // once, then keep waiting for the picker it leads to.
      if (!sawIssuerList && (await this.issuerButtons().count()) > 0) {
        sawIssuerList = true;
        await this.issuerButtons().first().click().catch(() => {});
      }

      await this.page.waitForTimeout(500);
    }

    return false;
  }

  async chooseOutcome(outcome: Outcome) {
    await this.selectIssuerIfPresent();

    const control = this.outcomeControl(outcome);
    await control.waitFor({ timeout: 30_000 });
    await control.click();
    await this.page.locator('.button.form__button').click();
  }
}
