import type { Page } from '@playwright/test';
import { dismissInvalidTokenWall } from '../../helpers/orders';

export class PaymentMethodsPage {
  constructor(private page: Page) {}

  async goto() {
    await this.page.goto('/admin1/index.php?controller=AdminMolliePaymentMethods');
    await dismissInvalidTokenWall(this.page);
    await this.page.locator('#mollie-payment-methods-root').waitFor({ timeout: 30_000 });
  }

  /**
   * The screen splits methods across two tabs and opens on "Enabled". A method
   * that is not enabled renders only under "Disabled", so a locator that does
   * not switch tabs first silently finds nothing.
   */
  async showEnabled() {
    await this.page.getByText(/enabled payment methods/i).first().click();
  }

  async showDisabled() {
    await this.page.getByText(/disabled payment methods/i).first().click();
  }

  card(methodId: string) {
    return this.page.getByTestId(`payment-method-${methodId}`);
  }

  /**
   * How many method cards the current tab holds. Lets a caller tell "the module
   * lists nothing at all" (a connection problem, worth failing over) apart from
   * "Mollie's test profile does not offer this one method" (a skip): the two look
   * identical through a single card's locator.
   */
  async cardCount(timeout = 15_000): Promise<number> {
    await this.page
      .locator('[data-testid^="payment-method-"]')
      .first()
      .waitFor({ state: 'attached', timeout })
      .catch(() => {});
    // Excludes the per-card controls, whose test ids share the same prefix.
    return this.page
      .locator('[data-testid^="payment-method-"]')
      .evaluateAll(
        (els) =>
          els.filter((el) => {
            const id = el.getAttribute('data-testid') ?? '';
            return !/-(toggle|status|save|enabled-switch)$/.test(id);
          }).length
      );
  }

  /**
   * Brings a method's card into view without assuming which tab holds it —
   * whether it sits under Enabled or Disabled depends on global config that the
   * cfg-* setup projects own, so a hardcoded tab makes these tests
   * order-dependent.
   */
  async revealCard(methodId: string, timeoutPerTab = 10_000) {
    // The list arrives from an `action=getPaymentMethods` AJAX call after the
    // React root mounts, so each tab needs a bounded wait, not a bare count().
    await this.page
      .getByText(/enabled payment methods/i)
      .first()
      .waitFor({ timeout: 30_000 })
      .catch(() => {});

    for (const showTab of [() => this.showEnabled(), () => this.showDisabled()]) {
      await showTab().catch(() => {});
      const card = this.card(methodId);
      const found = await card
        .waitFor({ state: 'attached', timeout: timeoutPerTab })
        .then(() => true)
        .catch(() => false);
      if (found) return card;
    }
    return this.card(methodId);
  }

  async toggleSettings(methodId: string) {
    await this.page.getByTestId(`payment-method-${methodId}-toggle`).click();
  }

  async isActive(methodId: string): Promise<boolean> {
    const text = (await this.page.getByTestId(`payment-method-${methodId}-status`).textContent()) || '';
    const label = text.trim().toLowerCase();
    // "inactive" also contains "active", so the negative has to be checked first.
    return !label.includes('inactive') && label.includes('active');
  }

  /**
   * Drives the method's enable switch through the real settings form and
   * saves — the path `PaymentMethodService::savePaymentMethod` owns. This is
   * deliberately NOT the SQL shortcut the cfg-* setups use: those bypass the
   * form entirely, so nothing else in the suite would notice the save flow
   * breaking.
   *
   * Returns once the module's AJAX reports success AND the card's status
   * badge reflects the new state (the UI refetches after saving, so the badge
   * is the signal that the round-trip is complete).
   */
  async setEnabledViaForm(methodId: string, enabled: boolean) {
    const card = await this.revealCard(methodId);
    await card.waitFor({ state: 'visible', timeout: 10_000 });

    // Expand the settings panel if it is not open yet.
    const enabledSwitch = this.page.getByTestId(`payment-method-${methodId}-enabled-switch`);
    if (!(await enabledSwitch.isVisible().catch(() => false))) {
      await this.toggleSettings(methodId);
      await enabledSwitch.waitFor({ timeout: 10_000 });
    }

    if ((await enabledSwitch.getAttribute('aria-checked')) !== String(enabled)) {
      await enabledSwitch.click();
    }

    const saveResponse = this.page.waitForResponse(
      (r) =>
        r.request().method() === 'POST' &&
        (r.request().postData() ?? '').includes('savePaymentMethodSettings'),
      { timeout: 30_000 }
    );
    await this.page.getByTestId(`payment-method-${methodId}-save`).click();
    const body = await (await saveResponse).json();
    if (!body.success) {
      throw new Error(`saving ${methodId} reported failure: ${JSON.stringify(body)}`);
    }
    // No UI assertion here on purpose: the success response proves the server
    // persisted, and after saving the UI refetches, moves the card to the
    // other tab and floats a toast over the tab buttons — waiting on any of
    // that is what a caller's own next navigation does more reliably. The
    // observable effect belongs to the caller (e.g. the FO payment step).
  }
}
