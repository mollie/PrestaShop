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

// eslint-disable-next-line @typescript-eslint/no-empty-object-type -- no test-scoped fixtures yet
export const test = base.extend<{}, WorkerFixtures>({
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
});

export { expect };
