import type { Page } from '@playwright/test';
import { test, expect } from '../../fixtures/base';
import { AdvancedSettingsPage } from '../../pages/admin/advanced-settings-page';
import { PaymentMethodsPage } from '../../pages/admin/payment-methods-page';
import { CheckoutPage } from '../../pages/front/checkout-page';
import { paymentMethods } from '../../data/payment-methods';
import {
  IMAGES_KEY,
  LOGOS_BIG,
  LOGOS_HIDE,
  LOGOS_NORMAL,
  getGlobalConfig,
  methodImages,
  restoreGlobalConfig,
} from '../../helpers/config';
import { skipIfDisconnected } from '../../helpers/module-state';

/**
 * The Cypress "IN3 logo exists OK" case (C339360), restored — and widened to the
 * setting it was really about. `MOLLIE_IMAGES` decides which of Mollie's images
 * a payment option carries (`Utility/ImageUtility::setOptionImage`):
 *
 *   normal → image.svg     big → image.size2x     hide → no logo at all
 *
 * in3, deliberately: the Cypress case used it, and it is one of the methods
 * whose logo a merchant is most likely to notice, since the option's own text
 * is otherwise a long sentence.
 *
 * Config-mutating and serial: `MOLLIE_IMAGES` is global, so a parallel spec
 * reading the checkout would see whichever value this one last wrote.
 */
test.describe.configure({ mode: 'serial' });

const IN3 = paymentMethods.find((m) => m.id === 'in3')!;

let previousImages: string | null = null;

test.beforeAll(() => {
  previousImages = getGlobalConfig(IMAGES_KEY);
});

test.afterAll(() => {
  restoreGlobalConfig(IMAGES_KEY, previousImages);
});

/**
 * The logo comes from the method row's `images_json`, and only a save through
 * the BO form fills that in — the cfg-* setups write `[]`. So the method is
 * saved through the form first, exactly as a merchant would have done, and only
 * then is the checkout asked about its logo.
 */
test('in3 carries Mollie\'s image set once it has been saved through the BO form', async ({ page }) => {
  test.setTimeout(120_000);

  const methods = new PaymentMethodsPage(page);
  await methods.goto();
  skipIfDisconnected((await methods.cardCount()) === 0, 'the Mollie method list is empty');

  const card = await methods.revealCard(IN3.id, 5_000);
  test.skip(
    (await card.count()) === 0,
    'in3 is not offered by the Mollie test profile — no card in the BO list'
  );

  await methods.setEnabledViaForm(IN3.id, true);

  const images = methodImages(IN3.id);
  expect(images, 'in3: no images_json on the row after saving through the form').not.toBeNull();
  expect(images, 'in3: images_json is still empty after saving through the form').not.toBe('[]');
  // The three keys ImageUtility reads. If Mollie ever renames them the logo
  // silently disappears from the checkout, which is the regression this catches.
  const parsed = JSON.parse(images!);
  expect(parsed, 'in3: images_json holds no svg image').toHaveProperty('svg');
  expect(parsed, 'in3: images_json holds no size2x image').toHaveProperty('size2x');
});

/**
 * Asserted against the row's own image set rather than a URL shape. Mollie used
 * to serve these as `…/payment-methods/in3%402x.png`, which is what the Cypress
 * case matched on literally; they are now opaque
 * `https://www.mollie.com/checkout/files/<uuid>` links, so the only durable
 * statement is the one ImageUtility actually makes — normal renders the `svg`
 * entry and big renders the `size2x` entry.
 */
for (const [setting, imageKey] of [
  [LOGOS_NORMAL, 'svg'],
  [LOGOS_BIG, 'size2x'],
] as const) {
  test(`MOLLIE_IMAGES=${setting} renders the in3 ${imageKey} logo`, async ({ page }) => {
    test.setTimeout(180_000);

    const images = JSON.parse(methodImages(IN3.id) ?? '{}');
    test.skip(
      !images[imageKey],
      `in3 carries no ${imageKey} image — see the images_json test above`
    );

    await setLogoDisplayThroughTheBackOffice(page, setting);

    const checkout = new CheckoutPage(page);
    // in3 is only offered inside its own order-value window.
    await checkout.start(IN3.billingCountry, { minTotal: IN3.minAmount });

    const option = checkout.paymentOption(IN3.label);
    skipIfDisconnected((await option.count()) === 0, 'in3 is not offered at the payment step');

    const logo = option.locator('img');
    await expect(logo, `in3: no logo rendered with MOLLIE_IMAGES=${setting}`).toHaveCount(1);
    expect(
      await logo.getAttribute('src'),
      `in3: MOLLIE_IMAGES=${setting} must render the "${imageKey}" image off the method row`
    ).toBe(images[imageKey]);
    // The two settings must not resolve to the same file, or neither assertion
    // would prove the setting was read at all.
    expect(images.svg, 'the svg and size2x images are identical').not.toBe(images.size2x);
  });
}

test(`MOLLIE_IMAGES=${LOGOS_HIDE} renders no logo at all`, async ({ page }) => {
  test.setTimeout(180_000);

  await setLogoDisplayThroughTheBackOffice(page, LOGOS_HIDE);

  const checkout = new CheckoutPage(page);
  await checkout.start(IN3.billingCountry, { minTotal: IN3.minAmount });

  const option = checkout.paymentOption(IN3.label);
  skipIfDisconnected((await option.count()) === 0, 'in3 is not offered at the payment step');

  // The method must still be offered — "hide" hides the logo, not the method.
  await expect(option).toBeVisible();
  await expect(option.locator('img'), 'in3: a logo is still rendered with MOLLIE_IMAGES=hide').toHaveCount(0);
});

/**
 * Drives the setting through the advanced-settings form rather than writing the
 * config row, because the form is the half that breaks: `logoDisplay` is a
 * button group with no field name, and it is the controller's
 * `updateValue(Config::MOLLIE_IMAGES, …)` that has to receive the right value.
 */
async function setLogoDisplayThroughTheBackOffice(page: Page, setting: string): Promise<void> {
  const settings = new AdvancedSettingsPage(page);
  await settings.goto();
  skipIfDisconnected(!(await settings.waitForForm()), 'the advanced settings form did not render');

  const section = page.locator('.settings-section').filter({ hasText: /visual settings/i }).first();
  // "Hide" / "Normal" / "Big" — the option ids are the config values, and the
  // buttons are labelled with their translated names, so match on the name.
  const label = { hide: /^hide$/i, normal: /^normal$/i, big: /^big$/i }[setting]!;
  await section.locator('.btn-group-item').filter({ hasText: label }).first().click();

  await settings.save();
  await settings.expectSavedSuccessfully();

  expect(
    getGlobalConfig(IMAGES_KEY),
    `saving the advanced settings did not store MOLLIE_IMAGES=${setting}`
  ).toBe(setting);
}
