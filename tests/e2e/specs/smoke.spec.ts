import { test, expect } from '@playwright/test';

test('shop front page responds', async ({ page }) => {
  await page.goto('/');
  await expect(page).toHaveTitle(/.+/);
});
