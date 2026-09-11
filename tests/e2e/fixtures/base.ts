import { test as base, expect, type Page } from '@playwright/test';
import path from 'node:path';
import { isPubliclyReachableBaseUrl } from '../helpers/env';

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
  /**
   * A page with no session at all — neither the BO cookie nor a front-office
   * login. `page` cannot express this: its storage state is overridden below to
   * the worker's dual BO+FO session, and `test.use({ storageState })` does not
   * reach a fixture override. Carries the same third-party stubbing and console
   * guard as `page`.
   */
  guestPage: Page;
};

/**
 * Stubs the third parties in THIRD_PARTY_BLOCKLIST. Shared by the `page` fixture
 * (via blockThirdParties) and by `guestPage`, which builds its own context.
 */
async function stubThirdParties(page: Page): Promise<void> {
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
}

/** Collects the console errors that ALLOWED_CONSOLE_ERRORS does not excuse. */
function watchConsoleErrors(page: Page): string[] {
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

  return errors;
}

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
        // Same rule as the config: only a local shop may present a broken cert.
        ignoreHTTPSErrors: !isPubliclyReachableBaseUrl(),
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
      await stubThirdParties(page);
      await use();
    },
    { auto: true },
  ],

  guestPage: async ({ browser }, use, testInfo) => {
    const context = await browser.newContext({
      baseURL: testInfo.project.use.baseURL,
      // Same rule as the config: only a local shop may present a broken cert.
      ignoreHTTPSErrors: !isPubliclyReachableBaseUrl(),
      // Explicitly empty, and not merely omitted: `browser.newContext()` inside
      // a test inherits the project's context options, so leaving this out hands
      // the "guest" the worker's BO+FO session — the checkout then skips the
      // personal-information step entirely and the test silently exercises a
      // logged-in customer instead.
      storageState: { cookies: [], origins: [] },
    });
    const page = await context.newPage();
    await stubThirdParties(page);
    const errors = watchConsoleErrors(page);

    await use(page);

    // Asserted before the context closes, and only when the test itself passed:
    // a failing test already has its own message, and adding a console
    // complaint on top of it hides the real one.
    const consoleErrors = [...errors];
    await context.close();
    if (testInfo.status === testInfo.expectedStatus) {
      expect(consoleErrors, `Unexpected console errors:\n${consoleErrors.join('\n')}`).toHaveLength(0);
    }
  },

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
      const errors = watchConsoleErrors(page);

      await use();

      expect(errors, `Unexpected console errors:\n${errors.join('\n')}`).toHaveLength(0);
    },
    { auto: true },
  ],
});

export { expect };
