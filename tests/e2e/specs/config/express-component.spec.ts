import { test, expect } from '../../fixtures/base';
import { PaymentMethodsPage } from '../../pages/admin/payment-methods-page';
import { CheckoutPage } from '../../pages/front/checkout-page';
import { getGlobalConfig, restoreGlobalConfig, setGlobalConfig, setMethodConfig } from '../../helpers/config';
import { skipIfDisconnected } from '../../helpers/module-state';

/**
 * The Express Component is live-only (the Sessions API beta has no test mode), so what a
 * test-key shop can prove is the gating around it: the back office card persists its
 * switches and locks the Apple Pay Direct ones, the payment step stays free of the mount
 * div and of console errors while the setting is on in test mode, and the Apple Pay Direct
 * button leaves the cart page while the component is enabled.
 *
 * Serial: every test here rewrites global configuration.
 */
test.describe.configure({ mode: 'serial' });

const EXPRESS_KEYS = [
  'MOLLIE_EXPRESS_COMPONENT_ENABLED',
  'MOLLIE_EXPRESS_COMPONENT_APPLEPAY',
  'MOLLIE_EXPRESS_COMPONENT_GOOGLEPAY',
  'MOLLIE_EXPRESS_COMPONENT_PAYPAL',
  'MOLLIE_APPLE_PAY_DIRECT_CART',
  'MOLLIE_APPLE_PAY_DIRECT_PRODUCT',
] as const;

const previous: Record<string, string | null> = {};

test.beforeAll(() => {
  for (const key of EXPRESS_KEYS) {
    previous[key] = getGlobalConfig(key);
  }
});

test.afterAll(() => {
  // A fixture failure can skip beforeAll; only restore what was captured.
  for (const key of Object.keys(previous)) {
    restoreGlobalConfig(key, previous[key]);
  }
});

test('the back office card saves its switches and locks Apple Pay Direct', async ({ page }) => {
  test.setTimeout(90_000);
  setGlobalConfig('MOLLIE_EXPRESS_COMPONENT_ENABLED', '0');
  setMethodConfig('applepay', { enabled: true, api: 'payments' });

  const methods = new PaymentMethodsPage(page);
  await methods.goto();
  skipIfDisconnected((await methods.cardCount()) === 0, 'the Mollie method list is empty');

  const card = page.getByTestId('express-component-card');
  await expect(card).toBeVisible();

  const enabledSwitch = page.getByTestId('express-component-enabled-switch');
  await expect(enabledSwitch).toHaveAttribute('aria-checked', 'false');
  await enabledSwitch.click();
  await page.getByTestId('express-component-paypal-switch').click();
  await page.getByTestId('express-component-save').click();

  await expect.poll(() => getGlobalConfig('MOLLIE_EXPRESS_COMPONENT_ENABLED'), { timeout: 15_000 }).toBe('1');
  expect(getGlobalConfig('MOLLIE_EXPRESS_COMPONENT_PAYPAL')).toBe('1');

  await page.reload();
  await expect(page.getByTestId('express-component-enabled-switch')).toHaveAttribute('aria-checked', 'true');

  const appleCard = await methods.revealCard('applepay', 5_000);
  test.skip((await appleCard.count()) === 0, 'applepay is not offered by the Mollie test profile');
  await methods.toggleSettings('applepay');
  // The Direct block is collapsed inside the method settings.
  await page.getByTestId('payment-method-applepay-direct-settings-toggle').click();
  await expect(page.getByTestId('payment-method-applepay-direct-product-switch')).toBeDisabled();
  await expect(page.getByTestId('payment-method-applepay-direct-cart-switch')).toBeDisabled();

  await page.getByTestId('express-component-enabled-switch').click();
  await page.getByTestId('express-component-save').click();
  await expect.poll(() => getGlobalConfig('MOLLIE_EXPRESS_COMPONENT_ENABLED'), { timeout: 15_000 }).toBe('0');

  await page.reload();
  await methods.revealCard('applepay', 5_000);
  await methods.toggleSettings('applepay');
  await page.getByTestId('payment-method-applepay-direct-settings-toggle').click();
  await expect(page.getByTestId('payment-method-applepay-direct-product-switch')).toBeEnabled();
});

test('a test-mode shop renders no express component on the payment step', async ({ page }) => {
  test.setTimeout(120_000);
  setGlobalConfig('MOLLIE_EXPRESS_COMPONENT_ENABLED', '1');

  const checkout = new CheckoutPage(page);
  await checkout.start('NL');

  await expect(page.locator('#checkout-payment-step')).toBeVisible();
  await expect(page.locator('#mollie-express-component')).toHaveCount(0);
  // The base fixture fails the test on any unexpected console error, which is the
  // assertion that the script was never served to a test-mode shop.
});

test('the Apple Pay Direct cart button is gone while the component is enabled', async ({ page }) => {
  test.setTimeout(90_000);
  setMethodConfig('applepay', { enabled: true, api: 'payments' });
  setGlobalConfig('MOLLIE_APPLE_PAY_DIRECT_CART', '0');
  setGlobalConfig('MOLLIE_EXPRESS_COMPONENT_ENABLED', '0');

  const checkout = new CheckoutPage(page);
  await checkout.emptyCart();
  await checkout.addProduct(1);

  // Direct is switched on only now, and the "present" case is read over plain
  // HTTP: the Direct script itself throws "insecure document" page errors on a
  // non-TLS shop, which the base fixture would count against this test. The
  // markup is what the PHP gate controls.
  setGlobalConfig('MOLLIE_APPLE_PAY_DIRECT_CART', '1');
  const withDirect = await (await page.request.get('/en/cart?action=show')).text();
  expect(withDirect).toContain('id="mollie-applepay-direct-button"');

  setGlobalConfig('MOLLIE_EXPRESS_COMPONENT_ENABLED', '1');
  await page.goto('/en/cart?action=show');
  await expect(page.locator('#mollie-applepay-direct-button')).toHaveCount(0);
});
