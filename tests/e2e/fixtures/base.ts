import { test as base, expect } from '@playwright/test';
import path from 'node:path';

/** Written by the `bo-auth` setup project. */
export const BO_AUTH_FILE = '.auth/bo.json';

/**
 * Every worker customer shares this password — it is the same bcrypt hash the
 * seed already carries for `demo@prestashop.com`, verified against
 * `password_verify`. See the E2E block at the end of
 * `tests/seed/database/prestashop_*.sql`.
 */
export const FO_PASSWORD = 'prestashop_demo';

/** Pre-seeded worker customers: `e2e-worker-0` … `e2e-worker-9`. */
export const WORKER_CUSTOMER_COUNT = 10;

/**
 * Console noise that is expected in this environment and must not fail a test.
 * Anything not listed here is treated as a regression.
 */
const ALLOWED_CONSOLE_ERRORS = [
  /ChunkLoadError/,
  /Loading chunk/,
  /Failed to fetch.*segment/i,
];

/**
 * Genuine third parties only. The module's own AJAX
 * (AdminMollieAjaxController and friends) is never mocked or blocked — and
 * neither is api.mollie.com.
 */
const THIRD_PARTY_BLOCKLIST = [
  /segment\.(io|com)/,
  /addons\.prestashop\.com/,
  /cloudsync[^/]*\.prestashop\.com/,
  /(api-)?accounts\.prestashop\.com/,
];

type TestFixtures = {
  assertNoConsoleErrors: void;
  blockThirdParties: void;
  screenshotOnSuccess: void;
};

type WorkerFixtures = {
  foCustomer: { email: string; password: string };
  /**
   * A storage state that is logged into BOTH the back office and the front
   * office. The BO half comes from the `bo-auth` setup project; the FO half is
   * logged in once per worker as that worker's own customer, so parallel
   * workers never share a cart.
   */
  authStorageState: string;
};

export const test = base.extend<TestFixtures, WorkerFixtures>({
  foCustomer: [
    async ({}, use, workerInfo) => {
      const index = workerInfo.parallelIndex % WORKER_CUSTOMER_COUNT;
      await use({
        email: `e2e-worker-${index}@mollie-test.invertus.eu`,
        password: FO_PASSWORD,
      });
    },
    { scope: 'worker' },
  ],

  authStorageState: [
    async ({ browser, foCustomer }, use, workerInfo) => {
      const file = path.join('.auth', `worker-${workerInfo.parallelIndex}.json`);
      const baseURL = workerInfo.project.use.baseURL;

      const context = await browser.newContext({
        storageState: BO_AUTH_FILE,
        baseURL,
        ignoreHTTPSErrors: true,
      });
      const page = await context.newPage();

      await page.goto('/en/my-account');
      await page.locator('#login-form [name="email"]').first().fill(foCustomer.email);
      await page.locator('#login-form [name="password"]').first().fill(foCustomer.password);
      await page.locator('#login-form [type="submit"]').first().click({ force: true });
      // The account page only renders these links for a signed-in customer.
      await page.locator('#identity-link, #history-link').first().waitFor({ timeout: 20_000 });

      await context.storageState({ path: file });
      await context.close();

      await use(file);
    },
    { scope: 'worker' },
  ],

  storageState: async ({ authStorageState }, use) => {
    await use(authStorageState);
  },

  blockThirdParties: [
    async ({ page }, use) => {
      for (const pattern of THIRD_PARTY_BLOCKLIST) {
        // Stubbed rather than aborted: an aborted analytics beacon makes the page
        // log "Failed to fetch", which the console guard would then report as a
        // regression we caused ourselves. The stub is shaped per resource type so
        // callers can still parse the response.
        await page.route(pattern, (route) => {
          switch (route.request().resourceType()) {
            case 'script':
              return route.fulfill({ status: 200, contentType: 'application/javascript', body: '' });
            case 'document':
              return route.fulfill({ status: 200, contentType: 'text/html', body: '<!doctype html><title>blocked</title>' });
            case 'image':
              return route.fulfill({ status: 200, contentType: 'image/gif', body: '' });
            default:
              return route.fulfill({ status: 200, contentType: 'application/json', body: '{}' });
          }
        });
      }
      await use();
    },
    { auto: true },
  ],

  /**
   * Failures already leave a video and an error-context snapshot behind;
   * a PASSED test leaves nothing, so a reviewer cannot see what "passed"
   * actually looked like. This attaches the final page state to every
   * passing test in the report (`screenshot: 'on'` in the config would do
   * the same but also duplicate the failure artifacts).
   */
  screenshotOnSuccess: [
    async ({ page }, use, testInfo) => {
      await use();

      if (testInfo.status !== 'passed') return;
      // Best-effort: a page that is already closed (or wedged mid-navigation)
      // must not fail a test that has passed on its merits.
      try {
        const shot = await page.screenshot({ fullPage: true, timeout: 10_000 });
        await testInfo.attach('passed-final-state', { body: shot, contentType: 'image/png' });
      } catch {
        // no screenshot is better than a teardown failure
      }
    },
    { auto: true },
  ],

  assertNoConsoleErrors: [
    async ({ page }, use) => {
      const errors: string[] = [];

      page.on('console', (msg) => {
        if (msg.type() !== 'error') return;
        const text = msg.text();
        if (ALLOWED_CONSOLE_ERRORS.some((p) => p.test(text))) return;
        // A failed asset fetch is only interesting when it is one of the
        // module's own assets; the seeded shop's theme and third-party modules
        // reference a few images that never resolve.
        if (/Failed to load resource/.test(text) && !/mollie/i.test(msg.location().url)) return;
        errors.push(text);
      });

      // An uncaught exception is always a regression, never expected noise.
      page.on('pageerror', (error) => errors.push(`pageerror: ${error.message}`));

      await use();

      expect(errors, `Unexpected console errors:\n${errors.join('\n')}`).toHaveLength(0);
    },
    { auto: true },
  ],
});

export { expect };
