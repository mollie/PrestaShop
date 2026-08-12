import type { Page } from '@playwright/test';

/**
 * Mollie's documented test card. In test mode nothing is charged and the
 * outcome is chosen on the sandbox page afterwards, but the number must pass
 * the components' client-side validation (Luhn + known scheme) or
 * `mollie.createToken()` refuses to tokenise and the form never submits.
 */
const TEST_CARD = {
  holder: 'T. TESTER',
  number: '4543474002249996',
  expiry: '12/30',
  cvc: '123',
};

/**
 * The inline card form Mollie Components renders at the shop's own payment
 * step (sandbox has MOLLIE_SANDBOX_IFRAME on): four iframes, one field each,
 * mounted into the module's `#<field>-<methodId>` containers
 * (views/templates/hook/mollie_iframe.tpl).
 */
export class MollieComponentsForm {
  constructor(private page: Page) {}

  async fill(methodId = 'creditcard', card = TEST_CARD) {
    // Each iframe holds ONE visible field plus hidden autocomplete-helper
    // inputs (`#cc-number` etc., aria-hidden), so the field is addressed by
    // its id, not by a bare `input`.
    const fields: Array<[string, string, string]> = [
      [`#card-holder-${methodId}`, '#cardHolder', card.holder],
      [`#card-number-${methodId}`, '#cardNumber', card.number],
      [`#expiry-date-${methodId}`, '#expiryDate', card.expiry],
      [`#verification-code-${methodId}`, '#verificationCode', card.cvc],
    ];

    for (const [container, inputId, value] of fields) {
      const input = this.page.frameLocator(`${container} iframe`).locator(inputId);
      await input.click();
      // Typed key by key, not fill(): the components mask/format as you type
      // (spaces in the card number, the / in the expiry) and validate on real
      // input events — a programmatic value assignment leaves them "empty" as
      // far as tokenisation is concerned.
      await input.pressSequentially(value, { delay: 30 });
    }
    // Blur the last field so its validation runs before the form submits.
    await this.page.locator('#payment-confirmation').click({ position: { x: 1, y: 1 } }).catch(() => {});
  }
}
