import { defineConfig } from '@playwright/test';

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
    { name: 'smoke', testMatch: /smoke\.spec\.ts/ },
  ],
});
