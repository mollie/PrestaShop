import { defineConfig, devices } from '@playwright/test';

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
    baseURL: process.env.E2E_BASE_URL || 'http://localhost:8002',
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
