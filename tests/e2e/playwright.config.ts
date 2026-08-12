import { defineConfig, devices } from '@playwright/test';
import { envValueOr, isPubliclyReachableBaseUrl } from './helpers/env';

/** Checkout traverses the tunnel and Mollie's hosted pages; 30s is not enough. */
const CHECKOUT_TIMEOUT = 120_000;

export default defineConfig({
  testDir: '.',
  timeout: 30_000,
  expect: { timeout: 5_000 },
  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 1 : 0,
  // outputDir is explicit because the default resolves against the nearest
  // package.json — the repo root, not this directory — and CI uploads
  // tests/e2e/blob-report. Without it every upload finds no files and the
  // merge job has nothing to merge.
  reporter: process.env.CI
    ? [['blob', { outputDir: 'blob-report' }], ['github']]
    : [['html', { open: 'never' }]],
  use: {
    baseURL: envValueOr('E2E_BASE_URL', 'http://localhost:8002'),
    trace: 'on-first-retry',
    video: 'retain-on-failure',
    // Only a local shop is allowed a broken certificate. The CI tunnel and
    // Mollie's hosted pages carry real ones, and there a TLS error is a
    // finding the suite must surface, not noise to swallow.
    ignoreHTTPSErrors: !isPubliclyReachableBaseUrl(),
  },
  projects: [
    { name: 'bo-auth', testMatch: /bo-auth\.setup\.ts/ },
    // Connects the module's test API key before anything that depends on the
    // module having one. Without it a shop straight out of `e2eh<VERSION>_local`
    // has MOLLIE_API_KEY_TEST NULL, and every method-dependent test skips
    // itself — a green run that exercised nothing.
    {
      name: 'mollie-connect',
      testMatch: /connect\.setup\.ts/,
      dependencies: ['bo-auth'],
      // Explicit, because this setup imports the bare Playwright `test` rather
      // than `fixtures/base` — so it does not inherit the storage-state override
      // that gives every spec its BO session, and would otherwise open the
      // module's configure page as an anonymous visitor and time out on the
      // login screen. Written by the `bo-auth` project above.
      use: { storageState: '.auth/bo.json' },
    },
    { name: 'cfg-orders', testMatch: /cfg-orders\.setup\.ts/, dependencies: ['mollie-connect'] },
    {
      name: 'admin',
      testDir: './specs/admin',
      testIgnore: /mobile-checkout\.spec\.ts/,
      // cfg-orders is not a data dependency, it is an ordering constraint:
      // it wipes and rewrites every ps_mol_payment_method row, and the local
      // CI job runs it (as mobile's dependency) in the same invocation as
      // this project. Without the ordering it can fire mid-flight through
      // method-toggle.spec and re-enable the method the spec just disabled.
      dependencies: ['mollie-connect', 'cfg-orders'],
    },
    // Needs no BO session of its own — it only exercises the front controller's
    // guard clauses — but it DOES need the shop's connected/disconnected state to
    // be settled, because which guard is reachable depends on it. Without this
    // dependency the spec raced `mollie-connect` and asserted 401 against a shop
    // that had just been connected.
    { name: 'webhook', testDir: './specs/webhook', dependencies: ['mollie-connect'] },
    // testDir, not testMatch: /checkout\.spec\.ts/ would also match
    // specs/admin/mobile-checkout.spec.ts.
    {
      name: 'checkout-orders',
      testDir: './specs/checkout',
      dependencies: ['cfg-orders'],
      // A checkout hop goes shop -> Cloudflare tunnel -> Mollie -> back, which
      // does not fit the 30s default.
      timeout: CHECKOUT_TIMEOUT,
    },
    // Deliberately NOT dependent on checkout-orders. Making it so re-runs the
    // whole checkout-orders project inside the payments invocation — and because
    // E2E_CHECKOUT_API is then 'payments', it runs the payments method set twice,
    // the second time labelled as the orders phase. Phase ordering is enforced by
    // running the two invocations in sequence (see the CI workflow and the
    // e2e-tests-locally target), which is also what keeps the shared per-method
    // API assignment from being rewritten mid-phase.
    { name: 'cfg-payments', testMatch: /cfg-payments\.setup\.ts/, dependencies: ['mollie-connect'] },
    {
      name: 'checkout-payments',
      testDir: './specs/checkout',
      dependencies: ['cfg-payments'],
      timeout: CHECKOUT_TIMEOUT,
    },
    {
      name: 'mobile',
      testMatch: /mobile-checkout\.spec\.ts/,
      dependencies: ['mollie-connect', 'cfg-orders'],
      // iPhone 13 defaults to WebKit; only Chromium is installed in CI, and it
      // supports the same mobile emulation.
      use: { ...devices['iPhone 13'], browserName: 'chromium' },
    },
  ],
});
