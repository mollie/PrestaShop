import { test, expect, Page } from '@playwright/test';
import fs from 'node:fs';
import path from 'node:path';

// After a reload the one page checkout gets the top strip and the payment step strip, both drawn
// visible with a placeholder. The top strip is the express entry there, so the payment step strip
// must not stay on the page as an empty block.

const SHOP = 'https://shop.test';
const SCRIPT = fs.readFileSync(path.resolve(__dirname, '../../../views/js/front/expressComponent/expressComponent.js'), 'utf8');

const SESSION = { clientAccessToken: 'token_fake', sessionId: 'sess_fake', amount: { value: '10.00' } };

function strip(placement: string): string {
  const paymentParts = placement === 'payment'
    ? '<p class="mollie-express-component__terms">Accept the terms of service below to use express checkout.</p>'
    : '';
  const divider = placement === 'payment' ? '<p class="mollie-express-component__divider"><span>or pay with</span></p>' : '';

  return `<div ${placement === 'payment' ? 'id="mollie-express-component" ' : ''}class="mollie-express-component mollie-express-component--${placement}" data-placement="${placement}" data-amount="10.00" data-currency="EUR">
      <p class="mollie-express-component__title">Express checkout</p>
      ${paymentParts}
      <div class="mollie-express-component__placeholder" style="height: 56px; background: #eeeeee;"></div>
      <div class="mollie-express-component__mount" style="height: 0; min-height: 0; overflow: hidden;"></div>
      <p class="mollie-express-component__error" hidden></p>
      ${divider}
    </div>`;
}

function onePageCheckout(placements: string[]): string {
  const top = placements.includes('checkout') ? strip('checkout') : '';
  const payment = placements.includes('payment') ? strip('payment') : '';

  return `<!doctype html><html><body>
    ${top}
    <section id="checkout-payment-step" class="checkout-step js-current-step">
      <div class="js-opc-payment-section">
        ${payment}
        <div id="conditions-to-approve"><input type="checkbox" required></div>
      </div>
    </section>
    <script>
      window.mollieExpressComponent = { ajaxUrl: '${SHOP}/ajax', createSessionAction: 'createSession', validateCartAction: 'validateCart', tokenTtlSeconds: 900, locale: 'en_US', buttons: {}, i18n: {} };
      window.Mollie2 = { Checkout: function () { return { on: function () {}, create: function () {
        return { mount: function (target) { var b = document.createElement('button'); b.textContent = 'Google Pay'; target.appendChild(b); }, unmount: function () {} };
      } }; } };
    </script>
    <script>${SCRIPT}</script>
  </body></html>`;
}

async function openCheckout(page: Page, placements: string[]): Promise<void> {
  await page.route(`${SHOP}/ajax`, (route) => route.fulfill({ contentType: 'application/json', body: JSON.stringify({ success: true, data: SESSION }) }));
  await page.route(`${SHOP}/order`, (route) => route.fulfill({ contentType: 'text/html', body: onePageCheckout(placements) }));
  await page.goto(`${SHOP}/order`);
}

const paymentStrip = (page: Page) => page.locator('.mollie-express-component--payment');
const topStrip = (page: Page) => page.locator('.mollie-express-component--checkout');

test.describe('Express strips on the one page checkout', () => {
  test('the payment step strip hides when the top strip is the express entry', async ({ page }) => {
    await openCheckout(page, ['checkout', 'payment']);

    await expect(topStrip(page).locator('.mollie-express-component__mount button')).toHaveCount(1);
    await expect(paymentStrip(page)).toBeHidden();
  });

  test('the payment step strip still mounts when there is no top strip', async ({ page }) => {
    await openCheckout(page, ['payment']);

    await page.locator('#conditions-to-approve input').check();

    await expect(paymentStrip(page)).toBeVisible();
    await expect(paymentStrip(page).locator('.mollie-express-component__mount button')).toHaveCount(1);
  });
});

test.describe('Express strips when the page changes after load', () => {
  test('a re-rendered one page checkout payment section does not bring the empty strip back', async ({ page }) => {
    await openCheckout(page, ['checkout', 'payment']);
    await expect(topStrip(page).locator('.mollie-express-component__mount button')).toHaveCount(1);

    await page.evaluate((html) => {
      const section = document.querySelector('.js-opc-payment-section');
      if (section) section.innerHTML = html;
    }, strip('payment'));

    await expect(paymentStrip(page)).toBeHidden();
    await expect(topStrip(page)).toBeVisible();
  });

  test('the 5 step payment strip shows its placeholder as soon as the payment step opens', async ({ page }) => {
    const fiveSteps = `<!doctype html><html><body>
      <section id="checkout-personal-information-step" class="checkout-step js-current-step">${strip('checkout')}</section>
      <section id="checkout-payment-step" class="checkout-step">${strip('payment')}</section>
      <script>
        window.mollieExpressComponent = { ajaxUrl: '${SHOP}/ajax', createSessionAction: 'createSession', validateCartAction: 'validateCart', tokenTtlSeconds: 900, locale: 'en_US', buttons: {}, i18n: {} };
        window.Mollie2 = { Checkout: function () { return { on: function () {}, create: function () {
          return { mount: function (target) { return new Promise(function (resolve) { setTimeout(function () {
            var b = document.createElement('button'); b.textContent = 'Google Pay'; target.appendChild(b); resolve();
          }, window.mountDelay || 0); }); }, unmount: function () {} };
        } }; } };
      </script>
      <script>${SCRIPT}</script>
    </body></html>`;
    await page.route(`${SHOP}/ajax`, (route) => route.fulfill({ contentType: 'application/json', body: JSON.stringify({ success: true, data: SESSION }) }));
    await page.route(`${SHOP}/order`, (route) => route.fulfill({ contentType: 'text/html', body: fiveSteps }));
    await page.goto(`${SHOP}/order`);

    await expect(topStrip(page).locator('.mollie-express-component__mount button')).toHaveCount(1);
    await expect(paymentStrip(page)).toBeHidden();

    await page.evaluate(() => {
      (window as unknown as { mountDelay: number }).mountDelay = 4000;
      document.getElementById('checkout-personal-information-step')?.classList.remove('js-current-step');
      document.getElementById('checkout-payment-step')?.classList.add('js-current-step');
    });

    await expect(paymentStrip(page)).toBeVisible({ timeout: 2000 });
    await expect(paymentStrip(page).locator('.mollie-express-component__placeholder')).toHaveCount(1);
    await expect(topStrip(page)).toBeHidden();
    await expect(paymentStrip(page).locator('.mollie-express-component__mount button')).toHaveCount(1, { timeout: 10000 });
  });
});
