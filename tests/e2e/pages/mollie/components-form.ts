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
 * The two cards the Cypress suite distinguished: a Mastercard enrolled for 3-D
 * Secure and a Visa that is not. In test mode neither is charged and the outcome
 * is still chosen on Mollie's sandbox page, so what these actually cover is that
 * the module tokenises and completes a payment for either scheme — not a real
 * 3-D Secure challenge, which Mollie's test mode does not present.
 */
export const CARD_3DS = {
  holder: 'T. TESTER',
  number: '5555555555554444',
  expiry: '12/30',
  cvc: '222',
};

export const CARD_NON_3DS = {
  holder: 'T. TESTER',
  number: '4242424242424242',
  expiry: '12/30',
  cvc: '222',
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
      // Punctuation-insensitive: the components reformat as you type (spaces in
      // the card number, the / in the expiry), so only letters and digits count.
      const normalize = (s: string) => s.replace(/[^a-z0-9]/gi, '');

      for (let attempt = 1; ; attempt++) {
        await input.click();
        // Typed key by key, not fill(): the components mask/format as you type
        // and validate on real input events — a programmatic value assignment
        // leaves them "empty" as far as tokenisation is concerned.
        await input.pressSequentially(value, { delay: 30 });

        // The component attaches its key handlers asynchronously after the
        // iframe mounts, and a keystroke that lands before then is silently
        // dropped — seen in CI as "Card number is too short" with exactly the
        // leading digit missing. Read back what actually arrived and retype
        // until the field holds the full value.
        if (normalize(await input.inputValue()) === normalize(value)) break;
        if (attempt >= 3) {
          throw new Error(
            `${container} ${inputId}: typed "${value}" but the field holds ` +
              `"${await input.inputValue()}" after ${attempt} attempts`
          );
        }
        // Cleared with key events for the same reason the value is typed:
        // the mask only tracks real keyboard input.
        await input.press('ControlOrMeta+a');
        await input.press('Backspace');
      }
    }
    // Blur the last field so its validation runs before the form submits.
    await this.page.locator('#payment-confirmation').click({ position: { x: 1, y: 1 } }).catch(() => {});
  }
}
