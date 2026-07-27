import { defineConfig, devices } from '@playwright/test';
import { envValueOr } from './helpers/env';

/** Checkout traverses the tunnel and Mollie's hosted pages; 30s is not enough. */
const CHECKOUT_TIMEOUT = 120_000;

export default defineConfig({
  testDir: '.',
  timeout: 30_000,
  expect: { timeout: 5_000 },
  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 1 : 0,
  reporter: process.env.CI
    ? [['blob'], ['github']]
    : [['html', { open: 'never' }]],
  use: {
    baseURL: envValueOr('E2E_BASE_URL', 'http://localhost:8002'),
    trace: 'on-first-retry',
    video: 'retain-on-failure',
    ignoreHTTPSErrors: true,
  },
  projects: [
    { name: 'bo-auth', testMatch: /bo-auth\.setup\.ts/ },
    { name: 'cfg-orders', testMatch: /cfg-orders\.setup\.ts/, dependencies: ['bo-auth'] },
    {
      name: 'admin',
      testDir: './specs/admin',
      testIgnore: /mobile-checkout\.spec\.ts/,
      dependencies: ['bo-auth'],
    },
    // No dependencies: needs neither a BO session nor module configuration, it
    // only exercises the front controller's own guard clauses.
    { name: 'webhook', testDir: './specs/webhook' },
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
    // Depends on checkout-orders, not just cfg-orders: the Orders-API checkouts
    // must finish before the per-method API assignment is rewritten under them.
    { name: 'cfg-payments', testMatch: /cfg-payments\.setup\.ts/, dependencies: ['checkout-orders'] },
    {
      name: 'checkout-payments',
      testDir: './specs/checkout',
      dependencies: ['cfg-payments'],
      timeout: CHECKOUT_TIMEOUT,
    },
    {
      name: 'mobile',
      testMatch: /mobile-checkout\.spec\.ts/,
      dependencies: ['bo-auth', 'cfg-orders'],
      // iPhone 13 defaults to WebKit; only Chromium is installed in CI, and it
      // supports the same mobile emulation.
      use: { ...devices['iPhone 13'], browserName: 'chromium' },
    },
  ],
});
