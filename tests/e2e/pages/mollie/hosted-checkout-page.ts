import type { Page } from '@playwright/test';

export type Outcome = 'paid' | 'failed' | 'canceled' | 'expired' | 'authorized';

/** Mollie's sandbox pages, shown instead of a real bank/card flow. */
export class HostedCheckoutPage {
  /**
   * Failures Mollie's own host returned, newest last.
   *
   * Worth recording because the symptom is otherwise unreadable: when Mollie
   * answers the hosted checkout with 403 — its bot protection throttling a CI
   * runner's IP — the page renders without its own stylesheet and jQuery, so the
   * outcome picker never appears and every test in the phase fails with
   * `waiting for locator('[value="paid"]')`. That looks like a module or spec
   * problem and is neither. A whole CI investigation went into finding this out
   * once; the message now says it.
   */
  private upstreamFailures: string[] = [];

  constructor(private page: Page) {
    page.on('response', (response) => {
      if (response.status() < 400) return;
      const url = response.url();
      let host: string;
      try {
        host = new URL(url).hostname;
      } catch {
        return; // data:/blob: and friends carry no host to attribute this to
      }
      if (!/(^|\.)mollie\.com$/.test(host)) return;
      this.upstreamFailures.push(`${response.status()} ${url}`);
    });
  }

  /**
   * Explains a missing control in terms of what Mollie's host did, so a run
   * throttled upstream is not read as a regression in the module.
   */
  private async describeMollieFailure(waitingFor: string): Promise<string> {
    const lines = [
      `Mollie's hosted page never offered ${waitingFor}.`,
      `Current URL: ${this.page.url()}`,
    ];

    if (this.upstreamFailures.length > 0) {
      // Deduplicated: one throttled page load produces the same status for the
      // document and each of its assets.
      const unique = [...new Set(this.upstreamFailures.map((f) => f.split(' ')[0]))];
      lines.push(
        `Mollie answered ${this.upstreamFailures.length} request(s) with ${unique.join('/')} — ` +
          'when that includes the /checkout/ document or its assets, Mollie is refusing this ' +
          'client (its bot protection throttles CI runner IPs) and the page cannot render its ' +
          'outcome picker. That is upstream, not the module.'
      );
      lines.push(...this.upstreamFailures.slice(-5).map((f) => `  ${f}`));
    }

    return lines.join('\n');
  }

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
    try {
      await control.waitFor({ timeout: 30_000 });
    } catch (error) {
      throw new Error(await this.describeMollieFailure(`[value="${outcome}"]`), { cause: error });
    }
    await control.click();
    await this.page.locator('.button.form__button').click();
  }
}
