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
  const isPayment = placement === 'payment';

  return `<div ${isPayment ? 'id="mollie-express-component" ' : ''}class="mollie-express-component mollie-express-component--${placement}" data-placement="${placement}" data-amount="10.00" data-currency="EUR">
      <p class="mollie-express-component__title">Express checkout</p>
      ${isPayment ? '<p class="mollie-express-component__terms">Accept the terms of service below to use express checkout.</p>' : ''}
      <div class="mollie-express-component__placeholder" style="height: 56px; background: #eeeeee;"></div>
      <div class="mollie-express-component__mount" style="height: 0; min-height: 0; overflow: hidden;"></div>
      <p class="mollie-express-component__error" hidden></p>
      ${isPayment ? '<p class="mollie-express-component__divider"><span>or pay with</span></p>' : ''}
    </div>`;
}

// Mollie draws one button after window.mountDelay milliseconds.
function checkoutPage(body: string): string {
  return `<!doctype html><html><body>
    ${body}
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
}

function onePageCheckout(placements: string[]): string {
  return `${placements.includes('checkout') ? strip('checkout') : ''}
    <section id="checkout-payment-step" class="checkout-step js-current-step">
      <div class="js-opc-payment-section">
        ${placements.includes('payment') ? strip('payment') : ''}
        <div id="conditions-to-approve"><input type="checkbox" required></div>
      </div>
    </section>`;
}

const FIVE_STEPS = `
  <section id="checkout-personal-information-step" class="checkout-step js-current-step">${strip('checkout')}</section>
  <section id="checkout-payment-step" class="checkout-step">${strip('payment')}</section>`;

async function openCheckout(page: Page, body: string): Promise<void> {
  await page.route(`${SHOP}/ajax`, (route) => route.fulfill({ contentType: 'application/json', body: JSON.stringify({ success: true, data: SESSION }) }));
  await page.route(`${SHOP}/order`, (route) => route.fulfill({ contentType: 'text/html', body: checkoutPage(body) }));
  await page.goto(`${SHOP}/order`);
}

const paymentStrip = (page: Page) => page.locator('.mollie-express-component--payment');
const topStrip = (page: Page) => page.locator('.mollie-express-component--checkout');

test.describe('Express strips on the one page checkout', () => {
  test('the payment step strip hides when the top strip is the express entry', async ({ page }) => {
    await openCheckout(page, onePageCheckout(['checkout', 'payment']));

    await expect(topStrip(page).locator('.mollie-express-component__mount button')).toHaveCount(1);
    await expect(paymentStrip(page)).toBeHidden();
  });

  test('the payment step strip still mounts when there is no top strip', async ({ page }) => {
    await openCheckout(page, onePageCheckout(['payment']));

    await page.locator('#conditions-to-approve input').check();

    await expect(paymentStrip(page)).toBeVisible();
    await expect(paymentStrip(page).locator('.mollie-express-component__mount button')).toHaveCount(1);
  });
});

test.describe('Express strips when the page changes after load', () => {
  test('a re-rendered one page checkout payment section does not bring the empty strip back', async ({ page }) => {
    await openCheckout(page, onePageCheckout(['checkout', 'payment']));
    await expect(topStrip(page).locator('.mollie-express-component__mount button')).toHaveCount(1);

    await page.evaluate((html) => {
      const section = document.querySelector('.js-opc-payment-section');
      if (section) section.innerHTML = html;
    }, strip('payment'));

    await expect(paymentStrip(page)).toBeHidden();
    await expect(topStrip(page)).toBeVisible();
  });

  test('the 5 step payment strip shows its placeholder as soon as the payment step opens', async ({ page }) => {
    await openCheckout(page, FIVE_STEPS);

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
