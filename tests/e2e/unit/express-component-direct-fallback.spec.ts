import { test, expect, Page } from '@playwright/test';
import fs from 'node:fs';
import path from 'node:path';

// The cart page renders Apple Pay Direct hidden while the express strip offers Apple Pay. These
// tests run the real front script against a stubbed shop and Mollie.js and check that Direct
// comes back whenever the strip cannot show, so the cart page never loses Apple Pay.

const SHOP = 'https://shop.test';
const SCRIPT = fs.readFileSync(path.resolve(__dirname, '../../../views/js/front/expressComponent/expressComponent.js'), 'utf8');

const SESSION = { clientAccessToken: 'token_fake', sessionId: 'sess_fake', amount: { value: '10.00' } };

type MollieStub = 'none' | 'buttons' | 'empty';

function cartPage(mollie: MollieStub): string {
  const mollieStub = {
    none: '',
    buttons: `window.Mollie2 = { Checkout: function () { return { on: function () {}, create: function () {
      return { mount: function (target) { var b = document.createElement('button'); b.textContent = 'Apple Pay'; target.appendChild(b); }, unmount: function () {} };
    } }; } };`,
    empty: `window.Mollie2 = { Checkout: function () { return { on: function () {}, create: function () {
      return { mount: function () {}, unmount: function () {} };
    } }; } };`,
  }[mollie];

  return `<!doctype html><html><body>
    <div class="cart-detailed-actions">
      <div class="mollie-express-component mollie-express-component--cart" data-placement="cart" data-amount="10.00" data-currency="EUR">
        <div class="mollie-express-component__mount"></div>
      </div>
      <div id="mollie-applepay-direct-button" data-mollie-express-fallback hidden></div>
    </div>
    <script>
      window.mollieExpressComponent = { ajaxUrl: '${SHOP}/ajax', createSessionAction: 'createSession', validateCartAction: 'validateCart', tokenTtlSeconds: 900, locale: 'en_US', buttons: {}, i18n: {} };
      ${mollieStub}
    </script>
    <script>${SCRIPT}</script>
  </body></html>`;
}

async function openCart(page: Page, mollie: MollieStub, ajax: { status: number; body: object }): Promise<void> {
  await page.route(`${SHOP}/ajax`, (route) => route.fulfill({ status: ajax.status, contentType: 'application/json', body: JSON.stringify(ajax.body) }));
  await page.route(`${SHOP}/cart`, (route) => route.fulfill({ contentType: 'text/html', body: cartPage(mollie) }));
  await page.goto(`${SHOP}/cart`);
}

const direct = (page: Page) => page.locator('#mollie-applepay-direct-button');
const strip = (page: Page) => page.locator('.mollie-express-component--cart');

test.describe('Apple Pay Direct fallback on the cart page', () => {
  test('stays hidden while the strip shows its buttons', async ({ page }) => {
    await openCart(page, 'buttons', { status: 200, body: { success: true, data: SESSION } });

    await expect(strip(page)).toBeVisible();
    await expect(page.locator('.mollie-express-component__mount button')).toHaveCount(1);
    await expect(direct(page)).toHaveAttribute('hidden', '');
  });

  test('comes back when the shop refuses the express session', async ({ page }) => {
    await openCart(page, 'buttons', { status: 400, body: { success: false, errors: ['express_unavailable'], data: [] } });

    await expect(direct(page)).not.toHaveAttribute('hidden');
    await expect(strip(page)).toBeHidden();
  });

  test('comes back when the session request fails', async ({ page }) => {
    await openCart(page, 'buttons', { status: 502, body: {} });

    await expect(direct(page)).not.toHaveAttribute('hidden');
  });

  test('comes back when Mollie.js is not loaded', async ({ page }) => {
    await openCart(page, 'none', { status: 200, body: { success: true, data: SESSION } });

    await expect(direct(page)).not.toHaveAttribute('hidden');
    await expect(strip(page)).toBeHidden();
  });

  test('comes back when Mollie draws no wallet button', async ({ page }) => {
    await openCart(page, 'empty', { status: 200, body: { success: true, data: SESSION } });

    await expect(direct(page)).not.toHaveAttribute('hidden', { timeout: 10000 });
    await expect(strip(page)).toBeHidden();
  });

  test('a re-rendered cart block keeps Direct visible after a failure', async ({ page }) => {
    await openCart(page, 'buttons', { status: 400, body: { success: false, errors: ['express_unavailable'], data: [] } });
    await expect(direct(page)).not.toHaveAttribute('hidden');

    await page.evaluate(() => {
      const old = document.querySelector('#mollie-applepay-direct-button');
      const fresh = document.createElement('div');
      fresh.id = 'mollie-applepay-direct-button';
      fresh.setAttribute('data-mollie-express-fallback', '');
      fresh.hidden = true;
      old?.replaceWith(fresh);
    });

    await expect(direct(page)).not.toHaveAttribute('hidden');
  });
});
